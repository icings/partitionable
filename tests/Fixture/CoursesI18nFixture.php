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
    public string $table = 'courses_i18n';

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
