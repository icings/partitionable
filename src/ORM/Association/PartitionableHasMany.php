<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\ORM\Association;

use Cake\ORM\Association\HasMany;
use Closure;
use Icings\Partitionable\ORM\Association\Loader\PartitionableSelectLoader;

class PartitionableHasMany extends HasMany implements PartitionableAssociationInterface
{
    use PartitionableAssociationTrait;

    /**
     * @inheritDoc
     */
    public function eagerLoader(array $options): Closure
    {
        $loader = new PartitionableSelectLoader([
            'alias' => $this->getAlias(),
            'sourceAlias' => $this->getSource()->getAlias(),
            'targetAlias' => $this->getTarget()->getAlias(),
            'foreignKey' => $this->getForeignKey(),
            'bindingKey' => $this->getBindingKey(),
            'strategy' => $this->getStrategy(),
            'associationType' => $this->type(),
            'sort' => $this->getSort(),
            'finder' => [$this, 'find'],
            'limit' => $this->getLimit(),
            'target' => $this->getTarget(),
            'filterStrategy' => $this->getFilterStrategy(),
        ]);

        return $loader->buildEagerLoader($options);
    }
}
