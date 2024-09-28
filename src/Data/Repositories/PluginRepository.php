<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Repositories;

use AspirePress\Cdn\Data\Entities\DownloadableFile;
use AspirePress\Cdn\Data\Entities\Plugin;
use AspirePress\Cdn\Data\Values\Version;
use Aura\Sql\ExtendedPdoInterface;
use Ramsey\Uuid\Uuid;

class PluginRepository
{
    public function __construct(private ExtendedPdoInterface $epdo)
    {
    }
    public function getPluginBySlug(string $slug): ?Plugin
    {
        if (empty($slug)) {
            return null;
        }

        $sql = 'SELECT * FROM plugins WHERE slug = :slug';
        $data = $this->epdo->fetchOne($sql, ['slug' => $slug]);

        if (!$data) {
            return null;
        }

        $sql = "SELECT * FROM files WHERE plugin_id = :plugin_id AND version = :version AND type = 'cdn'";
        $fileData = $this->epdo->fetchOne($sql, ['plugin_id' => $data['id'], 'version' => $data['current_version']]);
        if ($fileData) {
            $file = DownloadableFile::fromArray($fileData);
            $data['file'] = $file;
        }


        $plugin = Plugin::fromArray($data);
        return $plugin;
    }
}