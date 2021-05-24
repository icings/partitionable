<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM\Association;

use Cake\ORM\Association\BelongsToMany;
use Closure;
use Icings\Partitionable\ORM\Association\Loader\PartitionableSelectWithPivotLoader;

class PartitionableBelongsToMany extends BelongsToMany implements PartitionableAssociationInterface
{
    use PartitionableAssociationTrait;

    /**
     * @inheritDoc
     */
    public function eagerLoader(array $options): Closure
    {
        $name = $this->_junctionAssociationName();

        $loader = new PartitionableSelectWithPivotLoader([
            'alias' => $this->getAlias(),
            'sourceAlias' => $this->getSource()->getAlias(),
            'targetAlias' => $this->getTarget()->getAlias(),
            'foreignKey' => $this->getForeignKey(),
            'bindingKey' => $this->getBindingKey(),
            'strategy' => $this->getStrategy(),
            'associationType' => $this->type(),
            'sort' => $this->getSort(),
            'junctionAssociationName' => $name,
            'junctionProperty' => $this->_junctionProperty,
            'junctionAssoc' => $this->getTarget()->getAssociation($name),
            'junctionConditions' => $this->junctionConditions(),
            'finder' => function () {
                return $this->_appendJunctionJoin($this->find(), []);
            },
            'limit' => $this->getLimit(),
            'filterStrategy' => $this->getFilterStrategy(),
        ]);

        return $loader->buildEagerLoader($options);
    }
}
