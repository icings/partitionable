<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class AuthorsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                #'id' => 1,
                'name' => 'John Doe',
            ],
            [
                #'id' => 2,
                'title' => 'Jane Doe',
            ],
        ];
        parent::init();
    }
}
