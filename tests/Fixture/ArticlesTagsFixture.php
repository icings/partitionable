<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesTagsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'article_id' => ['type' => 'integer'],
        'article_id2' => ['type' => 'integer'],
        'tag_id' => ['type' => 'integer'],
        'tag_id2' => ['type' => 'integer'],
        'weight' => ['type' => 'integer'],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        '_indexes' => [
            'articles_tags_article_id' => ['type' => 'index', 'columns' => ['article_id']],
            'articles_tags_article_id2' => ['type' => 'index', 'columns' => ['article_id2']],
            'articles_tags_tag_id' => ['type' => 'index', 'columns' => ['article_id']],
            'articles_tags_tag_id2' => ['type' => 'index', 'columns' => ['article_id2']],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'article_id' => 1,
                'article_id2' => 2,
                'tag_id' => 1,
                'tag_id2' => 2,
                'weight' => 1,
                'created' => '2019-01-01 00:00:00',
                'modified' => '2019-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'article_id' => 1,
                'article_id2' => 2,
                'tag_id' => 2,
                'tag_id2' => 3,
                'weight' => 2,
                'created' => '2019-01-02 00:00:00',
                'modified' => '2019-01-02 00:00:00',
            ],
            [
                'id' => 3,
                'article_id' => 1,
                'article_id2' => 2,
                'tag_id' => 3,
                'tag_id2' => 4,
                'weight' => 3,
                'created' => '2019-01-03 00:00:00',
                'modified' => '2019-01-03 00:00:00',
            ],

            [
                'id' => 4,
                'article_id' => 2,
                'article_id2' => 3,
                'tag_id' => 1,
                'tag_id2' => 2,
                'weight' => 1,
                'created' => '2019-01-04 00:00:00',
                'modified' => '2019-01-04 00:00:00',
            ],
            [
                'id' => 5,
                'article_id' => 2,
                'article_id2' => 3,
                'tag_id' => 2,
                'tag_id2' => 3,
                'weight' => 2,
                'created' => '2019-01-05 00:00:00',
                'modified' => '2019-01-05 00:00:00',
            ],
            [
                'id' => 6,
                'article_id' => 2,
                'article_id2' => 3,
                'tag_id' => 3,
                'tag_id2' => 4,
                'weight' => 3,
                'created' => '2019-01-06 00:00:00',
                'modified' => '2019-01-06 00:00:00',
            ],
        ];
        parent::init();
    }
}
