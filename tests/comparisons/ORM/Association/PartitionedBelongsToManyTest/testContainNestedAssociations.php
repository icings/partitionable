<?php
return [
    'id' => 1,
    'id2' => 2,
    'top_graduated_courses' => [
        [
            'id' => 2,
            'id2' => 3,
            'university_id' => 1,
            'name' => 'Course B',
            'online' => true,
            '_joinData' => [
                'id' => 2,
                'student_id' => 1,
                'student_id2' => 2,
                'course_id' => 2,
                'course_id2' => 3,
                'grade' => 1,
                            ],
            'university' => [
                'id' => 1,
                'name' => 'University A',
                'courses' => [
                    [
                        'id' => 1,
                        'id2' => 2,
                        'university_id' => 1,
                        'name' => 'Course A',
                        'online' => true,
                    ],
                    [
                        'id' => 2,
                        'id2' => 3,
                        'university_id' => 1,
                        'name' => 'Course B',
                        'online' => true,
                    ],
                    [
                        'id' => 3,
                        'id2' => 4,
                        'university_id' => 1,
                        'name' => 'Course C',
                        'online' => true,
                    ],
                    [
                        'id' => 4,
                        'id2' => 5,
                        'university_id' => 1,
                        'name' => 'Course D',
                        'online' => false,
                    ],
                    [
                        'id' => 5,
                        'id2' => 6,
                        'university_id' => 1,
                        'name' => 'Course E',
                        'online' => true,
                    ]
                ]
            ],
            'students' => [
                [
                    'id' => 1,
                    'id2' => 2,
                    'university_id' => 1,
                    'name' => 'John Doe',
                    '_joinData' => [
                        'id' => 2,
                        'student_id' => 1,
                        'student_id2' => 2,
                        'course_id' => 2,
                        'course_id2' => 3,
                        'grade' => 1,
                    ],
                    'university' => [
                        'id' => 1,
                        'name' => 'University A',
                        'courses' => [
                            [
                                'id' => 1,
                                'id2' => 2,
                                'university_id' => 1,
                                'name' => 'Course A',
                                'online' => true,
                            ],
                            [
                                'id' => 2,
                                'id2' => 3,
                                'university_id' => 1,
                                'name' => 'Course B',
                                'online' => true,
                            ],
                            [
                                'id' => 3,
                                'id2' => 4,
                                'university_id' => 1,
                                'name' => 'Course C',
                                'online' => true,
                            ],
                            [
                                'id' => 4,
                                'id2' => 5,
                                'university_id' => 1,
                                'name' => 'Course D',
                                'online' => false,
                            ],
                            [
                                'id' => 5,
                                'id2' => 6,
                                'university_id' => 1,
                                'name' => 'Course E',
                                'online' => true,
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'id2' => 3,
                    'university_id' => 1,
                    'name' => null,
                    '_joinData' => [
                        'id' => 7,
                        'student_id' => 2,
                        'student_id2' => 3,
                        'course_id' => 2,
                        'course_id2' => 3,
                        'grade' => 4,
                    ],
                    'university' => [
                        'id' => 1,
                        'name' => 'University A',
                        'courses' => [
                            [
                                'id' => 1,
                                'id2' => 2,
                                'university_id' => 1,
                                'name' => 'Course A',
                                'online' => true,
                            ],
                            [
                                'id' => 2,
                                'id2' => 3,
                                'university_id' => 1,
                                'name' => 'Course B',
                                'online' => true,
                            ],
                            [
                                'id' => 3,
                                'id2' => 4,
                                'university_id' => 1,
                                'name' => 'Course C',
                                'online' => true,
                            ],
                            [
                                'id' => 4,
                                'id2' => 5,
                                'university_id' => 1,
                                'name' => 'Course D',
                                'online' => false,
                            ],
                            [
                                'id' => 5,
                                'id2' => 6,
                                'university_id' => 1,
                                'name' => 'Course E',
                                'online' => true,
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            'id' => 3,
            'id2' => 4,
            'university_id' => 1,
            'name' => 'Course C',
            'online' => true,
            '_joinData' => [
                'id' => 3,
                'student_id' => 1,
                'student_id2' => 2,
                'course_id' => 3,
                'course_id2' => 4,
                'grade' => 2,
            ],
            'university' => [
                'id' => 1,
                'name' => 'University A',
                'courses' => [
                    [
                        'id' => 1,
                        'id2' => 2,
                        'university_id' => 1,
                        'name' => 'Course A',
                        'online' => true,
                    ],
                    [
                        'id' => 2,
                        'id2' => 3,
                        'university_id' => 1,
                        'name' => 'Course B',
                        'online' => true,
                    ],
                    [
                        'id' => 3,
                        'id2' => 4,
                        'university_id' => 1,
                        'name' => 'Course C',
                        'online' => true,
                    ],
                    [
                        'id' => 4,
                        'id2' => 5,
                        'university_id' => 1,
                        'name' => 'Course D',
                        'online' => false,
                    ],
                    [
                        'id' => 5,
                        'id2' => 6,
                        'university_id' => 1,
                        'name' => 'Course E',
                        'online' => true,
                    ]
                ]
            ],
            'students' => [
                [
                    'id' => 1,
                    'id2' => 2,
                    'university_id' => 1,
                    'name' => 'John Doe',
                    '_joinData' => [
                        'id' => 3,
                        'student_id' => 1,
                        'student_id2' => 2,
                        'course_id' => 3,
                        'course_id2' => 4,
                        'grade' => 2,
                    ],
                    'university' => [
                        'id' => 1,
                        'name' => 'University A',
                        'courses' => [
                            [
                                'id' => 1,
                                'id2' => 2,
                                'university_id' => 1,
                                'name' => 'Course A',
                                'online' => true,
                            ],
                            [
                                'id' => 2,
                                'id2' => 3,
                                'university_id' => 1,
                                'name' => 'Course B',
                                'online' => true,
                            ],
                            [
                                'id' => 3,
                                'id2' => 4,
                                'university_id' => 1,
                                'name' => 'Course C',
                                'online' => true,
                            ],
                            [
                                'id' => 4,
                                'id2' => 5,
                                'university_id' => 1,
                                'name' => 'Course D',
                                'online' => false,
                            ],
                            [
                                'id' => 5,
                                'id2' => 6,
                                'university_id' => 1,
                                'name' => 'Course E',
                                'online' => true,
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'id2' => 3,
                    'university_id' => 1,
                    'name' => null,
                    '_joinData' => [
                        'id' => 8,
                        'student_id' => 2,
                        'student_id2' => 3,
                        'course_id' => 3,
                        'course_id2' => 4,
                        'grade' => 3,
                    ],
                    'university' => [
                        'id' => 1,
                        'name' => 'University A',
                        'courses' => [
                            [
                                'id' => 1,
                                'id2' => 2,
                                'university_id' => 1,
                                'name' => 'Course A',
                                'online' => true,
                            ],
                            [
                                'id' => 2,
                                'id2' => 3,
                                'university_id' => 1,
                                'name' => 'Course B',
                                'online' => true,
                            ],
                            [
                                'id' => 3,
                                'id2' => 4,
                                'university_id' => 1,
                                'name' => 'Course C',
                                'online' => true,
                            ],
                            [
                                'id' => 4,
                                'id2' => 5,
                                'university_id' => 1,
                                'name' => 'Course D',
                                'online' => false,
                            ],
                            [
                                'id' => 5,
                                'id2' => 6,
                                'university_id' => 1,
                                'name' => 'Course E',
                                'online' => true,
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
];
