<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM\Association\Loader;

use Cake\Database\Expression\CommonTableExpression;
use Cake\ORM\Association;
use Cake\ORM\Association\Loader\SelectWithPivotLoader;
use Cake\ORM\Query;
use RuntimeException;

/**
 * @internal
 */
class PartitionableSelectWithPivotLoader extends SelectWithPivotLoader
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
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "{$this->junctionAssociationName}.{$primaryKey}";
        }

        $foreignKeys = [];
        foreach ((array)$this->foreignKey as $foreignKey) {
            $foreignKeys[$foreignKey] = "{$this->junctionAssociationName}.{$foreignKey}";
        }

        $order = $this->_getOrder($query);
        if (!$order) {
            throw new RuntimeException('Partitioning requires a sort order.');
        }

        return $query
            ->select($primaryKeys)
            ->select([
                '__row_number' => $query
                    ->func()
                    ->rowNumber()
                    ->over()
                    ->partition($foreignKeys)
                    ->order($order),
            ]);
    }

    /**
     * ```sql
     * SELECT fields
     * FROM target
     * INNER JOIN junction ON junction.fk = target.pk
     * WHERE junction.fk IN (bk list) AND junction.pk IN (
     *     WITH ranking AS (
     *         SELECT junction.pk pk, ROW_NUMBER() OVER (PARTITION BY junction.fk ORDER BY sort) rank
     *         FROM target
     *         INNER JOIN junction ON junction.fk = target.pk
     *         WHERE junction.fk IN (bk list)
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
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
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
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[] = "{$this->junctionAssociationName}.{$primaryKey}";
        }
        $this->_addFilteringCondition($fetchQuery, $primaryKeys, $filterSubquery);

        return $fetchQuery;
    }

    /**
     * ```sql
     * SELECT fields
     * FROM target
     * INNER JOIN junction ON junction.fk = target.pk
     * WHERE junction.fk IN (bk list) AND junction.pk IN (
     *     SELECT junction.pk
     *     FROM junction
     *     INNER JOIN (
     *         SELECT junction.pk pk, ROW_NUMBER() OVER (PARTITION BY junction.fk ORDER BY sort) rank
     *         FROM target
     *         INNER JOIN junction ON junction.fk = target.pk
     *         WHERE junction.fk IN (bk list)
     *     ) ranking ON ranking.pk = junction.pk AND ranking.rank <= limit
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
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "__filter__{$this->junctionAssoc->getTable()}.{$primaryKey}";
        }

        $joinKeys = [];
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $joinKeys["__ranked__{$this->targetAlias}.{$primaryKey}"] =
                $fetchQuery->identifier("__filter__{$this->junctionAssoc->getTable()}.{$primaryKey}");
        }

        $filterSubquery = $rankQuery->getRepository()->getConnection()
            ->newQuery()
            ->select($primaryKeys)

            // Need to use an alias different to the target alias, as for Sqlite
            // and Sqlserver the tuple comparison will be rewritten, resulting in
            // the outer conditions moving into the inner query, where the outer
            // target alias would then match against the inner alias.

            ->from([
                "__filter__{$this->junctionAssoc->getTable()}" => $this->junctionAssoc->getTable(),
            ])
            ->innerJoin(
                ["__ranked__{$this->targetAlias}" => $rankQuery],
                $joinKeys + ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer']
            );

        // Add conditions to filter the fetch query via the filter query.
        // This will generate a `pk IN (subquery)` condition.
        $primaryKeys = [];
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[] = "{$this->junctionAssociationName}.{$primaryKey}";
        }
        $this->_addFilteringCondition($fetchQuery, $primaryKeys, $filterSubquery);

        return $fetchQuery;
    }

    /**
     * ```sql
     * SELECT fields
     * FROM target
     * INNER JOIN junction ON junction.fk = target.pk
     * WHERE junction.fk IN (bk list) AND junction.pk IN (
     *     SELECT ranking.pk
     *     FROM (
     *         SELECT junction.pk, ROW_NUMBER() OVER (PARTITION BY junction.fk ORDER BY sort) rank
     *         FROM target
     *         INNER JOIN junction ON junction.fk = target.pk
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
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "__ranked__{$this->targetAlias}.{$primaryKey}";
        }

        $filterSubquery = $rankQuery->getRepository()->getConnection()
            ->newQuery()
            ->select($primaryKeys)
            ->from(["__ranked__{$this->targetAlias}" => $rankQuery])
            ->where(
                ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer']
            );

        // Add conditions to filter the fetch query via the filter query.
        // This will generate a `pk IN (subquery)` condition.
        $primaryKeys = [];
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[] = "{$this->junctionAssociationName}.{$primaryKey}";
        }
        $this->_addFilteringCondition($fetchQuery, $primaryKeys, $filterSubquery);

        return $fetchQuery;
    }

    /**
     * ```sql
     * WITH ranking AS (
     *     SELECT junction.pk pk, ROW_NUMBER() OVER (PARTITION BY junction.fk ORDER BY sort) rank
     *     FROM target
     *     INNER JOIN junction ON junction.fk = target.pk
     *     WHERE junction.fk IN (bk list)
     * )
     * SELECT fields
     * FROM target
     * INNER JOIN junction ON junction.fk = target.pk
     * INNER JOIN ranking ON ranking.pk = junction.pk AND ranking.rank <= limit
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
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys["__ranked__{$this->targetAlias}.{$primaryKey}"] =
                $fetchQuery->identifier("{$this->junctionAssociationName}.{$primaryKey}");
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
     * INNER JOIN junction ON junction.fk = target.pk
     * INNER JOIN (
     *     SELECT junction.pk pk, ROW_NUMBER() OVER (PARTITION BY junction.fk ORDER BY sort) rank
     *     FROM target
     *     INNER JOIN junction ON junction.fk = target.pk
     *     WHERE target.fk IN (bk list)
     * ) ranking ON ranking.pk = junction.pk AND ranking.rank <= limit
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
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys["__ranked__{$this->targetAlias}.{$primaryKey}"] =
                $fetchQuery->identifier("{$this->junctionAssociationName}.{$primaryKey}");
        }

        $fetchQuery
            ->innerJoin(
                ["__ranked__{$this->targetAlias}" => $rankQuery],
                $primaryKeys + ["__ranked__{$this->targetAlias}.__row_number <=" => $limit],
                ["__ranked__{$this->targetAlias}.__row_number" => 'integer']
            );

        return $fetchQuery;
    }

    /**
     * @inheritDoc
     */
    protected function _buildResultMap(Query $fetchQuery, array $options): array
    {
        $resultMap = parent::_buildResultMap($fetchQuery, $options);

        // Reduce the result map to the single result format in case the
        // association is of a one-to-one type.
        //
        // Turns
        //
        // [
        //     '1' => [
        //         [/* result data */]
        //     ],
        //     '2' => [
        //         [/* result data */]
        //     ]
        // ]
        //
        // into
        //
        // [
        //     '1' => [/* result data */],
        //     '2' => [/* result data */]
        // ]
        if ($this->associationType === Association::ONE_TO_ONE) {
            $resultMap = array_combine(
                array_keys($resultMap),
                array_column($resultMap, 0)
            );
            /** @var array<string, mixed> $resultMap */
            $resultMap = array_filter((array)$resultMap);
        }

        return $resultMap;
    }
}
