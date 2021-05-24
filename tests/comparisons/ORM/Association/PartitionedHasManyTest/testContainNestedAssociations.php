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
                'replies' => [
                    [
                        'id' => 7,
                        'author_id' => 1,
                        'comment_id' => 4,
                        'comment_id2' => 5,
                        'body' => 'Lorem ipsum dolor sit amet',
                        'comment' => [
                            'id' => 4,
                            'id2' => 5,
                            'article_id' => 1,
                            'article_id2' => 2,
                            'author_id' => 2,
                            'votes' => 4,
                            'body' => 'Lorem ipsum dolor sit amet',
                            'published' => false
                        ]
                    ],
                    [
                        'id' => 8,
                        'author_id' => 2,
                        'comment_id' => 4,
                        'comment_id2' => 5,
                        'body' => 'Lorem ipsum dolor sit amet',
                        'comment' => [
                            'id' => 4,
                            'id2' => 5,
                            'article_id' => 1,
                            'article_id2' => 2,
                            'author_id' => 2,
                            'votes' => 4,
                            'body' => 'Lorem ipsum dolor sit amet',
                            'published' => false
                        ]
                    ]
                ],
                'article' => [
                    'id' => 1,
                    'id2' => 2,
                    'author_id' => 1,
                    'title' => 'Lorem ipsum',
                    'body' => 'Lorem ipsum dolor sit amet',
                    'author' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'comments' => [
                            [
                                'id' => 1,
                                'id2' => 2,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 1,
                                'votes' => 1,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 2,
                                'id2' => 3,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 1,
                                'votes' => 2,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 5,
                                'id2' => 6,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 1,
                                'votes' => 10,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 6,
                                'id2' => 7,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 1,
                                'votes' => 9,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ]
                        ]
                    ]
                ]
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
                'replies' => [
                    [
                        'id' => 5,
                        'author_id' => 1,
                        'comment_id' => 3,
                        'comment_id2' => 4,
                        'body' => 'Lorem ipsum dolor sit amet',
                        'comment' => [
                            'id' => 3,
                            'id2' => 4,
                            'article_id' => 1,
                            'article_id2' => 2,
                            'author_id' => 2,
                            'votes' => 3,
                            'body' => 'Lorem ipsum dolor sit amet',
                            'published' => true
                        ]
                    ],
                    [
                        'id' => 6,
                        'author_id' => 2,
                        'comment_id' => 3,
                        'comment_id2' => 4,
                        'body' => 'Lorem ipsum dolor sit amet',
                        'comment' => [
                            'id' => 3,
                            'id2' => 4,
                            'article_id' => 1,
                            'article_id2' => 2,
                            'author_id' => 2,
                            'votes' => 3,
                            'body' => 'Lorem ipsum dolor sit amet',
                            'published' => true
                        ]
                    ]
                ],
                'article' => [
                    'id' => 1,
                    'id2' => 2,
                    'author_id' => 1,
                    'title' => 'Lorem ipsum',
                    'body' => 'Lorem ipsum dolor sit amet',
                    'author' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'comments' => [
                            [
                                'id' => 1,
                                'id2' => 2,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 1,
                                'votes' => 1,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 2,
                                'id2' => 3,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 1,
                                'votes' => 2,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 5,
                                'id2' => 6,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 1,
                                'votes' => 10,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 6,
                                'id2' => 7,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 1,
                                'votes' => 9,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ]
                        ]
                    ]
                ]
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
                'body' => 'Lorem ipsum dolor sit amet',
                'published' => true,
                'replies' => [],
                'article' => [
                    'id' => 2,
                    'id2' => 3,
                    'author_id' => 2,
                    'title' => 'Lorem ipsum',
                    'body' => 'Lorem ipsum dolor sit amet',
                    'author' => [
                        'id' => 2,
                        'name' => null,
                        'comments' => [
                            [
                                'id' => 3,
                                'id2' => 4,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 2,
                                'votes' => 3,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 4,
                                'id2' => 5,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 2,
                                'votes' => 4,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => false
                            ],
                            [
                                'id' => 7,
                                'id2' => 8,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 2,
                                'votes' => 8,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 8,
                                'id2' => 9,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 2,
                                'votes' => 7,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => false
                            ]
                        ]
                    ]
                ]
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
                'replies' => [],
                'article' => [
                    'id' => 2,
                    'id2' => 3,
                    'author_id' => 2,
                    'title' => 'Lorem ipsum',
                    'body' => 'Lorem ipsum dolor sit amet',
                    'author' => [
                        'id' => 2,
                        'name' => null,
                        'comments' => [
                            [
                                'id' => 3,
                                'id2' => 4,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 2,
                                'votes' => 3,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 4,
                                'id2' => 5,
                                'article_id' => 1,
                                'article_id2' => 2,
                                'author_id' => 2,
                                'votes' => 4,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => false
                            ],
                            [
                                'id' => 7,
                                'id2' => 8,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 2,
                                'votes' => 8,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => true
                            ],
                            [
                                'id' => 8,
                                'id2' => 9,
                                'article_id' => 2,
                                'article_id2' => 3,
                                'author_id' => 2,
                                'votes' => 7,
                                'body' => 'Lorem ipsum dolor sit amet',
                                'published' => false
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
