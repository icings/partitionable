<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('authors');
        $this->setPrimaryKey('id');

        $this
            ->hasMany('Comments')
            ->setSort(['Comments.id' => 'ASC']);
        $this
            ->hasMany('Replies')
            ->setSort(['Replies.id' => 'ASC']);
    }
}
