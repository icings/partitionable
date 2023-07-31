<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test;

use Cake\Core\Configure;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\I18n;
use Cake\ORM\Association;
use Cake\TestSuite\TestCase as CakeTestCase;
use Icings\Partitionable\ORM\Association\PartitionableAssociationInterface;

class TestCase extends CakeTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        I18n::setLocale(Configure::read('App.defaultLocale'));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        I18n::setLocale(Configure::read('App.defaultLocale'));
    }

    public function assertResultsEqualFile(string $expectedFilePath, array $results, string $message = ''): void
    {
        $basePath = substr(static::class, strlen('Icings\Partitionable\Test\TestCase\\'));
        $basePath = str_replace('\\', DS, $basePath);

        $expectedFile = TESTS . 'comparisons' . DS . $basePath . DS . $expectedFilePath . '.php';
        $this->assertFileExists($expectedFile);

        $expected = require $expectedFile;

        $this->assertSame($expected, $results, $message);
    }

    public function skipInSubqueryCTEStrategyIfSqlServer(string $strategy): void
    {
        $this->skipIf(
            $strategy === PartitionableAssociationInterface::FILTER_IN_SUBQUERY_CTE &&
            ConnectionManager::get('default')->getDriver() instanceof Sqlserver,
            'SQL Server does not support CTEs in subqueries'
        );
    }

    public static function filterStrategyDataProvider(): array
    {
        $filterStrategies = [
            PartitionableAssociationInterface::FILTER_IN_SUBQUERY_CTE,
            PartitionableAssociationInterface::FILTER_IN_SUBQUERY_JOIN,
            PartitionableAssociationInterface::FILTER_IN_SUBQUERY_TABLE,
            PartitionableAssociationInterface::FILTER_INNER_JOIN_CTE,
            PartitionableAssociationInterface::FILTER_INNER_JOIN_SUBQUERY,
        ];

        $loaderStrategies = [
            Association::STRATEGY_SELECT,
            Association::STRATEGY_SUBQUERY,
        ];

        $strategies = [];
        foreach ($loaderStrategies as $loaderStrategy) {
            foreach ($filterStrategies as $filterStrategy) {
                $strategies[] = [$loaderStrategy, $filterStrategy];
            }
        }

        return $strategies;
    }
}
