<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Table;

class CoursesI18nTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('courses_i18n');
        $this->setPrimaryKey('id');
    }
}
