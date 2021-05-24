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
    public $fields = [
        'id' => ['type' => 'integer'],
        'id2' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer'],
        'title' => ['type' => 'string'],
        'body' => ['type' => 'text'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'id2']],
        ],
    ];

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
