<?php
declare(strict_types=1);

return [
    [
        'table' => 'articles',
        'columns' => [
            'id' => ['type' => 'integer'],
            'id2' => ['type' => 'integer'],
            'author_id' => ['type' => 'integer'],
            'title' => ['type' => 'string'],
            'body' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'id2']],
        ],
    ],
    [
        'table' => 'articles_tags',
        'columns' => [
            'id' => ['type' => 'integer'],
            'article_id' => ['type' => 'integer'],
            'article_id2' => ['type' => 'integer'],
            'tag_id' => ['type' => 'integer'],
            'tag_id2' => ['type' => 'integer'],
            'weight' => ['type' => 'integer'],
            'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        'indexes' => [
            'articles_tags_article_id' => ['type' => 'index', 'columns' => ['article_id']],
            'articles_tags_article_id2' => ['type' => 'index', 'columns' => ['article_id2']],
            'articles_tags_tag_id' => ['type' => 'index', 'columns' => ['article_id']],
            'articles_tags_tag_id2' => ['type' => 'index', 'columns' => ['article_id2']],
        ],
    ],
    [
        'table' => 'authors',
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    [
        'table' => 'comments',
        'columns' => [
            'id' => ['type' => 'integer'],
            'id2' => ['type' => 'integer'],
            'article_id' => ['type' => 'integer'],
            'article_id2' => ['type' => 'integer'],
            'author_id' => ['type' => 'integer'],
            'votes' => ['type' => 'integer'],
            'body' => ['type' => 'text'],
            'published' => ['type' => 'boolean'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'id2']],
        ],
        'indexes' => [
            'comments_article_id' => ['type' => 'index', 'columns' => ['article_id', 'article_id2']],
            'comments_top_comments_sort' => ['type' => 'index', 'columns' => ['votes', 'id']],
        ],
    ],
    [
        'table' => 'comments_i18n',
        'columns' => [
            'id' => ['type' => 'integer'],
            'locale' => ['type' => 'string'],
            'model' => ['type' => 'string'],
            'foreign_key' => ['type' => 'integer'],
            'field' => ['type' => 'string'],
            'content' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'comments_I18N_LOCALE_FIELD' => ['type' => 'unique', 'columns' => ['locale', 'model', 'foreign_key', 'field']],
        ],
        'indexes' => [
            'comments_I18N_FIELD' => ['type' => 'index', 'columns' => ['model', 'foreign_key', 'field']],
        ],
    ],
    [
        'table' => 'comments_translations',
        'columns' => [
            'id' => ['type' => 'integer'],
            'locale' => ['type' => 'string'],
            'body' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'locale']],
        ],
        'indexes' => [
            'comments_translations_locale' => ['type' => 'index', 'columns' => ['locale']],
        ],
    ],
    [
        'table' => 'course_memberships',
        'columns' => [
            'id' => ['type' => 'integer'],
            'student_id' => ['type' => 'integer'],
            'student_id2' => ['type' => 'integer'],
            'course_id' => ['type' => 'integer'],
            'course_id2' => ['type' => 'integer'],
            'grade' => ['type' => 'integer', 'null' => true, 'default' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['student_id', 'student_id2', 'course_id', 'course_id2']],
        ],
    ],
    [
        'table' => 'courses',
        'columns' => [
            'id' => ['type' => 'integer'],
            'id2' => ['type' => 'integer'],
            'university_id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
            'online' => ['type' => 'boolean'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'id2']],
        ],
        'indexes' => [
            'courses_university_id' => ['type' => 'index', 'columns' => ['university_id']],
        ],
    ],
    [
        'table' => 'courses_i18n',
        'columns' => [
            'id' => ['type' => 'integer'],
            'locale' => ['type' => 'string'],
            'model' => ['type' => 'string'],
            'foreign_key' => ['type' => 'integer'],
            'field' => ['type' => 'string'],
            'content' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'courses_I18N_LOCALE_FIELD' => ['type' => 'unique', 'columns' => ['locale', 'model', 'foreign_key', 'field']],
        ],
        'indexes' => [
            'courses_I18N_FIELD' => ['type' => 'index', 'columns' => ['model', 'foreign_key', 'field']],
        ],
    ],
    [
        'table' => 'courses_translations',
        'columns' => [
            'id' => ['type' => 'integer'],
            'locale' => ['type' => 'string'],
            'name' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'locale']],
        ],
        'indexes' => [
            'courses_translations_locale' => ['type' => 'index', 'columns' => ['locale']],
        ],
    ],
    [
        'table' => 'replies',
        'columns' => [
            'id' => ['type' => 'integer'],
            'author_id' => ['type' => 'integer'],
            'comment_id' => ['type' => 'integer'],
            'comment_id2' => ['type' => 'integer'],
            'body' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        'indexes' => [
            'replies_author_id' => ['type' => 'index', 'columns' => ['author_id']],
            'replies_comment_id' => ['type' => 'index', 'columns' => ['comment_id', 'comment_id2']],
        ],
    ],
    [
        'table' => 'students',
        'columns' => [
            'id' => ['type' => 'integer'],
            'id2' => ['type' => 'integer'],
            'university_id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'id2']],
        ],
        'indexes' => [
            'students_university_id' => ['type' => 'index', 'columns' => ['university_id']],
        ],
    ],
    [
        'table' => 'tags',
        'columns' => [
            'id' => ['type' => 'integer'],
            'id2' => ['type' => 'integer'],
            'author_id' => ['type' => 'integer'],
            'title' => ['type' => 'string'],
            'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'id2']],
        ],
    ],
    [
        'table' => 'universities',
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
