<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM\Association;

interface PartitionableAssociationInterface
{
    public const FILTER_IN_SUBQUERY_CTE = 'inSubqueryCTE';
    public const FILTER_IN_SUBQUERY_JOIN = 'inSubqueryJoin';
    public const FILTER_IN_SUBQUERY_TABLE = 'inSubqueryTable';
    public const FILTER_INNER_JOIN_CTE = 'innerJoinCTE';
    public const FILTER_INNER_JOIN_SUBQUERY = 'innerJoinSubquery';
}
