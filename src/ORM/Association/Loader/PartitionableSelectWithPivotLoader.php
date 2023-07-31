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
use Cake\ORM\Query\SelectQuery;
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
     * @param SelectQuery $query The query to turn into the rank query.
     * @return SelectQuery
     */
    protected function _buildRankQuery(SelectQuery $query): SelectQuery
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
                    ->orderBy($order),
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
     * @param SelectQuery $fetchQuery The fetch query.
     * @param SelectQuery $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return SelectQuery
     */
    protected function _inSubqueryCTE(SelectQuery $fetchQuery, SelectQuery $rankQuery, int $limit): SelectQuery
    {
        $primaryKeys = [];
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "__ranked__{$this->targetAlias}.{$primaryKey}";
        }

        $filterSubquery = $rankQuery->getRepository()->getConnection()
            ->selectQuery()
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
     * @param SelectQuery $fetchQuery The fetch query.
     * @param SelectQuery $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return SelectQuery
     */
    protected function _inSubqueryJoin(SelectQuery $fetchQuery, SelectQuery $rankQuery, int $limit): SelectQuery
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
            ->selectQuery()
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
     * @param SelectQuery $fetchQuery The fetch query.
     * @param SelectQuery $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return SelectQuery
     */
    protected function _inSubqueryTable(SelectQuery $fetchQuery, SelectQuery $rankQuery, int $limit): SelectQuery
    {
        $primaryKeys = [];
        foreach ((array)$this->junctionAssoc->getPrimaryKey() as $primaryKey) {
            $primaryKeys[$primaryKey] = "__ranked__{$this->targetAlias}.{$primaryKey}";
        }

        $filterSubquery = $rankQuery->getRepository()->getConnection()
            ->selectQuery()
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
     * @param SelectQuery $fetchQuery The fetch query.
     * @param SelectQuery $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return SelectQuery
     */
    protected function _innerJoinCTE(SelectQuery $fetchQuery, SelectQuery $rankQuery, int $limit): SelectQuery
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
     * @param SelectQuery $fetchQuery The fetch query.
     * @param SelectQuery $rankQuery The rank query.
     * @param int $limit The partition limit.
     * @return SelectQuery
     */
    protected function _innerJoinSubquery(SelectQuery $fetchQuery, SelectQuery $rankQuery, int $limit): SelectQuery
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
    protected function _buildResultMap(SelectQuery $fetchQuery, array $options): array
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
            $resultMap = array_filter($resultMap);
        }

        return $resultMap;
    }
}
