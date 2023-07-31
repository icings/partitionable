<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class StudentsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'id2' => 2,
                'university_id' => 1,
                'name' => 'John Doe',
            ],
            [
                'id' => 2,
                'id2' => 3,
                'university_id' => 1,
                'title' => 'Jane Doe',
            ],
        ];
        parent::init();
    }
}
