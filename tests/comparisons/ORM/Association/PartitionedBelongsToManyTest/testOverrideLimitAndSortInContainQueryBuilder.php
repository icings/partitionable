<?php
return [
    [
        'id' => 1,
        'id2' => 2,
        'top_graduated_courses' => [
            [
                'id' => 5,
                'id2' => 6,
                'university_id' => 1,
                'name' => 'Course E',
                'online' => true,
                '_joinData' => [
                    'id' => 5,
                    'student_id' => 1,
                    'student_id2' => 2,
                    'course_id' => 5,
                    'course_id2' => 6,
                    'grade' => 4,
                ]
            ],
            [
                'id' => 4,
                'id2' => 5,
                'university_id' => 1,
                'name' => 'Course D',
                'online' => false,
                '_joinData' => [
                    'id' => 4,
                    'student_id' => 1,
                    'student_id2' => 2,
                    'course_id' => 4,
                    'course_id2' => 5,
                    'grade' => 3,
                ]
            ]
        ]
    ],
    [
        'id' => 2,
        'id2' => 3,
        'top_graduated_courses' => [
            [
                'id' => 2,
                'id2' => 3,
                'university_id' => 1,
                'name' => 'Course B',
                'online' => true,
                '_joinData' => [
                    'id' => 7,
                    'student_id' => 2,
                    'student_id2' => 3,
                    'course_id' => 2,
                    'course_id2' => 3,
                    'grade' => 4,
                ]
            ],
            [
                'id' => 3,
                'id2' => 4,
                'university_id' => 1,
                'name' => 'Course C',
                'online' => true,
                '_joinData' => [
                    'id' => 8,
                    'student_id' => 2,
                    'student_id2' => 3,
                    'course_id' => 3,
                    'course_id2' => 4,
                    'grade' => 3,
                ]
            ]
        ]
    ]
];
