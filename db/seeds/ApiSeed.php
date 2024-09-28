<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class ApiSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $sites = [
            [
                'id' => 'CC49B9AC-516F-4F6F-81F9-11639A52805A',
                'host' => 'localhost',
            ],
            [
                'id' => '7C741E97-955D-415B-9120-DF28A9091626',
                'host' => 'wpconcierge.com',
            ]
        ];

        $apiKeys = [
            [
                'id' => 'CAD863E6-6EEF-49AA-8251-280982AE39F5',
                'key' => password_hash('password', PASSWORD_DEFAULT),
                'key_prefix' => 'vFgVfznZGN',
                'site_id' => 'CC49B9AC-516F-4F6F-81F9-11639A52805A',
            ],
            [
                'id' => '66F7CD68-F674-4F28-9045-8360157B6BEB',
                'key' => password_hash('password2', PASSWORD_DEFAULT),
                'key_prefix' => 'OmtmTMoLMl',
                'site_id' => 'CC49B9AC-516F-4F6F-81F9-11639A52805A',
            ],
            [
                'id' => '75D93BD3-0023-4733-AD54-35383067B6A1',
                'key' => password_hash('password3', PASSWORD_DEFAULT),
                'key_prefix' => 'eNREConcrO',
                'site_id' => '7C741E97-955D-415B-9120-DF28A9091626',
            ],
        ];

        $this->insert('sites', $sites);
        $this->insert('api_keys', $apiKeys);
    }
}
