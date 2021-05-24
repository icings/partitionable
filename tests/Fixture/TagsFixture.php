<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'id2' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer'],
        'title' => ['type' => 'string'],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
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
                'title' => 'Foo',
                'created' => '2019-01-01 00:00:00',
                'modified' => '2019-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'id2' => 3,
                'author_id' => 2,
                'title' => 'Bar',
                'created' => '2019-01-02 00:00:00',
                'modified' => '2019-01-02 00:00:00',
            ],
            [
                'id' => 3,
                'id2' => 4,
                'author_id' => 2,
                'title' => 'Baz',
                'created' => '2019-01-03 00:00:00',
                'modified' => '2019-01-03 00:00:00',
            ],
        ];
        parent::init();
    }
}
