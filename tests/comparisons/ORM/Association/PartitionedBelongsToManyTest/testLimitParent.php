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
                'name' => 'Course B',
                'online' => true,
                '_joinData' => [
                    'id' => 2,
                    'student_id' => 1,
                    'student_id2' => 2,
                    'course_id' => 2,
                    'course_id2' => 3,
                    'grade' => 1,
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
                ]
            ],
        ]
    ],
];
