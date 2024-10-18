<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStatsTable extends AbstractMigration
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
        $stats = $this->table('stats', ['id' => false, 'primary_key' => 'id']);
        $stats->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('command', 'string', ['length' => 255, 'null' => false])
            ->addColumn('stats', 'jsonb', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'NOW()'])
            ->create();
    }
}
