# Partitionable

![Stability][ico-stability]
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]
[![Latest Version][ico-version]][link-version]
[![Software License][ico-license]][link-license]

[ico-stability]: https://img.shields.io/badge/stability-experimental-orange.svg?style=flat-square
[ico-build]: https://img.shields.io/github/workflow/status/icings/partitionable/CI/master?style=flat-square
[ico-coverage]: https://img.shields.io/codecov/c/github/icings/partitionable.svg?style=flat-square
[ico-version]: https://img.shields.io/packagist/v/icings/partitionable.svg?style=flat-square&label=latest
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-build]: https://github.com/icings/partitionable/actions/workflows/ci.yml?query=branch%3Amaster
[link-coverage]: https://codecov.io/github/icings/partitionable
[link-version]: https://packagist.org/packages/icings/partitionable
[link-license]: LICENSE.txt

A set of partitionable associations for the CakePHP ORM, allowing for basic limiting per group.


## Requirements

* CakePHP ORM 4.1+
* A DBMS supported by CakePHP with window function support (MySQL 8, MariaDB 10.2, Postgres 9.4, SQL Sever 2017,
  Sqlite 3.25).


## Installation

Use [Composer](https://getcomposer.org) to add the library to your project:

```bash
composer require icings/partitionable
```


## I don't get it, what is this good for exactly?

What exactly are these associations good for, what is a "_limit per group_" you may ask.

Basically the associations provided in this library allow applying limits for `hasMany` and `belongsToMany` type of
associations, so that it's possible to for example receive a maximum of _n_ number of comments per article for an
`Articles hasMany Comments` association.


## Usage

**Make sure to first check the [Known Issues/Limitations](#known-issueslimitations) section!**

Then add the `\Icings\Partitionable\ORM\AssociationsTrait` trait to your table class, use its `partitionableHasMany()`
and `partitionableBelongsToMany()` methods to add `hasMany`, respectively `belongsToMany` associations, configure a
limit and a sort order, and you're done with the minimal setup and you can contain the partitionable associations just
like any other associations.

Note that configuring a sort order is mandatory, as it is not possible to reliably partition the results without an
explicit sort order, omitting it will result in an error!

### Has Many

```php
// ...
use Icings\Partitionable\ORM\AssociationsTrait;

class ArticlesTable extends \Cake\ORM\Table
{
    use AssociationsTrait;

    public function initialize(array $config): void
    {
        // ...

        $this
            ->partitionableHasMany('TopComments')
            ->setClassName('Comments')
            ->setLimit(3)
            ->setSort([
                'TopComments.votes' => 'DESC',
                'TopComments.id' => 'ASC',
            ]);
    }
}
```

```php
$articlesQuery = $this->Articles
    ->find()
    ->contain('TopComments');
```

That would query the 3 highest voted comments for each article, eg the result would look something like:

```php
[
    'title' => 'Some Article',
    'top_comments' => [
        [
            'votes' => 10,
            'body' => 'Some Comment',
        ],
        [
            'votes' => 9,
            'body' => 'Some Other Comment',
        ],
        [
            'votes' => 8,
            'body' => 'And Yet Another Comment',
        ],
    ],
]
```

### Belongs To Many

```php
// ...
use Icings\Partitionable\ORM\AssociationsTrait;

class StudentsTable extends \Cake\ORM\Table
{
    use AssociationsTrait;

    public function initialize(array $config): void
    {
        // ...

        $this
            ->partitionableBelongsToMany('TopGraduatedCourses')
            ->setClassName('Courses')
            ->setThrough('CourseMemberships')
            ->setLimit(3)
            ->setSort([
                'CourseMemberships.grade' => 'ASC',
                'CourseMemberships.id' => 'ASC',
            ])
            ->setConditions([
                'CourseMemberships.grade IS NOT' => null,
            ]);
    }
}
```

```php
$studentsQuery = $this->Students
    ->find()
    ->contain('TopGraduatedCourses');
```

That would query the 3 highest graduated courses for each student, eg the result would look something like:

```php
[
    'name' => 'Some Student',
    'top_graduated_courses' => [
        [
            'name' => 'Some Course',
            '_joinData' => [
                'grade' => 1,
            ],
        ],
        [
            'body' => 'Some Other Course',
            '_joinData' => [
                'grade' => 2,
            ],
        ],
        [
            'body' => 'And Yet Another Course',
            '_joinData' => [
                'grade' => 3,
            ],
        ],
    ],
]
```

### Using options to configure the associations

Additionally to the chained method call syntax, options as known from the built-in associations are supported too,
specifically the following options are supported for both `partitionableHasMany()` as well as
`partitionableBelongsToMany()`:

* `limit` (`int|null`)
* `singleResult` (`bool`)
* `filterStrategy` (`string`)

```php
$this
    ->partitionableHasMany('TopComments', [
        'className' => 'Comments',
        'limit' => 1,
        'singleResult' => false,
        'filterStrategy' => \Icings\Partitionable\ORM\Association\PartitionableHasMany::FILTER_IN_SUBQUERY_TABLE,
        'sort' => [
          'TopComments.votes' => 'DESC',
          'TopComments.id' => 'ASC',
        ],
    ]);
```

### Changing settings on the fly

The limit and the sort order can be applied/changed on the fly in the containment's query builder:

```php
$articlesQuery = $this->Articles
    ->find()
    ->contain('TopComments', function (\Cake\ORM\Query $query) {
        return $query
            ->limit(10)
            ->order([
                'TopComments.votes' => 'DESC',
                'TopComments.id' => 'ASC',
            ]);
    });
```

and via `Model.beforeFind`:

```php
$this->Articles->TopComments
    ->getEventManager()
    ->on('Model.beforeFind', function ($event, \Cake\ORM\Query $query) {
        return $query
            ->limit(10)
            ->order([
                'TopComments.votes' => 'DESC',
                'TopComments.id' => 'ASC',
            ]);
    });
```

### Limiting to a single result

When setting the limit to `1`, the associations will automatically switch to using singular property names (if no 
property name has been set yet), and non-nested results.

For example, limiting this association to `1`:

```php
$this
    ->partitionableHasMany('TopComments')
    ->setClassName('Comments')
    ->setLimit(1)
    ->setSort([
        'TopComments.votes' => 'DESC',
        'TopComments.id' => 'ASC',
    ]);
```

would return a result like this:

```php
[
    'title' => 'Some Article',
    'top_comment' => [
        'votes' => 10,
        'body' => 'Some Comment',
    ],
]
```

while a limit of greater or equal to `2`, would return a result like this:

```php
[
    'title' => 'Some Article',
    'top_comments' => [
        [
            'votes' => 10,
            'body' => 'Some Comment',
        ],
        [
            'votes' => 5,
            'body' => 'Some Other Comment',
        ],
    ],
]
```

This behavior can be disabled using the association's `disableSingleResult()` method, and likewise _enabled_ using
`enableSingleResult()`. Calling the latter will also cause the limit to be set to `1`. Furthermore, setting the limit
to greater or equal to `2`, will automatically disable the single result mode.

With the single result mode disabled:

```php
$this
    ->partitionableHasMany('TopComments')
    ->setClassName('Comments')
    ->setLimit(1)
    ->disableSingleResult()
    ->setSort([
        'TopComments.votes' => 'DESC',
        'TopComments.id' => 'ASC',
    ]);
```

a limit of `1` would return a result like this:

```php
[
    'title' => 'Some Article',
    'top_comments' => [
        [
            'votes' => 10,
            'body' => 'Some Comment',
        ],
    ],
]
```

### Filter Strategies

The associations currently provide a few different filter strategies that affect how the query that obtains the
associated data is being filtered.

Not all queries are equal, while one strategy may work fine for one query, it might cause problems for another.

The strategy can be set using the association's `setFilterStrategy()` method:

```php
use Icings\Partitionable\ORM\Association\PartitionableHasMany;

// ...

$this
    ->partitionableHasMany('TopComments')
    ->setClassName('Comments')
    ->setFilterStrategy(PartitionableHasMany::FILTER_IN_SUBQUERY_TABLE)
    ->setLimit(3)
    ->setSort([
        'TopComments.votes' => 'DESC',
        'TopComments.id' => 'ASC',
    ]);
```

Please refer to the API docs for SQL examples of how the different strategies work:

* [`\Icings\Partitionable\ORM\Association\Loader\PartitionableSelectLoader`](
  src/ORM/Association/Loader/PartitionableSelectLoader.php)
* [`\Icings\Partitionable\ORM\Association\Loader\PartitionableSelectWithPivotLoader`](
  src/ORM/Association/Loader/PartitionableSelectWithPivotLoader.php)

The currently available strategies are:

* `\Icings\Partitionable\ORM\Association\PartitionableAssociationInterface::FILTER_IN_SUBQUERY_CTE`
* `\Icings\Partitionable\ORM\Association\PartitionableAssociationInterface::FILTER_IN_SUBQUERY_JOIN`
* `\Icings\Partitionable\ORM\Association\PartitionableAssociationInterface::FILTER_IN_SUBQUERY_TABLE` (default)
* `\Icings\Partitionable\ORM\Association\PartitionableAssociationInterface::FILTER_INNER_JOIN_CTE`
* `\Icings\Partitionable\ORM\Association\PartitionableAssociationInterface::FILTER_INNER_JOIN_SUBQUERY`


## Known Issues/Limitations

* These associations are **_not_** meant for save or delete operations, _only_ for read operations!

* MySQL 5 is not supported as it doesn't support the required window functions used for row numbering. While it's
  possible to emulate the required row numbering, these constructs are rather fragile and there's way too many
  situations in which they will break, respectively silently produce wrong results.

* MariaDB, when running in `ONLY_FULL_GROUP_MY` mode, erroneously requires a `GROUP BY` clause to be present when using
  window functions like the one used for row numbering (https://jira.mariadb.org/browse/MDEV-17785). There isn't much
  that can be done about it until the bug is fixed, other than disabling `ONLY_FULL_GROUP_BY`, or adding grouping to
  the association's query accordingly.

* SQL Server does not support common table expressions in subqueries, hence the `FILTER_IN_SUBQUERY_CTE` strategy cannot
  be used with it. In fact, it's also not possible to use custom common table expressions in the association's query
  with any other strategy, as it would result in the expression to be used in a subquery too, or nested in another
  common table expression, which also isn't supported.
