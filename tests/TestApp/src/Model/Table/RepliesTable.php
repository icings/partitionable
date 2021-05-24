<?php
declare(strict_types=1);

/**
 * A set of partitionable associations for the CakePHP ORM.
 *
 * @see https://github.com/icings/partitionable
 */

namespace Icings\Partitionable\Test\TestApp\Model\Table;

use Cake\ORM\Table;

class RepliesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('replies');
        $this->setPrimaryKey('id');

        $this->belongsTo('Authors');
        $this
            ->belongsTo('Comments')
            ->setForeignKey(['comment_id', 'comment_id2']);
    }
}
