<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueIndexToFiles extends AbstractMigration
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
        $pluginFiles = $this->table('plugin_files');
        $pluginFiles->addIndex(['plugin_id', 'version', 'type'], ['unique' => true])
            ->update();

        $themeFiles = $this->table('theme_files');
        $themeFiles->addIndex(['theme_id', 'version', 'type'], ['unique' => true])
            ->update();
    }
}
