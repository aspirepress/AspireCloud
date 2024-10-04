<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDateTimeColumns extends AbstractMigration
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
        $versions = $this->table('plugin_files');
        $versions->addColumn('created', 'datetime', ['default' => 'NOW()'])
            ->addColumn('processed', 'datetime', ['null' => true])
            ->update();

        $pdo = $this->getAdapter()->getConnection();
        $sql = 'SELECT id, metadata FROM plugin_files WHERE metadata IS NOT NULL';
        $update = 'UPDATE plugin_files SET processed = :processed WHERE id = :id';
        $updateStmt = $pdo->prepare($update);
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $metadata = json_decode($row['metadata'], true);
            if (isset($metadata['finalized'])) {
                $updateStmt->execute([':processed' => $metadata['finalized'], ':id' => $row['id']]);
            }
        }
    }

    public function down(): void
    {
        $versions = $this->table('plugin_files');
        $versions->removeColumn('created')
            ->removeColumn('processed')
            ->update();
    }
}
