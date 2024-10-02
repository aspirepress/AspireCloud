<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class FileSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [
            PluginSeed::class,
        ];
    }

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
        $files = [
            [
                'id' => 'BB10EADB-365E-487C-82FD-D0436FD145CB',
                'plugin_id' => '01D299FF-32A8-4C4F-ACDF-01A49131E9B0',
                'file_url' => 'foo.zip',
                'type' => 'aws_cdn',
                'version' => '0.0.9',
            ],
            [
                'id' => 'FB3E236C-28E0-4B3A-9E25-5606E390DB15',
                'plugin_id' => '01D299FF-32A8-4C4F-ACDF-01A49131E9B0',
                'file_url' => 'foo1.zip',
                'type' => 'cf_cdn',
                'version' => '1.0.0',
            ],
            [
                'id' => '7B2F3182-AFCD-46AF-BB4F-55FB887A5181',
                'plugin_id' => 'D1E70274-3300-4EE2-9C39-A7A19D02DFD1',
                'file_url' => 'https://downloads.wordpress.org/plugin/fooplugin.zip',
                'type' => 'wp_cdn',
                'version' => '3.2.1.0',
            ],
        ];

        $this->insert('plugin_files', $files);
    }
}
