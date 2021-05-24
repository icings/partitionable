<?php
return [
    [
        'id' => 1,
        'id2' => 2,
        'top_comments' => [
            [
                'id' => 4,
                'id2' => 5,
                'article_id' => 1,
                'article_id2' => 2,
                'author_id' => 2,
                'votes' => 4,
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => false,
                '_locale' => 'de_DE'
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
                '_locale' => 'de_DE'
            ]
        ]
    ],
    [
        'id' => 2,
        'id2' => 3,
        'top_comments' => [
            [
                'id' => 5,
                'id2' => 6,
                'article_id' => 2,
                'article_id2' => 3,
                'author_id' => 1,
                'votes' => 10,
                'body' => 'Fix Schwyz! quäkt Jürgen blöd vom Paß',
                'published' => true,
                '_locale' => 'de_DE'
            ],
            [
                'id' => 6,
                'id2' => 7,
                'article_id' => 2,
                'article_id2' => 3,
                'author_id' => 1,
                'votes' => 9,
                'body' => 'Quod erat demonstrandum.',
                'published' => true,
                '_locale' => 'de_DE'
            ]
        ]
    ]
];
