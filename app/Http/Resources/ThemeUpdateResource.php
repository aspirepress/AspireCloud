<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;

use function Safe\preg_match_all;

class ThemeUpdateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
    *     name: string,
    *     theme: string,
    *     new_version: string,
    *     url: string,
    *     package: string,
    *     requires: bool,
    *     requires_php: string
    * }
     */
    public function toArray(Request $request): array
    {
        $urlBase = "https://wp-themes.com/wp-content/themes/{$this->resource->slug}";

        $data = [
            'name' => $this->resource->name,
            'theme' => $this->resource->slug,
            'new_version' => $this->resource->version,
            'url' => $urlBase,
            'package' => $this->getDownloadUrl($this->resource->version),
            'requires' =>  $this->resource->requires,
            'requires_php' => $this->resource->requires_php,
        ];

        return $data;
    }

    /**
     * @param string $version
     * @return string
     */
    private function getDownloadUrl($version)
    {
        return 'downloadurl_placeholder' . $version;
        //return $this->resource->repo_package->download_url($version);
    }

}
