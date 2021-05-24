<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM\Association;

use Cake\Utility\Inflector;
use InvalidArgumentException;

trait PartitionableAssociationTrait
{
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
    protected $filterStrategy = self::FILTER_IN_SUBQUERY_TABLE;

    /**
     * Whether the single result mode is enabled.
     *
     * @var bool
     */
    protected $_isSingleResultEnabled = false;

    /**
     * Returns the partition limit.
     *
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Sets the partition limit.
     *
     * @param int|null $limit The limit.
     * @return $this
     */
    public function setLimit(?int $limit)
    {
        if (
            $limit !== null &&
            $limit < 1
        ) {
            throw new InvalidArgumentException(sprintf(
                'The `$limit` argument must be greater than or equal `1`, `%s` given.',
                $limit
            ));
        }

        $this->limit = $limit;

        // Enable single results mode for a limit of 1, unless
        // single results mode has been explicitly disabled.
        if (
            $limit === 1 &&
            !$this->isSingleResultEnabled()
        ) {
            $this->enableSingleResult();
        }

        // Disable single results mode for a limit greater than 1,
        // unless single results mode has been explicitly enabled.
        if (
            $limit > 1 &&
            $this->isSingleResultEnabled()
        ) {
            $this->disableSingleResult();
        }

        return $this;
    }

    /**
     * Returns the filter strategy.
     *
     * @return string
     */
    public function getFilterStrategy(): string
    {
        return $this->filterStrategy;
    }

    /**
     * Sets the filter strategy.
     *
     * @param string $strategy The strategy.
     * @return $this
     */
    public function setFilterStrategy(string $strategy)
    {
        $validStrategies = [
            static::FILTER_IN_SUBQUERY_CTE,
            static::FILTER_IN_SUBQUERY_JOIN,
            static::FILTER_IN_SUBQUERY_TABLE,
            static::FILTER_INNER_JOIN_CTE,
            static::FILTER_INNER_JOIN_SUBQUERY,
        ];
        if (!in_array($strategy, $validStrategies, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$strategy` argument must be one of %s, `%s` given.',
                    '`' . implode('`, `', $validStrategies) . '`',
                    $strategy
                )
            );
        }

        $this->filterStrategy = $strategy;

        return $this;
    }

    /**
     * Enables the single result mode.
     *
     * @return $this
     */
    public function enableSingleResult()
    {
        $this->_isSingleResultEnabled = true;
        $this->setLimit(1);

        return $this;
    }

    /**
     * Disables the single result mode.
     *
     * @return $this
     */
    public function disableSingleResult()
    {
        $this->_isSingleResultEnabled = false;

        return $this;
    }

    /**
     * Returns whether the single result mode is enabled.
     *
     * @return bool
     */
    public function isSingleResultEnabled(): bool
    {
        return $this->_isSingleResultEnabled;
    }

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        // By default use a one-to-one type for single results,
        // unless single results mode is disabled.
        if (
            $this->getLimit() === 1 &&
            $this->isSingleResultEnabled()
        ) {
            return self::ONE_TO_ONE;
        }

        return parent::type();
    }

    /**
     * @inheritDoc
     */
    protected function _options(array $opts): void
    {
        parent::_options($opts);

        if (isset($opts['limit'])) {
            $this->setLimit($opts['limit']);
        }

        if (isset($opts['filterStrategy'])) {
            $this->setFilterStrategy($opts['filterStrategy']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function _propertyName(): string
    {
        $name = parent::_propertyName();

        // By default use the singular property names names for single results,
        // unless single results mode is disabled.
        if (
            $this->getLimit() === 1 &&
            $this->isSingleResultEnabled()
        ) {
            $name = Inflector::singularize($name);
        }

        return $name;
    }
}