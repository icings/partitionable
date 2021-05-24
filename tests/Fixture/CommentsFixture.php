<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CommentsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'id2' => ['type' => 'integer'],
        'article_id' => ['type' => 'integer'],
        'article_id2' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer'],
        'votes' => ['type' => 'integer'],
        'body' => ['type' => 'text'],
        'published' => ['type' => 'boolean'],
        '_indexes' => [
            'comments_article_id' => ['type' => 'index', 'columns' => ['article_id', 'article_id2']],
            'comments_top_comments_sort' => ['type' => 'index', 'columns' => ['votes', 'id']],
        ],
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
                'article_id' => 1,
                'article_id2' => 2,
                'author_id' => 1,
                'votes' => 1,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => true,
            ],
            [
                'id' => 2,
                'id2' => 3,
                'article_id' => 1,
                'article_id2' => 2,
                'author_id' => 1,
                'votes' => 2,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => true,
            ],
            [
                'id' => 3,
                'id2' => 4,
                'article_id' => 1,
                'article_id2' => 2,
                'author_id' => 2,
                'votes' => 3,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => true,
            ],
            [
                'id' => 4,
                'id2' => 5,
                'article_id' => 1,
                'article_id2' => 2,
                'author_id' => 2,
                'votes' => 4,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => false,
            ],

            [
                'id' => 5,
                'id2' => 6,
                'article_id' => 2,
                'article_id2' => 3,
                'author_id' => 1,
                'votes' => 10,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => true,
            ],
            [
                'id' => 6,
                'id2' => 7,
                'article_id' => 2,
                'article_id2' => 3,
                'author_id' => 1,
                'votes' => 9,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => true,
            ],
            [
                'id' => 7,
                'id2' => 8,
                'article_id' => 2,
                'article_id2' => 3,
                'author_id' => 2,
                'votes' => 8,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => true,
            ],
            [
                'id' => 8,
                'id2' => 9,
                'article_id' => 2,
                'article_id2' => 3,
                'author_id' => 2,
                'votes' => 7,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => false,
            ],
        ];
        parent::init();
    }
}
