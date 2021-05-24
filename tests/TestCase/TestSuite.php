<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestCase;

use Cake\Datasource\ConnectionManager;
use PHPUnit\Framework\TestResult;

class TestSuite extends \Cake\TestSuite\TestSuite
{
    /**
     * @return static
     */
    public static function suite()
    {
        $suite = new static('Tests');
        $suite->addTestDirectoryRecursive(__DIR__ . DS . 'ORM');

        return $suite;
    }

    /**
     * @inheritDoc
     */
    public function count(bool $preferCache = false): int
    {
        return parent::count($preferCache) * 2;
    }

    /**
     * @inheritDoc
     */
    public function run(?TestResult $result = null): TestResult
    {
        $permutations = [
            'Identifier Quoting' => function () {
                ConnectionManager::get('test')->getDriver()->enableAutoQuoting(true);
            },
            'No identifier quoting' => function () {
                ConnectionManager::get('test')->getDriver()->enableAutoQuoting(false);
            },
        ];

        foreach ($permutations as $permutation) {
            $permutation();
            $result = parent::run($result);
        }

        return $result;
    }
}
