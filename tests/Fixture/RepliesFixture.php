<?php
namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class RepliesFixture extends TestFixture
{
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
