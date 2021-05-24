<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;

class CoursesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('courses');
        $this->setPrimaryKey(['id', 'id2']);

        $this
            ->belongsTo('Universities');

        $this
            ->belongsToMany('Students')
            ->setThrough('CourseMemberships')
            ->setForeignKey(['course_id', 'course_id2'])
            ->setTargetForeignKey(['student_id', 'student_id2'])
            ->setSort(['Students.id' => 'ASC']);
    }

    public function findOnline(Query $query, array $options): Query
    {
        return $query->where([
            $this->aliasField('online') => true,
        ]);
    }
}
