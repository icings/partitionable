<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Table;

class UniversitiesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('universities');
        $this->setPrimaryKey('id');

        $this
            ->hasMany('Courses')
            ->setSort(['Courses.id' => 'ASC']);
    }
}
