<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM\Association\Loader;

use Cake\Database\Expression\CommonTableExpression;
use Cake\ORM\Association\Loader\SelectLoader;
use Cake\ORM\Query;
use RuntimeException;

/**
 * @internal
 */
class PartitionableSelectLoader extends SelectLoader
{
    use PartitionableSelectLoaderTrait;

    /**
     * Builds the rank query.
     *
     * @param Query $query The query to turn into the rank query.
     * @return Query
     */
    protected function _buildRankQuery(Query $query): Query
    {
        $primaryKeys = [];
        foreach ((array)$query->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "{$this->targetAlias}.{$primaryKey}";
        }

        $foreignKeys = [];
        foreach ((array)$this->foreignKey as $foreignKey) {
            $foreignKeys[] = "{$this->targetAlias}.{$foreignKey}";
        }

        $order = $this->_getOrder($query);
        if (!$order) {
            throw new RuntimeException('Partitioning requires a sort order.');
        }

        // Ensure that all fields will be present in case auto-fields has not
        // been explicitly disabled, and no fields have been explicitly selected.
        if (
            $query->isAutoFieldsEnabled() === null &&
            $query->clause('select') === []
        ) {
            $query->enableAutoFields();
        }

        $query
            ->select($primaryKeys)
            ->select([
                '__row_number' => $query
                    ->func()
                    ->rowNumber()
                    ->over()
                    ->partition($foreignKeys)
                    ->order($order),
            ]);

        return $query;
    }

    /**
     * ```sql
     * SELECT fields
     * FROM target
     * WHERE target.fk IN (bk list) AND target.pk IN (
     *     WITH ranking AS (
     *         SELECT target.pk pk, ROW_NUMBER() OVER (PARTITION BY target.fk ORDER BY sort) rank
     *         FROM target
     *         WHERE target.fk IN (bk list)
     *     )
     *     SELECT ranking.pk
     *     FROM ranking
     *     WHERE ranking.rank <= limit
     * )
     * ORDER BY sort
     * ```
     *
     * @param Query $fetchQuery The fetch query.
     * @param Query $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return Query
     */
    protected function _inSubqueryCTE(Query $fetchQuery, Query $rankQuery, int $limit): Query
    {
        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "__ranked__{$this->targetAlias}.{$primaryKey}";
        }

        $filterSubquery = $rankQuery->getRepository()->getConnection()
            ->newQuery()
            ->with(function (CommonTableExpression $cte) use ($rankQuery) {
                return $cte
                    ->name("__ranked__{$this->targetAlias}")
                    ->query($rankQuery);
            })
            ->select($primaryKeys)
            ->from("__ranked__{$this->targetAlias}")
            ->where(
                ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer']
            );

        // Add conditions to filter the fetch query via the filter query.
        // This will generate a `pk IN (subquery)` condition.
        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys[] = "{$this->targetAlias}.{$primaryKey}";
        }
        $this->_addFilteringCondition($fetchQuery, $primaryKeys, $filterSubquery);

        return $fetchQuery;
    }

    /**
     * ```sql
     * SELECT fields
     * FROM target
     * WHERE target.fk IN (bk list) AND target.pk IN (
     *     SELECT target.pk
     *     FROM target
     *     INNER JOIN (
     *         SELECT target.pk pk, ROW_NUMBER() OVER (PARTITION BY target.fk ORDER BY sort) rank
     *         FROM target
     *         WHERE target.fk IN (bk list)
     *     ) ranking ON ranking.pk = target.pk AND ranking.rank <= limit
     * )
     * ORDER BY sort
     * ```
     *
     * @param Query $fetchQuery The fetch query.
     * @param Query $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return Query
     */
    protected function _inSubqueryJoin(Query $fetchQuery, Query $rankQuery, int $limit): Query
    {
        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "{$rankQuery->getRepository()->getTable()}.{$primaryKey}";
        }

        $joinKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $joinKeys["__ranked__{$this->targetAlias}.{$primaryKey}"] =
                $fetchQuery->identifier("{$rankQuery->getRepository()->getTable()}.{$primaryKey}");
        }

        $filterSubquery = $rankQuery->getRepository()->getConnection()
            ->newQuery()
            ->select($primaryKeys)

            // Need to use either the unaliased table, or an alias different to the target alias, as
            // for Sqlite the tuple comparison will be rewritten, resulting in the outer conditions
            // moving into the inner query, where the outer target alias would then match against the
            // inner alias.

            ->from($rankQuery->getRepository()->getTable())
            ->innerJoin(
                ["__ranked__{$this->targetAlias}" => $rankQuery],
                $joinKeys + ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer']
            );

        // Add conditions to filter the fetch query via the filter query.
        // This will generate a `pk IN (subquery)` condition.
        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys[] = "{$this->targetAlias}.{$primaryKey}";
        }
        $this->_addFilteringCondition($fetchQuery, $primaryKeys, $filterSubquery);

        return $fetchQuery;
    }

    /**
     * ```sql
     * SELECT fields
     * FROM target
     * WHERE target.fk IN (bk list) AND target.pk IN (
     *     SELECT ranking.pk
     *     FROM (
     *         SELECT target.pk, ROW_NUMBER() OVER (PARTITION BY target.fk ORDER BY sort) rank
     *         FROM target
     *         WHERE target.fk IN (bk list)
     *     ) ranking
     *     WHERE ranking.rank <= limit
     * )
     * ORDER BY sort
     * ```
     *
     * @param Query $fetchQuery The fetch query.
     * @param Query $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return Query
     */
    protected function _inSubqueryTable(Query $fetchQuery, Query $rankQuery, int $limit): Query
    {
        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "__ranked__{$this->targetAlias}.{$primaryKey}";
        }

        $filterSubquery = $rankQuery->getRepository()->getConnection()
            ->newQuery()
            ->select($primaryKeys)
            ->from(["__ranked__{$this->targetAlias}" => $rankQuery])
            ->where(
                ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer'],
                true
            );

        // Add conditions to filter the fetch query via the filter query.
        // This will generate a `pk IN (subquery)` condition.
        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys[] = "{$this->targetAlias}.{$primaryKey}";
        }
        $this->_addFilteringCondition($fetchQuery, $primaryKeys, $filterSubquery);

        return $fetchQuery;
    }

    /**
     * ```sql
     * WITH ranking AS (
     *     SELECT target.pk pk, ROW_NUMBER() OVER (PARTITION BY target.fk ORDER BY sort) rank
     *     FROM target
     *     WHERE target.fk IN (bk list)
     * )
     * SELECT fields
     * FROM target
     * INNER JOIN ranking ON ranking.pk = target.pk AND ranking.rank <= limit
     * WHERE target.fk IN (bk list)
     * ORDER BY sort
     * ```
     *
     * @param Query $fetchQuery The fetch query.
     * @param Query $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return Query
     */
    protected function _innerJoinCTE(Query $fetchQuery, Query $rankQuery, int $limit): Query
    {
        $fetchQuery
            ->with(function (CommonTableExpression $cte) use ($rankQuery) {
                return $cte
                    ->name("__ranked__{$this->targetAlias}")
                    ->query($rankQuery);
            });

        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys["__ranked__{$this->targetAlias}.{$primaryKey}"] =
                $fetchQuery->identifier("{$this->targetAlias}.{$primaryKey}");
        }

        $fetchQuery
            ->innerJoin(
                "__ranked__{$this->targetAlias}",
                $primaryKeys + ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer']
            );

        return $fetchQuery;
    }

    /**
     * ```sql
     * SELECT fields
     * FROM target
     * INNER JOIN (
     *     SELECT target.pk pk, ROW_NUMBER() OVER (PARTITION BY target.fk ORDER BY sort) rank
     *     FROM target
     *     WHERE target.fk IN (bk list)
     * ) ranking ON ranking.pk = target.pk AND ranking.rank <= limit
     * WHERE target.fk IN (bk list)
     * ORDER BY sort
     * ```
     *
     * @param Query $fetchQuery The fetch query.
     * @param Query $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return Query
     */
    protected function _innerJoinSubquery(Query $fetchQuery, Query $rankQuery, int $limit): Query
    {
        $primaryKeys = [];
        foreach ((array)$rankQuery->getRepository()->getPrimaryKey() as $primaryKey) {
            $primaryKeys["__ranked__{$this->targetAlias}.{$primaryKey}"] =
                $fetchQuery->identifier("{$this->targetAlias}.{$primaryKey}");
        }

        $fetchQuery
            ->innerJoin(
                ["__ranked__{$this->targetAlias}" => $rankQuery],
                $primaryKeys + ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer']
            );

        return $fetchQuery;
    }
}
