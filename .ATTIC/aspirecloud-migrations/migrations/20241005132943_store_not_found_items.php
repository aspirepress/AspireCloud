<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StoreNotFoundItems extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $this->table('not_found_items', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'uuid')
            ->addColumn('item_type', 'string', ['null' => false])
            ->addColumn('item_slug', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'NOW()'])
            ->addColumn('updated_at', 'datetime', ['default' => 'NOW()'])
            ->create();
    }
}
