<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CourseMembershipsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'student_id' => ['type' => 'integer'],
        'student_id2' => ['type' => 'integer'],
        'course_id' => ['type' => 'integer'],
        'course_id2' => ['type' => 'integer'],
        'grade' => ['type' => 'integer', 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['student_id', 'student_id2', 'course_id', 'course_id2']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'student_id' => 1,
                'student_id2' => 2,
                'course_id' => 1,
                'course_id2' => 2,
                'grade' => null,
            ],
            [
                'id' => 2,
                'student_id' => 1,
                'student_id2' => 2,
                'course_id' => 2,
                'course_id2' => 3,
                'grade' => 1,
            ],
            [
                'id' => 3,
                'student_id' => 1,
                'student_id2' => 2,
                'course_id' => 3,
                'course_id2' => 4,
                'grade' => 2,
            ],
            [
                'id' => 4,
                'student_id' => 1,
                'student_id2' => 2,
                'course_id' => 4,
                'course_id2' => 5,
                'grade' => 3,
            ],
            [
                'id' => 5,
                'student_id' => 1,
                'student_id2' => 2,
                'course_id' => 5,
                'course_id2' => 6,
                'grade' => 4,
            ],

            [
                'id' => 6,
                'student_id' => 2,
                'student_id2' => 3,
                'course_id' => 1,
                'course_id2' => 2,
                'grade' => null,
            ],
            [
                'id' => 7,
                'student_id' => 2,
                'student_id2' => 3,
                'course_id' => 2,
                'course_id2' => 3,
                'grade' => 4,
            ],
            [
                'id' => 8,
                'student_id' => 2,
                'student_id2' => 3,
                'course_id' => 3,
                'course_id2' => 4,
                'grade' => 3,
            ],
            [
                'id' => 9,
                'student_id' => 2,
                'student_id2' => 3,
                'course_id' => 4,
                'course_id2' => 5,
                'grade' => 2,
            ],
            [
                'id' => 10,
                'student_id' => 2,
                'student_id2' => 3,
                'course_id' => 5,
                'course_id2' => 6,
                'grade' => 1,
            ],
        ];
        parent::init();
    }
}
