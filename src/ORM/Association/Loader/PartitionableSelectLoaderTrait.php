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
use Cake\Event\EventManager;
use Cake\ORM\Query\SelectQuery;
use Closure;
use Icings\Partitionable\ORM\Association\PartitionableAssociationInterface;
use RuntimeException;
use const PHP_INT_MAX;

/**
 * @internal
 */
trait PartitionableSelectLoaderTrait
{
    /**
     * Listener that handles invoking individual, query specific
     * cleanup listeners.
     *
     * @var Closure|null
     */
    protected static ?Closure $_cleanUpListener = null;

    /**
     * Map of tracking IDs and clean up listener callables.
     *
     * @var array<int, Closure>
     */
    protected static array $_cleanUpListenerMap = [];

    /**
     * The next tracking ID.
     *
     * @var int
     */
    protected static int $_nextTrackingId = 1;

    /**
     * The partition limit.
     *
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * The filter strategy.
     *
     * @var string
     */
    protected string $filterStrategy = PartitionableAssociationInterface::FILTER_IN_SUBQUERY_TABLE;

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
     * Returns a unique tracking ID on every call.
     *
     * @return int
     */
    protected function _getNextTrackingId(): int
    {
        return static::$_nextTrackingId++;
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
    protected function _addFilteringCondition(SelectQuery $query, array|string $key, mixed $filter): SelectQuery
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
    protected function _buildQuery(array $options): SelectQuery
    {
        $fetchQuery =
            parent::_buildQuery($options)
            ->applyOptions(['partitionableQueryType' => 'fetcher']);

        $dummy = $this->_getDummyQuery($fetchQuery);

        // Bail out early with default behavior in case no limit has been set.
        $limit = $this->_getLimit($dummy, $options['limit']);
        if (!$limit) {
            return $fetchQuery;
        }

        $order = $this->_getOrder($dummy);
        if ($order) {
            $fetchQuery->orderBy($order, true);
        }

        // Clear states that must not apply to neither the fetch nor the rank query.
        $fetchQuery
            ->limit(null)
            ->offset(null);

        $rankQuery = $this
            ->_buildRankQuery(clone $fetchQuery)
            ->applyOptions(['partitionableQueryType' => 'ranking']);

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
        $this->_addCleanupListener($rankQuery->orderBy([], true), ['order' => true]);

        return $fetchQuery;
    }

    /**
     * Adds a listener for the query to ensure possibly conflicting states added
     * in `Model.beforeFind` are being cleared.
     *
     * @param SelectQuery $query The query on which to add the cleanup listener.
     * @param array<string, bool> $removals Defines what query parts should be cleaned up.
     * @return SelectQuery
     */
    protected function _addCleanupListener(SelectQuery $query, array $removals = []): SelectQuery
    {
        $removals += [
            'limit' => true,
            'offset' => true,
            'order' => false,
        ];

        $trackingOptionName = static::class . '_trackingId';
        $processedStateOptionName = static::class . '_processed';

        if (static::$_cleanUpListener === null) {
            static::$_cleanUpListener = function (
                EventInterface $event,
                SelectQuery $query
            ) use (
                $trackingOptionName,
                $processedStateOptionName
            ): SelectQuery {
                // Scope the listener to specific queries in order to avoid states
                // being messed with when the listener is triggered for other queries
                // of the same repository. Furthermore, this ensures that the listener
                // proceeds for cloned queries, which share one and the same listener,
                // but are different objects.

                $queryOptions = $query->getOptions();
                if (
                    !array_key_exists($trackingOptionName, $queryOptions) ||
                    $queryOptions[$processedStateOptionName] === true
                ) {
                    return $query;
                }

                $trackingId = $queryOptions[$trackingOptionName];
                if (!isset(static::$_cleanUpListenerMap[$trackingId])) {
                    throw new RuntimeException(
                        'The tracking ID value found on the query object has not been mapped, ' .
                        'make sure that you did not empty out or override the query options.'
                    );
                }

                return (static::$_cleanUpListenerMap[$trackingId])($event, $query);
            };
        }

        $repository = $query->getRepository();

        $eventManager = $repository->getEventManager();
        assert($eventManager instanceof EventManager);
        $listeners = $eventManager->prioritisedListeners('Model.beforeFind');

        $isListenerRegistered = false;
        foreach ($listeners[PHP_INT_MAX] ?? [] as $listener) {
            if ($listener['callable'] === static::$_cleanUpListener) {
                $isListenerRegistered = true;
                break;
            }
        }

        if (!$isListenerRegistered) {
            $repository
                ->getEventManager()
                ->on('Model.beforeFind', ['priority' => PHP_INT_MAX], static::$_cleanUpListener);
        }

        $trackingId = $this->_getNextTrackingId();

        $query->applyOptions([
            $trackingOptionName => $trackingId,
            $processedStateOptionName => false,
        ]);

        if (!isset(static::$_cleanUpListenerMap[$trackingId])) {
            $listener = function (
                EventInterface $event,
                SelectQuery $query
            ) use (
                $removals,
                $processedStateOptionName
            ): SelectQuery {
                if ($removals['limit']) {
                    $query->limit(null);
                }

                if ($removals['offset']) {
                    $query->offset(null);
                }

                if ($removals['order']) {
                    $query->orderBy([], true);
                }

                $query->applyOptions([$processedStateOptionName => true]);

                return $query;
            };

            static::$_cleanUpListenerMap[$trackingId] = $listener;
        }

        return $query;
    }

    /**
     * Creates a dummy query that has all its `beforeFind` modifications applied.
     *
     * @param SelectQuery $query The query from which to create the dummy query.
     * @return SelectQuery
     */
    protected function _getDummyQuery(SelectQuery $query): SelectQuery
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
     * @param SelectQuery $query The query that might hold the limit option.
     * @param int|null $limit The limit to use in case no limit is set on the query.
     * @return int|null
     */
    protected function _getLimit(SelectQuery $query, ?int $limit): ?int
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
     * @param SelectQuery $query The query that might hold the order.
     * @return array<ExpressionInterface|string>|null
     */
    protected function _getOrder(SelectQuery $query): ?array
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
