<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CoursesTranslationsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'locale' => ['type' => 'string'],
        'name' => ['type' => 'text'],
        '_indexes' => [
            'courses_translations_locale' => ['type' => 'index', 'columns' => ['locale']],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'locale']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => 2,
                'locale' => 'de_DE',
                'name' => 'Kurs B',
            ],
            [
                'id' => 5,
                'locale' => 'de_DE',
                'name' => 'Kurs E',
            ],
        ];
        parent::init();
    }
}
