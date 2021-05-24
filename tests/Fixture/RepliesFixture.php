<?php
namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class RepliesFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer'],
        'comment_id' => ['type' => 'integer'],
        'comment_id2' => ['type' => 'integer'],
        'body' => ['type' => 'text'],
        '_indexes' => [
            'replies_author_id' => ['type' => 'index', 'columns' => ['author_id']],
            'replies_comment_id' => ['type' => 'index', 'columns' => ['comment_id', 'comment_id2']],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                #'id' => 1,
                'author_id' => 1,
                'comment_id' => 1,
                'comment_id2' => 2,
                'body' => 'Lorem ipsum dolor sit amet',
            ],
            [
                #'id' => 2,
                'author_id' => 2,
                'comment_id' => 1,
                'comment_id2' => 2,
                'body' => 'Lorem ipsum dolor sit amet',
            ],

            [
                #'id' => 3,
                'author_id' => 1,
                'comment_id' => 2,
                'comment_id2' => 3,
                'body' => 'Lorem ipsum dolor sit amet',
            ],
            [
                #'id' => 4,
                'author_id' => 2,
                'comment_id' => 2,
                'comment_id2' => 3,
                'body' => 'Lorem ipsum dolor sit amet',
            ],

            [
                #'id' => 5,
                'author_id' => 1,
                'comment_id' => 3,
                'comment_id2' => 4,
                'body' => 'Lorem ipsum dolor sit amet',
            ],
            [
                #'id' => 6,
                'author_id' => 2,
                'comment_id' => 3,
                'comment_id2' => 4,
                'body' => 'Lorem ipsum dolor sit amet',
            ],

            [
                #'id' => 7,
                'author_id' => 1,
                'comment_id' => 4,
                'comment_id2' => 5,
                'body' => 'Lorem ipsum dolor sit amet',
            ],
            [
                #'id' => 8,
                'author_id' => 2,
                'comment_id' => 4,
                'comment_id2' => 5,
                'body' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
