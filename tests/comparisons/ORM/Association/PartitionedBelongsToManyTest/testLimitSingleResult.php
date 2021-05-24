<?php
return [
    [
        'id' => 1,
        'id2' => 2,
        'top_graduated_course' => [
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
        ]
    ],
    [
        'id' => 2,
        'id2' => 3,
        'top_graduated_course' => [
            'id' => 5,
            'id2' => 6,
            'university_id' => 1,
            'name' => 'Course E',
            'online' => true,
            '_joinData' => [
                'id' => 10,
                'student_id' => 2,
                'student_id2' => 3,
                'course_id' => 5,
                'course_id2' => 6,
                'grade' => 1,
            ]
        ]
    ]
];
