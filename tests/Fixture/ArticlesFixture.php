<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'id2' => 2,
                'author_id' => 1,
                'title' => 'Lorem ipsum',
                'body' => 'Lorem ipsum dolor sit amet',
            ],
            [
                'id' => 2,
                'id2' => 3,
                'author_id' => 2,
                'title' => 'Lorem ipsum',
                'body' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
