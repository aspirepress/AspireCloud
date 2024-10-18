<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class PluginSeed extends AbstractSeed
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
        $plugins = [
            [
                'id' => '01D299FF-32A8-4C4F-ACDF-01A49131E9B0',
                'name' => 'AspirePress Updater',
                'slug' => 'aspirepress-updater',
                'current_version' => '1.0',
            ],
            [
                'id' => 'D1E70274-3300-4EE2-9C39-A7A19D02DFD1',
                'name' => 'Foobar Plugin',
                'slug' => 'foobar-plugin',
                'current_version' => '3.2.1.0',
            ],
        ];

        $this->insert('plugins', $plugins);
    }
}
