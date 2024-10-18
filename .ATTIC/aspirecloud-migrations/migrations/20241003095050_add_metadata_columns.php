<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMetadataColumns extends AbstractMigration
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
        $plugins = $this->table('plugins');
        $plugins->addColumn('metadata', 'jsonb', ['null' => true])
            ->update();

        $plugin_files = $this->table('plugin_files');
        $plugin_files->addColumn('metadata', 'jsonb', ['null' => true])
            ->update();
    }
}
