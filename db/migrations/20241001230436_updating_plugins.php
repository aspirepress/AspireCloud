<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdatingPlugins extends AbstractMigration
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
    public function up(): void
    {
        $plugins = $this->table('plugins');
        $plugins->addColumn('updated', 'datetime', ['default' => 'NOW()'])
            ->addColumn('status', 'string', ['default' => 'open'])
            ->addColumn('pulled_at', 'datetime', ['default' => 'NOW()'])
            ->save();

        $files = $this->table('files');
        $files->rename('plugin_files')
            ->removeColumn('filename')
            ->removeColumn('file_path')
            ->save();
    }

    public function down(): void
    {
        $plugins = $this->table('plugins');
        $plugins->removeColumn('updated')
            ->removeColumn('status', 'string')
            ->removeColumn('pulled_at', 'datetime')
            ->save();

        $files = $this->table('plugin_files');
        $files->rename('files')
            ->addColumn('filename', 'string', ['null' => true])
            ->addColumn('file_path', 'string', ['null' => true])
            ->save();
    }
}
