<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM;

use Cake\ORM\Table;
use Icings\Partitionable\ORM\Association\PartitionableBelongsToMany;
use Icings\Partitionable\ORM\Association\PartitionableHasMany;

/**
 * @mixin Table
 */
trait AssociationsTrait
{
    /**
     * Creates a new `PartitionableHasMany` association.
     *
     * The options array accepts the following keys additionally to the ones accepted
     * by `hasMany()`:
     *
     * - `limit` (`int|null`): The partition limit.
     * - `singleResult` (`bool`): Whether to enable the single result mode.
     * - `filterStrategy` (`string`): The filter strategy.
     *
     * @param string $associated The alias for the target table.
     * @param array $options List of options to configure the association definition.
     * @return PartitionableHasMany
     * @see \Cake\ORM\Table::hasMany()
     */
    public function partitionableHasMany(string $associated, array $options = []): PartitionableHasMany
    {
        $options += ['sourceTable' => $this];

        $association = $this->_associations->load(PartitionableHasMany::class, $associated, $options);
        assert($association instanceof PartitionableHasMany);

        return $association;
    }

    /**
     * Creates a new `PartitionableBelongsToMany` association.
     *
     * The options array accepts the following keys additionally to the ones accepted
     * by `belongsToMany()`:
     *
     * - `limit` (`int|null`): The partition limit.
     * - `singleResult` (`bool`): Whether to enable the single result mode.
     * - `filterStrategy` (`string`): The filter strategy.
     *
     * @param string $associated The alias for the target table.
     * @param array $options List of options to configure the association definition.
     * @return PartitionableBelongsToMany
     * @see \Cake\ORM\Table::belongsToMany()
     */
    public function partitionableBelongsToMany(string $associated, array $options = []): PartitionableBelongsToMany
    {
        $options += ['sourceTable' => $this];

        $association = $this->_associations->load(PartitionableBelongsToMany::class, $associated, $options);
        assert($association instanceof PartitionableBelongsToMany);

        return $association;
    }
}
