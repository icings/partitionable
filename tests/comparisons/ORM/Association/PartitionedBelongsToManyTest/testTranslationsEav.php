<?php
return [
    [
        'id' => 1,
        'id2' => 2,
        'top_graduated_courses' => [
            [
                'id' => 2,
                'id2' => 3,
                'university_id' => 1,
                'name' => 'Kurs B',
                'online' => true,
                '_joinData' => [
                    'id' => 2,
                    'student_id' => 1,
                    'student_id2' => 2,
                    'course_id' => 2,
                    'course_id2' => 3,
                    'grade' => 1,
                ],
                '_locale' => 'de_DE'
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
                '_locale' => 'de_DE'
            ]
        ]
    ],
    [
        'id' => 2,
        'id2' => 3,
        'top_graduated_courses' => [
            [
                'id' => 5,
                'id2' => 6,
                'university_id' => 1,
                'name' => 'Kurs E',
                'online' => true,
                '_joinData' => [
                    'id' => 10,
                    'student_id' => 2,
                    'student_id2' => 3,
                    'course_id' => 5,
                    'course_id2' => 6,
                    'grade' => 1,
                ],
                '_locale' => 'de_DE'
            ],
            [
                'id' => 4,
                'id2' => 5,
                'university_id' => 1,
                'name' => 'Course D',
                'online' => false,
                '_joinData' => [
                    'id' => 9,
                    'student_id' => 2,
                    'student_id2' => 3,
                    'course_id' => 4,
                    'course_id2' => 5,
                    'grade' => 2,
                ],
                '_locale' => 'de_DE'
            ]
        ]
    ]
];
