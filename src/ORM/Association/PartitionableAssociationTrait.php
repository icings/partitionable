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
    protected ?int $limit = null;

    /**
     * The filter strategy.
     *
     * @var string
     */
    protected string $filterStrategy = self::FILTER_IN_SUBQUERY_TABLE;

    /**
     * Whether the single result mode is enabled.
     *
     * @var bool
     */
    protected bool $_isSingleResultEnabled = false;

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
     * A limit of `1` will automatically enable the single result mode.
     *
     * @param int|null $limit The limit.
     * @return $this
     * @see enableSingleResult()
     * @see disableSingleResult()
     * @see isSingleResultEnabled()
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

        // Enable single results mode for a limit of 1.
        if (
            $limit === 1 &&
            !$this->isSingleResultEnabled()
        ) {
            $this->enableSingleResult();
        }

        // Disable single results mode for a limit greater than 1.
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
     * Enabling this mode will automatically set the limit to `1`.
     *
     * @return $this
     * @see setLimit()
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
    protected function _options(array $options): void
    {
        parent::_options($options);

        if (isset($options['limit'])) {
            $this->setLimit($options['limit']);
        }

        if (isset($options['filterStrategy'])) {
            $this->setFilterStrategy($options['filterStrategy']);
        }

        if (isset($options['singleResult'])) {
            if ($options['singleResult']) {
                $this->enableSingleResult();
            } else {
                $this->disableSingleResult();
            }
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
