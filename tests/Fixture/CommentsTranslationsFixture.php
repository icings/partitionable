<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CommentsTranslationsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'locale' => ['type' => 'string'],
        'body' => ['type' => 'text'],
        '_indexes' => [
            'comments_translations_locale' => ['type' => 'index', 'columns' => ['locale']],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'locale']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'locale' => 'de_DE',
                'body' => 'Franz jagt im komplett verwahrlosten Taxi quer durch Bayern',
            ],
            [
                'id' => 2,
                'locale' => 'de_DE',
                'body' => 'Der schnelle braune Fuchs springt über den faulen Hund',
            ],
            [
                'id' => 5,
                'locale' => 'de_DE',
                'body' => 'Fix Schwyz! quäkt Jürgen blöd vom Paß',
            ],
            [
                'id' => 6,
                'locale' => 'de_DE',
                'body' => 'Quod erat demonstrandum.',
            ],
        ];
        parent::init();
    }
}
