<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Table;

class CourseMembershipsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('course_memberships');
        $this->setPrimaryKey(['student_id', 'student_id2', 'course_id', 'course_id2']);

        $this
            ->belongsTo('Students')
            ->setForeignKey(['student_id', 'student_id2']);

        $this
            ->belongsTo('Courses')
            ->setForeignKey(['course_id', 'course_id2']);
    }
}
