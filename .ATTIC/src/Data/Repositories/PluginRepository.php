<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\Data\Repositories;

use AspirePress\AspireCloud\Data\Entities\DownloadableFile;
use AspirePress\AspireCloud\Data\Entities\Plugin;
use Aura\Sql\ExtendedPdoInterface;

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

        $sql  = 'SELECT * FROM plugins WHERE slug = :slug';
        $data = $this->epdo->fetchOne($sql, ['slug' => $slug]);

        if (! $data) {
            return null;
        }

        $sql      = "SELECT * FROM files WHERE plugin_id = :plugin_id AND version = :version AND type = 'cdn'";
        $fileData = $this->epdo->fetchOne($sql, ['plugin_id' => $data['id'], 'version' => $data['current_version']]);
        if ($fileData) {
            $file         = DownloadableFile::fromArray($fileData);
            $data['file'] = $file;
        }

        return Plugin::fromArray($data);
    }
}
