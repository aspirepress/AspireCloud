<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Plugins;

use App\Models\WpOrg\ClosedPlugin;
use App\Values\DTO;
use Bag\Attributes\Transforms;

readonly class ClosedPluginResponse extends DTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $description,
        public string $closed_date,
        public string $reason,
        public string $reason_text,
        public string $error = 'closed',
        public bool $closed = true,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(ClosedPlugin::class)]
    public static function fromClosedPlugin(ClosedPlugin $plugin): array
    {
        return [
            'name' => $plugin->name,
            'slug' => $plugin->slug,
            'description' => $plugin->description,
            'closed_date' => $plugin->closed_date->format('Y-m-d'),
            'reason' => $plugin->reason,
            'reason_text' => $plugin->getReasonText(),
        ];
    }
}
