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
    public string $table = 'comments_i18n';

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
