<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;

class CommentsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('comments');
        $this->setPrimaryKey(['id', 'id2']);

        $this
            ->belongsTo('Articles')
            ->setForeignKey([
                'article_id',
                'article_id2',
            ]);
        $this
            ->hasMany('Replies')
            ->setForeignKey([
                'comment_id',
                'comment_id2',
            ])
            ->setSort(['Replies.id' => 'ASC']);
    }

    public function findPublished(Query $query, array $options): Query
    {
        return $query->where([
            $this->aliasField('published') => true,
        ]);
    }
}
