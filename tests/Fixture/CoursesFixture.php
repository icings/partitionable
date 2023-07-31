<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CoursesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'id2' => 2,
                'university_id' => 1,
                'name' => 'Course A',
                'online' => true,
            ],
            [
                'id' => 2,
                'id2' => 3,
                'university_id' => 1,
                'name' => 'Course B',
                'online' => true,
            ],
            [
                'id' => 3,
                'id2' => 4,
                'university_id' => 1,
                'name' => 'Course C',
                'online' => true,
            ],
            [
                'id' => 4,
                'id2' => 5,
                'university_id' => 1,
                'name' => 'Course D',
                'online' => false,
            ],
            [
                'id' => 5,
                'id2' => 6,
                'university_id' => 1,
                'name' => 'Course E',
                'online' => true,
            ],
        ];
        parent::init();
    }
}
