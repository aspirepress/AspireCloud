<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddThemeTables extends AbstractMigration
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
        $themes = $this->table('themes', ['id' => false, 'primary_key' => ['id']]);
        $themes->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('current_version', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('updated', 'datetime', ['null' => false])
            ->addColumn('pulled_at', 'datetime', ['null' => false, 'default' => 'NOW()'])
            ->addColumn('metadata', 'jsonb', ['null' => false])
            ->create();

        $themeFiles = $this->table('theme_files', ['id' => false, 'primary_key' => ['id']]);
        $themeFiles->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('theme_id', 'uuid', ['null' => false])
            ->addColumn('file_url', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('type', 'string', ['null' => false])
            ->addColumn('version', 'string', ['null' => false])
            ->addColumn('metadata', 'jsonb', ['null' => true])
            ->addColumn('created', 'datetime', ['null' => false, 'default' => 'NOW()'])
            ->addColumn('processed', 'datetime', ['null' => true])
            ->addForeignKey('theme_id', 'themes', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
