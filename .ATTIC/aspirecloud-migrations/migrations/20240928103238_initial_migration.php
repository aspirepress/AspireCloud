<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialMigration extends AbstractMigration
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
        $plugins = $this->table('plugins', ['id' => false, 'primary_key' => 'id']);
        $plugins->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('slug', 'string', ['null' => false])
            ->addColumn('current_version', 'string', ['null' => true])
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $files = $this->table('files', ['id' => false, 'primary_key' => 'id']);
        $files->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('plugin_id', 'uuid', ['null' => false])
            ->addColumn('filename', 'string', ['null' => false])
            ->addColumn('file_path', 'string', ['null' => false])
            ->addColumn('file_url', 'string', ['null' => true])
            ->addColumn('type', 'string', ['null' => false])
            ->addColumn('version', 'string', ['null' => false])
            ->addForeignKey('plugin_id', 'plugins', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['plugin_id', 'version', 'type'])
            ->create();

        $sites = $this->table('sites', ['id' => false, 'primary_key' => 'id']);
        $sites->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('host', 'string', ['null' => false])
            ->addIndex(['host'], ['unique' => true])
            ->create();

        $apiKeys = $this->table('api_keys', ['id' => false, 'primary_key' => 'id']);
        $apiKeys->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('key', 'string', ['null' => false])
            ->addColumn('site_id', 'uuid', ['null' => false])
            ->addColumn('key_prefix', 'string', ['null' => false])
            ->addColumn('revoked', 'datetime', ['null' => true])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->addIndex(['site_id', 'key_prefix'], ['unique' => true])
            ->create();
    }
}
