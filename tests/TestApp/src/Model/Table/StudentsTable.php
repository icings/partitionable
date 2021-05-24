<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Table;
use Icings\Partitionable\ORM\AssociationsTrait;

class StudentsTable extends Table
{
    use AssociationsTrait;

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('students');
        $this->setPrimaryKey(['id', 'id2']);

        $this
            ->belongsTo('Universities');
    }
}
