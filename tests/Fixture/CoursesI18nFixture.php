<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CoursesI18nFixture extends TestFixture
{
    public $table = 'courses_i18n';

    public $fields = [
        'id' => ['type' => 'integer'],
        'locale' => ['type' => 'string'],
        'model' => ['type' => 'string'],
        'foreign_key' => ['type' => 'integer'],
        'field' => ['type' => 'string'],
        'content' => ['type' => 'text'],
        '_indexes' => [
            'courses_I18N_FIELD' => ['type' => 'index', 'columns' => ['model', 'foreign_key', 'field']],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'courses_I18N_LOCALE_FIELD' => ['type' => 'unique', 'columns' => ['locale', 'model', 'foreign_key', 'field']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                #'id' => 1,
                'locale' => 'de_DE',
                'model' => 'Courses',
                'foreign_key' => 2,
                'field' => 'name',
                'content' => 'Kurs B',
            ],
            [
                #'id' => 4,
                'locale' => 'de_DE',
                'model' => 'Courses',
                'foreign_key' => 5,
                'field' => 'name',
                'content' => 'Kurs E',
            ],
        ];
        parent::init();
    }
}
