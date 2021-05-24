<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestCase\ORM\Association;

use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Sqlserver;
use Cake\Database\Expression\CommonTableExpression;
use Cake\Database\Expression\OrderClauseExpression;
use Cake\I18n\I18n;
use Cake\ORM\Association;
use Cake\ORM\Behavior\Translate\EavStrategy;
use Cake\ORM\Behavior\Translate\ShadowTableStrategy;
use Cake\ORM\Query;
use Icings\Partitionable\ORM\Association\Loader\PartitionableSelectLoader;
use Icings\Partitionable\ORM\Association\PartitionableHasMany;
use Icings\Partitionable\Test\Fixture\ArticlesFixture;
use Icings\Partitionable\Test\Fixture\AuthorsFixture;
use Icings\Partitionable\Test\Fixture\CommentsFixture;
use Icings\Partitionable\Test\Fixture\CommentsI18nFixture;
use Icings\Partitionable\Test\Fixture\CommentsTranslationsFixture;
use Icings\Partitionable\Test\Fixture\RepliesFixture;
use Icings\Partitionable\Test\TestApp\Model\Table\ArticlesTable;
use Icings\Partitionable\Test\TestApp\Model\Table\CommentsI18nTable;
use Icings\Partitionable\Test\TestCase;
use InvalidArgumentException;
use RuntimeException;

class PartitionedHasManyTest extends TestCase
{
    public $fixtures = [
        ArticlesFixture::class,
        AuthorsFixture::class,
        CommentsFixture::class,
        RepliesFixture::class,
        CommentsI18nFixture::class,
        CommentsTranslationsFixture::class,
    ];

    /**
     * @var ArticlesTable
     */
    protected $_articlesTable;

    public function setUp(): void
    {
        parent::setUp();

        $articlesTable = $this->getTableLocator()->get('Articles');
        assert($articlesTable instanceof ArticlesTable);
        $this->_articlesTable = $articlesTable;

        $articlesTable
            ->partitionableHasMany('TopComments')
            ->setClassName('Comments')
            ->setForeignKey([
                'article_id',
                'article_id2',
            ])
            ->setSort([
                'TopComments.votes' => 'DESC',
                'TopComments.id' => 'ASC',
                'TopComments.id2' => 'ASC',
            ])
            ->setLimit(3);
    }

    public function testOptions(): void
    {
        $this->_articlesTable->associations()->removeAll();
        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->partitionableBelongsToMany('TopComments');

        $this->assertNull($association->getLimit());
        $this->assertSame(PartitionableHasMany::FILTER_IN_SUBQUERY_TABLE, $association->getFilterStrategy());
        $this->assertFalse($association->isSingleResultEnabled());

        $this->_articlesTable->associations()->removeAll();
        $association = $this->_articlesTable->partitionableBelongsToMany('TopComments', [
            'limit' => 10,
            'filterStrategy' => PartitionableHasMany::FILTER_INNER_JOIN_SUBQUERY,
            'singleResult' => false,
        ]);

        $this->assertSame(10, $association->getLimit());
        $this->assertSame(PartitionableHasMany::FILTER_INNER_JOIN_SUBQUERY, $association->getFilterStrategy());
        $this->assertFalse($association->isSingleResultEnabled());

        $this->_articlesTable->associations()->removeAll();
        $association = $this->_articlesTable->partitionableBelongsToMany('TopComments', [
            'limit' => 10,
            'singleResult' => true,
        ]);

        $this->assertSame(1, $association->getLimit());
        $this->assertTrue($association->isSingleResultEnabled());

        $this->_articlesTable->associations()->removeAll();
        $association = $this->_articlesTable->partitionableBelongsToMany('TopComments', [
            'limit' => 1,
            'singleResult' => false,
        ]);

        $this->assertSame(1, $association->getLimit());
        $this->assertFalse($association->isSingleResultEnabled());
    }

    public function testInvalidLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `$limit` argument must be greater than or equal `1`, `0` given.');

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setLimit(0);
    }

    public function testLimitTogglesSingleResult(): void
    {
        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');

        $this->assertFalse($association->isSingleResultEnabled());

        $association
            ->setLimit(1);
        $this->assertTrue($association->isSingleResultEnabled());

        $association
            ->setLimit(2);
        $this->assertFalse($association->isSingleResultEnabled());
    }

    public function testEnableSingleResultSetsLimit(): void
    {
        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');

        $this->assertSame(3, $association->getLimit());

        $association
            ->enableSingleResult();
        $this->assertSame(1, $association->getLimit());
    }

    public function testEnableSingleResultAffectsTypeAndPropertyName(): void
    {
        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');

        $this->assertSame(Association::ONE_TO_MANY, $association->type());
        $this->assertSame('top_comments', (clone $association)->getProperty());

        $association
            ->enableSingleResult();

        $this->assertSame(Association::ONE_TO_ONE, $association->type());
        $this->assertSame('top_comment', $association->getProperty());
    }

    public function testInvalidStrategy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The `$strategy` argument must be one of `inSubqueryCTE`, `inSubqueryJoin`, `inSubqueryTable`, ' .
            '`innerJoinCTE`, `innerJoinSubquery`, `invalid` given.'
        );

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setFilterStrategy('invalid');
    }

    public function testNoLimit(): void
    {
        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setLimit(null);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimit(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimitSingleResult(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(1);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimitWithSingleKeys(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        $this->_articlesTable->setPrimaryKey('id');

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setForeignKey([
                'article_id',
            ])
            ->setSort([
                'TopComments.votes' => 'DESC',
                'TopComments.id' => 'ASC',
            ]);

        $association
            ->setPrimaryKey('id');

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    public function testLimitParent(): void
    {
        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration()
            ->limit(1);

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    public function testMissingSortOrder(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Partitioning requires a sort order.');

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setSort(null);

        $this->_articlesTable
            ->find()
            ->contain('TopComments')
            ->all();
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testSortOrderWithExpressions(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setSort(function ($exp, Query $query) {
                return [
                    new OrderClauseExpression(
                        $query->identifier('TopComments.votes'),
                        'DESC'
                    ),
                    'TopComments.id' => 'ASC',
                    'TopComments.id2' => 'ASC',
                ];
            });

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testOverrideLimitAndSortInContainQueryBuilder(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $ids = $query
            ->extract(function ($row) {
                return array_column($row['top_comments'], 'id');
            })
            ->toArray();
        $this->assertSame([[4, 3, 2], [5, 6, 7]], $ids);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments', function (Query $query) {
                return $query
                    ->limit(2)
                    ->order([
                        'TopComments.votes' => 'ASC',
                    ]);
            })
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testOverrideLimitAndSortInBeforeFindQueryBuilder(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $ids = $query
            ->extract(function ($row) {
                return array_column($row['top_comments'], 'id');
            })
            ->toArray();
        $this->assertSame([[4, 3, 2], [5, 6, 7]], $ids);

        $association
            ->getEventManager()
            ->on('Model.beforeFind', function ($event, Query $query) {
                return $query
                    ->limit(2)
                    ->order([
                        'TopComments.votes' => 'ASC',
                    ]);
            });

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();
        $clonedQuery = clone $query;

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
        $this->assertResultsEqualFile(__FUNCTION__, $clonedQuery->toArray());
        $this->assertResultsEqualFile(__FUNCTION__, $clonedQuery->clearResult()->toArray());
    }

    public function testRemovingTheObjectHashOption(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The hash value found on the query object has not been mapped, ' .
            'make sure that you did not empty out or override the query options.'
        );

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');

        $query = $this->_articlesTable
            ->find()
            ->contain('TopComments')
            ->disableHydration();

        $association
            ->getEventManager()
            ->on('Model.beforeFind', function ($event, Query $query) {
                return $query->applyOptions([
                    PartitionableSelectLoader::class . '_object_hash' => null,
                ]);
            });

        $query->toArray();
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimitAndOffsetInBeforeFindDoAffectQueriesForTheSameRepository(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $this->_articlesTable
            ->getAssociation('TopComments')
            ->getEventManager()
            ->on('Model.beforeFind', function ($event, Query $query) {
                return $query
                    ->limit(2)
                    ->offset(3);
            });

        $query = $this->_articlesTable
            ->find()
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__ . '.Partitioned', $query->toArray());

        $query = $this->_articlesTable
            ->getAssociation('TopComments')
            ->find()
            ->orderAsc('TopComments.id')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__ . '.SameRepo', $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testContainNestedAssociations(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(2);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments', function (Query $query) {
                return $query
                    ->contain('Articles.Authors.Comments')
                    ->contain('Replies.Comments');
            })
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testComplexContainQuery(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        if ($this->_articlesTable->getConnection()->getDriver() instanceof Mysql) {
            $stmt = $this->_articlesTable
                ->getConnection()
                ->query("SET @@session.sql_mode = CONCAT('ONLY_FULL_GROUP_BY,', @@sql_mode);");
            $this->assertTrue($stmt->execute());
            $stmt->closeCursor();
        }

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setFinder('published')
            ->setConditions([
                'TopComments.votes !=' => 3,
            ]);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments', function (Query $query) {
                $typeMap = $query->getSelectTypeMap();
                $typeMap->addDefaults([
                    'aliased' => 'integer',
                    'counter' => 'integer',
                    'JoinAlias__foo' => 'integer',
                    'cte__cte_field' => 'integer',
                    'cte_field_aliased' => 'integer',
                ]);

                $query
                    ->select([
                        'id', 'id2', 'TopComments.article_id', 'TopComments.article_id2', 'TopComments.votes',
                        'aliased' => 42,
                        'field_aliased' => 'TopComments.body',
                        'JoinAlias.foo',
                        'Articles.id',
                        'foo_bar_baz' => $query->func()->concat(['FOO', '.', 'BAR' , '.', 'BAZ']),
                        'cte.cte_field',
                        'cte_field_aliased' => 'cte.cte_field',
                        'replies_count' => $query->func()->count(
                            $query->identifier('Replies.id')
                        ),
                    ])
                    ->enableAutoFields()
                    ->leftJoin([
                        'JoinAlias' => $query->getConnection()->newQuery()->select(['foo' => 1]),
                    ])
                    ->leftJoinWith('Replies')
                    ->contain('Articles.Authors.Comments')
                    ->contain('Replies.Comments')
                    ->where([
                        'TopComments.votes <' => 10000,
                    ])
                    ->group([
                        'TopComments.id',
                        'TopComments.id2',
                        'TopComments.author_id',
                        'TopComments.article_id',
                        'TopComments.article_id2',
                        'TopComments.votes',
                        'TopComments.body',
                        'TopComments.published',
                        'cte.cte_field',
                        'JoinAlias.foo',
                        'Articles.id',
                        'Articles.id2',
                        'Articles.author_id',
                        'Articles.title',
                        'Articles.body',
                        'Authors.id',
                        'Authors.name',
                    ])
                    ->limit(2)
                    ->setSelectTypeMap($typeMap);

                if ($query->getConnection()->getDriver() instanceof Sqlserver) {
                    $query
                        ->innerJoin([
                            'cte' => $query
                                ->getConnection()
                                ->newQuery()
                                ->select(['cte_field' => 1]),
                        ]);
                } else {
                    $query
                        ->with(function (CommonTableExpression $cte, \Cake\Database\Query $query) {
                            $cteQuery = $query
                                ->select(['cte_field' => 1]);

                            return $cte
                                ->name('cte')
                                ->query($cteQuery);
                        })
                        ->innerJoin('cte');
                }

                return $query;
            })
            ->orderAsc('id')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testReferenceFieldsFromTheSelectList(string $loaderStrategy, string $filterStrategy): void
    {
        if (!($this->_articlesTable->getConnection()->getDriver() instanceof Mysql)) {
            $this->markTestSkipped('Referencing fields from the select list only works with MySQL/MariaDB');
        }

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments', function (Query $query) {
                return $query
                    ->select(['alias' => 123])
                    ->select($query->getRepository())
                    ->group([
                        'TopComments.id',
                        'TopComments.id2',
                    ])
                    ->having(['alias' => 123], ['alias' => 'integer']);
            })
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testTranslationsEav(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        I18n::setLocale('de_DE');

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(2);

        $association
            ->setPrimaryKey('id');

        $association
            ->addBehavior('Translate', [
                'strategyClass' => EavStrategy::class,
                'translationTable' => CommentsI18nTable::class,
                'fields' => ['body'],
            ]);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testTranslationsEavOnlyTranslated(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        I18n::setLocale('de_DE');

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $association
            ->setPrimaryKey('id');

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();
        $queryClone = clone $query;

        $counts = $query
            ->extract(function ($row) {
                return count($row['top_comments']);
            })
            ->toArray();
        $this->assertSame([3, 3], $counts);

        $association
            ->setPrimaryKey('id')
            ->addBehavior('Translate', [
                'strategyClass' => EavStrategy::class,
                'translationTable' => CommentsI18nTable::class,
                'fields' => ['body'],
                'onlyTranslated' => true,
            ]);

        $this->assertResultsEqualFile(__FUNCTION__, $queryClone->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testTranslationsShadowTable(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        I18n::setLocale('de_DE');

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(2);

        $association
            ->setPrimaryKey('id');

        $association
            ->addBehavior('Translate', [
                'strategyClass' => ShadowTableStrategy::class,
                'fields' => ['body'],
            ]);

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testTranslationsShadowTableOnlyTranslated(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        I18n::setLocale('de_DE');

        /** @var PartitionableHasMany $association */
        $association = $this->_articlesTable->getAssociation('TopComments');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $association
            ->setPrimaryKey('id');

        $query = $this->_articlesTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopComments')
            ->disableHydration();
        $queryClone = clone $query;

        $counts = $query
            ->extract(function ($row) {
                return count($row['top_comments']);
            })
            ->toArray();
        $this->assertSame([3, 3], $counts);

        $association
            ->addBehavior('Translate', [
                'strategyClass' => ShadowTableStrategy::class,
                'fields' => ['body'],
                'onlyTranslated' => true,
            ]);

        $this->assertResultsEqualFile(__FUNCTION__, $queryClone->toArray());
    }
}
