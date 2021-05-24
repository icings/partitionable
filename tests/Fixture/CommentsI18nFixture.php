<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CommentsI18nFixture extends TestFixture
{
    public $table = 'comments_i18n';

    public $fields = [
        'id' => ['type' => 'integer'],
        'locale' => ['type' => 'string'],
        'model' => ['type' => 'string'],
        'foreign_key' => ['type' => 'integer'],
        'field' => ['type' => 'string'],
        'content' => ['type' => 'text'],
        '_indexes' => [
            'comments_I18N_FIELD' => ['type' => 'index', 'columns' => ['model', 'foreign_key', 'field']],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'comments_I18N_LOCALE_FIELD' => ['type' => 'unique', 'columns' => ['locale', 'model', 'foreign_key', 'field']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                #'id' => 1,
                'locale' => 'de_DE',
                'model' => 'Comments',
                'foreign_key' => 1,
                'field' => 'body',
                'content' => 'Franz jagt im komplett verwahrlosten Taxi quer durch Bayern',
            ],
            [
                #'id' => 2,
                'locale' => 'de_DE',
                'model' => 'Comments',
                'foreign_key' => 2,
                'field' => 'body',
                'content' => 'Der schnelle braune Fuchs springt über den faulen Hund',
            ],
            [
                #'id' => 3,
                'locale' => 'de_DE',
                'model' => 'Comments',
                'foreign_key' => 5,
                'field' => 'body',
                'content' => 'Fix Schwyz! quäkt Jürgen blöd vom Paß',
            ],
            [
                #'id' => 4,
                'locale' => 'de_DE',
                'model' => 'Comments',
                'foreign_key' => 6,
                'field' => 'body',
                'content' => 'Quod erat demonstrandum.',
            ],
        ];
        parent::init();
    }
}
