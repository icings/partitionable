<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UniversitiesFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                #'id' => 1,
                'name' => 'University A',
            ],
        ];
        parent::init();
    }
}
