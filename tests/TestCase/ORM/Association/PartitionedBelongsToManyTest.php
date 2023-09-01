<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestCase\ORM\Association;

use Arrayobject;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Sqlserver;
use Cake\Database\Expression\CommonTableExpression;
use Cake\Database\Expression\OrderClauseExpression;
use Cake\I18n\I18n;
use Cake\ORM\Association;
use Cake\ORM\Behavior\Translate\EavStrategy;
use Cake\ORM\Behavior\Translate\ShadowTableStrategy;
use Cake\ORM\Query;
use Icings\Partitionable\ORM\Association\Loader\PartitionableSelectWithPivotLoader;
use Icings\Partitionable\ORM\Association\PartitionableBelongsToMany;
use Icings\Partitionable\Test\Fixture\CourseMembershipsFixture;
use Icings\Partitionable\Test\Fixture\CoursesFixture;
use Icings\Partitionable\Test\Fixture\CoursesI18nFixture;
use Icings\Partitionable\Test\Fixture\CoursesTranslationsFixture;
use Icings\Partitionable\Test\Fixture\StudentsFixture;
use Icings\Partitionable\Test\Fixture\UniversitiesFixture;
use Icings\Partitionable\Test\TestApp\Model\Table\CoursesI18nTable;
use Icings\Partitionable\Test\TestApp\Model\Table\StudentsTable;
use Icings\Partitionable\Test\TestCase;
use InvalidArgumentException;
use RuntimeException;

class PartitionedBelongsToManyTest extends TestCase
{
    public $fixtures = [
        CourseMembershipsFixture::class,
        CoursesFixture::class,
        CoursesI18nFixture::class,
        CoursesTranslationsFixture::class,
        StudentsFixture::class,
        UniversitiesFixture::class,
    ];

    /**
     * @var StudentsTable
     */
    protected $_studentsTable;

    public function setUp(): void
    {
        parent::setUp();

        $studentsTable = $this->getTableLocator()->get('Students');
        assert($studentsTable instanceof StudentsTable);
        $this->_studentsTable = $studentsTable;

        $studentsTable
            ->partitionableBelongsToMany('TopGraduatedCourses')
            ->setClassName('Courses')
            ->setThrough('CourseMemberships')
            ->setForeignKey([
                'student_id',
                'student_id2',
            ])
            ->setTargetForeignKey([
                'course_id',
                'course_id2',
            ])
            ->setSort([
                'CourseMemberships.grade' => 'ASC',
            ])
            ->setLimit(2)
            ->setConditions([
                'CourseMemberships.grade IS NOT' => null,
            ]);
    }

    public function testOptions(): void
    {
        $this->_studentsTable->associations()->removeAll();
        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->partitionableBelongsToMany('TopGraduatedCourses');

        $this->assertNull($association->getLimit());
        $this->assertSame(PartitionableBelongsToMany::FILTER_IN_SUBQUERY_TABLE, $association->getFilterStrategy());
        $this->assertFalse($association->isSingleResultEnabled());

        $this->_studentsTable->associations()->removeAll();
        $association = $this->_studentsTable->partitionableBelongsToMany('TopGraduatedCourses', [
            'limit' => 10,
            'filterStrategy' => PartitionableBelongsToMany::FILTER_INNER_JOIN_SUBQUERY,
        ]);

        $this->assertSame(10, $association->getLimit());
        $this->assertSame(PartitionableBelongsToMany::FILTER_INNER_JOIN_SUBQUERY, $association->getFilterStrategy());
        $this->assertFalse($association->isSingleResultEnabled());

        $this->_studentsTable->associations()->removeAll();
        $association = $this->_studentsTable->partitionableBelongsToMany('TopGraduatedCourses', [
            'limit' => 10,
            'singleResult' => true,
        ]);

        $this->assertSame(1, $association->getLimit());
        $this->assertTrue($association->isSingleResultEnabled());

        $this->_studentsTable->associations()->removeAll();
        $association = $this->_studentsTable->partitionableBelongsToMany('TopGraduatedCourses', [
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setLimit(0);
    }

    public function testLimitTogglesSingleResult(): void
    {
        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');

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
        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');

        $this->assertSame(2, $association->getLimit());

        $association
            ->enableSingleResult();
        $this->assertSame(1, $association->getLimit());
    }

    public function testEnableSingleResultAffectsTypeAndPropertyName(): void
    {
        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');

        $this->assertSame(Association::MANY_TO_MANY, $association->type());
        $this->assertSame('top_graduated_courses', (clone $association)->getProperty());

        $association
            ->enableSingleResult();

        $this->assertSame(Association::ONE_TO_ONE, $association->type());
        $this->assertSame('top_graduated_course', $association->getProperty());
    }

    public function testInvalidStrategy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The `$strategy` argument must be one of `inSubqueryCTE`, `inSubqueryJoin`, `inSubqueryTable`, ' .
            '`innerJoinCTE`, `innerJoinSubquery`, `invalid` given.'
        );

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setFilterStrategy('invalid');
    }

    public function testNoLimit(): void
    {
        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setLimit(null);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimitSortOnJoinTable(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setSort([
                'CourseMemberships.grade' => 'ASC',
            ]);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimitSortOnTargetTable(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        $this->_studentsTable
            ->partitionableBelongsToMany('LatestCourses')
            ->setClassName('Courses')
            ->setThrough('CourseMemberships')
            ->setForeignKey([
                'student_id',
                'student_id2',
            ])
            ->setTargetForeignKey([
                'course_id',
                'course_id2',
            ])
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setSort([
                'LatestCourses.id' => 'DESC',
            ])
            ->setLimit(2);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('LatestCourses')
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(1);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
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

        $this->_studentsTable->setPrimaryKey('id');

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setForeignKey([
                'student_id',
            ])
            ->setTargetForeignKey([
                'course_id',
            ]);

        $association
            ->setPrimaryKey('id');
        $association
            ->junction()
            ->setPrimaryKey(['id']);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    public function testLimitParent(): void
    {
        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration()
            ->limit(1);

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * Tests that tuple comparison transformation does not create a
     * naming conflict when using standard association aliases that
     * do match the table name.
     *
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimitNoConflictWithStandardAlias(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        $this->_studentsTable
            ->partitionableBelongsToMany('Courses')
            ->setThrough('CourseMemberships')
            ->setForeignKey([
                'student_id',
                'student_id2',
            ])
            ->setTargetForeignKey([
                'course_id',
                'course_id2',
            ])
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setSort([
                'Courses.id' => 'DESC',
            ])
            ->setLimit(2);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('Courses')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testLimitMySqlMariaDbOnlyFullGroupBy(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipIf(
            !($this->_studentsTable->getConnection()->getDriver() instanceof Mysql),
            'ONLY_FULL_GROUP_BY tests are for MySQL/MariaDB only.'
        );

        $this->_studentsTable->getConnection()
            ->query(
                'SET SESSION sql_mode=CONCAT(@@SESSION.sql_mode, ",ONLY_FULL_GROUP_BY")'
            )
            ->execute();

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setSort([
                'CourseMemberships.grade' => 'ASC',
            ]);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->toArray());
    }

    public function testMissingSortOrder(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Partitioning requires a sort order.');

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setSort([]);

        $this->_studentsTable
            ->find()
            ->contain('TopGraduatedCourses')
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setSort(function ($exp, Query $query) {
                return [
                    new OrderClauseExpression(
                        $query->identifier('CourseMemberships.grade'),
                        'ASC'
                    ),
                ];
            });

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(3);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();

        $ids = $query
            ->all()
            ->extract(function ($row) {
                return array_column($row['top_graduated_courses'], 'id');
            })
            ->toArray();
        $this->assertSame([[2, 3, 4], [5, 4, 3]], $ids);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses', function (Query $query) {
                return $query
                    ->limit(2)
                    ->order([
                        'CourseMemberships.grade' => 'DESC',
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(3);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();
        $queryClone = clone $query;

        $ids = $query
            ->all()
            ->extract(function ($row) {
                return array_column($row['top_graduated_courses'], 'id');
            })
            ->toArray();
        $this->assertSame([[2, 3, 4], [5, 4, 3]], $ids);

        $association
            ->getEventManager()
            ->on('Model.beforeFind', function ($event, Query $query, Arrayobject $options) {
                if (($options['partitionableQueryType'] ?? null) === 'fetcher') {
                    $query
                        ->limit(2)
                        ->order([
                            'CourseMemberships.grade' => 'DESC',
                        ]);
                }

                return $query;
            });

        $this->assertResultsEqualFile(__FUNCTION__, $queryClone->toArray());
    }

    public function testRemovingTheObjectHashOption(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The tracking ID value found on the query object has not been mapped, ' .
            'make sure that you did not empty out or override the query options.'
        );

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');

        $query = $this->_studentsTable
            ->find()
            ->contain('TopGraduatedCourses')
            ->disableHydration();

        $association
            ->getEventManager()
            ->on('Model.beforeFind', function ($event, Query $query) {
                return $query->applyOptions([
                    PartitionableSelectWithPivotLoader::class . '_trackingId' => null,
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $this->_studentsTable
            ->getAssociation('TopGraduatedCourses')
            ->getEventManager()
            ->on('Model.beforeFind', function ($event, Query $query) {
                return $query
                    ->limit(2)
                    ->offset(3);
            });

        $query = $this->_studentsTable
            ->find()
            ->contain('TopGraduatedCourses')
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__ . '.Partitioned', $query->toArray());

        $query = $this->_studentsTable
            ->getAssociation('TopGraduatedCourses')
            ->find()
            ->orderAsc('TopGraduatedCourses.id')
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses', function (Query $query) {
                return $query
                    ->contain('Students.Universities.Courses')
                    ->contain('Universities.Courses');
            })
            ->disableHydration();

        $this->assertResultsEqualFile(__FUNCTION__, $query->firstOrFail());
    }

    /**
     * @dataProvider filterStrategyDataProvider
     * @param string $loaderStrategy The loader strategy.
     * @param string $filterStrategy The filter strategy.
     */
    public function testComplexContainQuery(string $loaderStrategy, string $filterStrategy): void
    {
        $this->skipInSubqueryCTEStrategyIfSqlServer($filterStrategy);

        if ($this->_studentsTable->getConnection()->getDriver() instanceof Mysql) {
            $stmt = $this->_studentsTable
                ->getConnection()
                ->query("SET @@session.sql_mode = CONCAT('ONLY_FULL_GROUP_BY,', @@sql_mode);");
            $this->assertTrue($stmt->execute());
            $stmt->closeCursor();
        }

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setFinder('online')
            ->setConditions([
                'CourseMemberships.grade !=' => 1,
            ]);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses', function (Query $query) {
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
                        'id', 'id2', 'TopGraduatedCourses.university_id', 'TopGraduatedCourses.name',
                        'aliased' => 42,
                        'field_aliased' => 'TopGraduatedCourses.name',
                        'JoinAlias.foo',
                        'Universities.id',
                        'foo_bar_baz' => $query->func()->concat(['FOO', '.', 'BAR' , '.', 'BAZ']),
                        'cte.cte_field',
                        'cte_field_aliased' => 'cte.cte_field',
                        'students_count' => $query->func()->count(
                            $query->identifier('Students.id')
                        ),
                    ])
                    ->enableAutoFields()
                    ->leftJoin([
                        'JoinAlias' => $query->getConnection()->newQuery()->select(['foo' => 1]),
                    ])
                    ->leftJoinWith('Students')
                    ->contain('Students.Universities.Courses')
                    ->contain('Universities.Courses')
                    ->where([
                        'TopGraduatedCourses.id <' => 10000,
                    ])
                    ->group([
                        'CourseMemberships.id',
                        'CourseMemberships.student_id',
                        'CourseMemberships.student_id2',
                        'CourseMemberships.course_id',
                        'CourseMemberships.course_id2',
                        'CourseMemberships.grade',
                        'TopGraduatedCourses.id',
                        'TopGraduatedCourses.id2',
                        'TopGraduatedCourses.university_id',
                        'TopGraduatedCourses.name',
                        'TopGraduatedCourses.online',
                        'cte.cte_field',
                        'JoinAlias.foo',
                        'Universities.id',
                        'Universities.name',
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
        if (!($this->_studentsTable->getConnection()->getDriver() instanceof Mysql)) {
            $this->markTestSkipped('Referencing fields from the select list only works with MySQL/MariaDB');
        }

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses', function (Query $query) {
                $query
                    ->select(['alias' => 123])
                    ->enableAutoFields()
                    ->group([
                        'TopGraduatedCourses.id',
                        'TopGraduatedCourses.id2',
                        'TopGraduatedCourses.university_id',
                        'TopGraduatedCourses.name',
                        'TopGraduatedCourses.online',
                        'CourseMemberships.id',
                        'CourseMemberships.student_id',
                        'CourseMemberships.student_id2',
                        'CourseMemberships.course_id',
                        'CourseMemberships.course_id2',
                        'CourseMemberships.grade',
                    ])
                    ->having(['alias' => 123], ['alias' => 'integer']);

                $typeMap = $query->getSelectTypeMap();
                $typeMap->addDefaults(['alias' => 'integer']);

                return $query;
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setTargetForeignKey([
                'course_id',
            ]);

        $association
            ->setPrimaryKey('id');

        $association
            ->addBehavior('Translate', [
                'strategyClass' => EavStrategy::class,
                'translationTable' => CoursesI18nTable::class,
                'fields' => ['name'],
            ]);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(3)
            ->setTargetForeignKey([
                'course_id',
            ]);

        $association
            ->setPrimaryKey('id');

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();
        $queryClone = clone $query;

        $counts = $query
            ->all()
            ->extract(function ($row) {
                return count($row['top_graduated_courses']);
            })
            ->toArray();
        $this->assertSame([3, 3], $counts);

        $association
            ->addBehavior('Translate', [
                'strategyClass' => EavStrategy::class,
                'translationTable' => CoursesI18nTable::class,
                'fields' => ['name'],
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setTargetForeignKey([
                'course_id',
            ]);

        $association
            ->setPrimaryKey('id');

        $association
            ->addBehavior('Translate', [
                'strategyClass' => ShadowTableStrategy::class,
                'fields' => ['name'],
            ]);

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
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

        /** @var PartitionableBelongsToMany $association */
        $association = $this->_studentsTable->getAssociation('TopGraduatedCourses');
        $association
            ->setStrategy($loaderStrategy)
            ->setFilterStrategy($filterStrategy)
            ->setLimit(3)
            ->setTargetForeignKey([
                'course_id',
            ]);

        $association
            ->setPrimaryKey('id');

        $query = $this->_studentsTable
            ->find()
            ->select(['id', 'id2'])
            ->contain('TopGraduatedCourses')
            ->disableHydration();
        $queryClone = clone $query;

        $counts = $query
            ->all()
            ->extract(function ($row) {
                return count($row['top_graduated_courses']);
            })
            ->toArray();
        $this->assertSame([3, 3], $counts);

        $association
            ->addBehavior('Translate', [
                'strategyClass' => ShadowTableStrategy::class,
                'fields' => ['name'],
                'onlyTranslated' => true,
            ]);

        $this->assertResultsEqualFile(__FUNCTION__, $queryClone->toArray());
    }
}
