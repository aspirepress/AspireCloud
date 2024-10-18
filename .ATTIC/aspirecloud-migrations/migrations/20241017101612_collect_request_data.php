<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CollectRequestData extends AbstractMigration
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
        $requestData = $this->table('request_data', ['id' => false, 'primary_key' => ['id']]);
        $requestData->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('request_path', 'string', ['null' => false])
            ->addColumn('request_query_params', 'json', ['null' => true])
            ->addColumn('request_body', 'json', ['null' => true])
            ->addColumn('request_headers', 'json', ['null' => false])
            ->addColumn('response_code', 'integer', ['null' => false])
            ->addColumn('response_body', 'text', ['null' => true])
            ->addColumn('response_headers', 'json', ['null' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'NOW()'])
            ->create();
    }
}
