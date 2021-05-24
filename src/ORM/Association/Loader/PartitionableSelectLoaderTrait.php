<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM\Association\Loader;

use Cake\Database\Expression\OrderByExpression;
use Cake\Database\ExpressionInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Icings\Partitionable\ORM\Association\PartitionableAssociationInterface;
use RuntimeException;
use function spl_object_hash;
use const PHP_INT_MAX;

/**
 * @internal
 */
trait PartitionableSelectLoaderTrait
{
    /**
     * Map of repository aliases and clean up listener callables.
     *
     * @var array<string, array<string, Callable>>
     */
    protected static $_cleanUpListenerMap = [];

    /**
     * The partition limit.
     *
     * @var int|null
     */
    protected $limit;

    /**
     * The filter strategy.
     *
     * @var string
     */
    protected $filterStrategy;

    /**
     * @inheritDoc
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        $this->limit = $options['limit'];
        $this->filterStrategy = $options['filterStrategy'];
    }

    /**
     * @inheritDoc
     */
    protected function _defaultOptions(): array
    {
        return parent::_defaultOptions() + [
            'limit' => $this->limit,
            'filterStrategy' => $this->filterStrategy,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function _addFilteringCondition(Query $query, $key, $filter): Query
    {
        // Ensure that non-composite keys are not being passed as an array,
        // in order to avoid tuple comparison expressions being used when
        // not actually required.
        if (
            is_array($key) &&
            count($key) === 1
        ) {
            $key = current($key);
        }

        return parent::_addFilteringCondition($query, $key, $filter);
    }

    /**
     * @inheritDoc
     */
    protected function _buildQuery(array $options): Query
    {
        $fetchQuery = parent::_buildQuery($options);
        $dummy = $this->_getDummyQuery($fetchQuery);

        // Bail out early with default behavior in case no limit has been set.
        $limit = $this->_getLimit($dummy, $options['limit']);
        if (!$limit) {
            return $fetchQuery;
        }

        $order = $this->_getOrder($dummy);
        if ($order) {
            $fetchQuery->order($order, true);
        }

        // Clear states that must not apply to neither the fetch nor the rank query.
        $fetchQuery
            ->limit(null)
            ->offset(null);

        $rankQuery = $this->_buildRankQuery(clone $fetchQuery);
        switch ($this->filterStrategy) {
            case PartitionableAssociationInterface::FILTER_IN_SUBQUERY_CTE:
                $fetchQuery = $this->_inSubqueryCTE($fetchQuery, $rankQuery, $limit);
                break;

            case PartitionableAssociationInterface::FILTER_IN_SUBQUERY_JOIN:
                $fetchQuery = $this->_inSubqueryJoin($fetchQuery, $rankQuery, $limit);
                break;

            case PartitionableAssociationInterface::FILTER_IN_SUBQUERY_TABLE:
                $fetchQuery = $this->_inSubqueryTable($fetchQuery, $rankQuery, $limit);
                break;

            case PartitionableAssociationInterface::FILTER_INNER_JOIN_CTE:
                $fetchQuery = $this->_innerJoinCTE($fetchQuery, $rankQuery, $limit);
                break;

            case PartitionableAssociationInterface::FILTER_INNER_JOIN_SUBQUERY:
                $fetchQuery = $this->_innerJoinSubquery($fetchQuery, $rankQuery, $limit);
                break;
        }

        // Clear states that must not apply to neither the fetch nor the rank query.
        $this->_addCleanupListener($fetchQuery);
        $this->_addCleanupListener($rankQuery->order([], true), ['order' => true]);

        return $fetchQuery;
    }

    /**
     * Adds a listener for the query to ensure possibly conflicting states added
     * in `Model.beforeFind` are being cleared.
     *
     * @param Query $query The query on which to add the cleanup listener.
     * @param array<string, bool> $removals Defines what query parts should be cleaned up.
     * @return Query
     */
    protected function _addCleanupListener(Query $query, array $removals = []): Query
    {
        $removals += [
            'limit' => true,
            'offset' => true,
            'order' => false,
        ];

        $hashOptionName = static::class . '_object_hash';
        $queryHash = spl_object_hash($query);
        $query->applyOptions([$hashOptionName => spl_object_hash($query)]);

        $repository = $query->getRepository();
        $repositoryHash = spl_object_hash($repository);

        if (!isset(static::$_cleanUpListenerMap[$repositoryHash])) {
            static::$_cleanUpListenerMap[$repositoryHash] = [];

            $repository
                ->getEventManager()
                ->on('Model.beforeFind', ['priority' => PHP_INT_MAX], function (
                    EventInterface $event,
                    Query $query
                ) use (
                    $hashOptionName
                ): Query {
                    // Scope the listener to a specific query in order to avoid states
                    // being messed with when the listener is triggered for other queries
                    // of the same repository. Furthermore this ensures that the listener
                    // proceeds for cloned queries, which share one and the same listener,
                    // but are different objects.

                    $queryOptions = $query->getOptions();
                    if (!array_key_exists($hashOptionName, $queryOptions)) {
                        return $query;
                    }

                    $repositoryHash = spl_object_hash($query->getRepository());
                    $queryHash = $queryOptions[$hashOptionName];
                    if (!isset(static::$_cleanUpListenerMap[$repositoryHash][$queryHash])) {
                        throw new RuntimeException(
                            'The hash value found on the query object has not been mapped, ' .
                            'make sure that you did not empty out or override the query options.'
                        );
                    }

                    return (static::$_cleanUpListenerMap[$repositoryHash][$queryHash])($event, $query);
                });
        }

        if (!isset(static::$_cleanUpListenerMap[$repositoryHash][$queryHash])) {
            $listener = function (
                EventInterface $event,
                Query $query
            ) use (
                $removals
            ): Query {
                if ($removals['limit']) {
                    $query
                        ->limit(null);
                }

                if ($removals['offset']) {
                    $query
                        ->offset(null);
                }

                if ($removals['order']) {
                    $query
                        ->order([], true);
                }

                return $query;
            };

            static::$_cleanUpListenerMap[$repositoryHash][$queryHash] = $listener;
        }

        return $query;
    }

    /**
     * Creates a dummy query that has all its `beforeFind` modifications applied.
     *
     * @param Query $query The query from which to create the dummy query.
     * @return Query
     */
    protected function _getDummyQuery(Query $query): Query
    {
        $dummy = clone $query;
        // Run listeners in order to be able to fetch stuff that was set in a
        // `beforeFind` event.
        $dummy->triggerBeforeFind();

        return $dummy;
    }

    /**
     * Obtains the partition limit.
     *
     * @param Query $query The query that might hold the limit option.
     * @param int|null $limit The limit to use in case no limit is set on the query.
     * @return int|null
     */
    protected function _getLimit(Query $query, ?int $limit): ?int
    {
        // The limit set on the query should win over the limit set in the
        // association configuration in order to allow changing the behavior
        // on the fly.

        $queryLimit = $query->clause('limit');
        if ($queryLimit) {
            $limit = $queryLimit;
            $query->limit(null);
        }

        if (!$limit) {
            return null;
        }

        return $limit;
    }

    /**
     * Obtains the partition order.
     *
     * @param Query $query The query that might hold the order.
     * @return array<ExpressionInterface|string>|null
     */
    protected function _getOrder(Query $query): ?array
    {
        // The order set on the query should win over the order set in the
        // association configuration in order to allow changing the behavior
        // on the fly.

        /** @var OrderByExpression|null $orderClause */
        $orderClause = $query->clause('order');
        if ($orderClause) {
            $order = [];
            $orderClause->iterateParts(
                /**
                 * @param mixed $value Value
                 * @param mixed $key Key
                 * @return mixed
                 */
                function ($value, $key) use (&$order) {
                    if ($value instanceof ExpressionInterface) {
                        $value = clone $value;
                    }
                    $order[$key] = $value;

                    return $value;
                }
            );

            return $order;
        }

        return null;
    }
}
