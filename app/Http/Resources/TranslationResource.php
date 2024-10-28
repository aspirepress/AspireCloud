<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;

use function Safe\preg_match_all;

class TranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
    *     type: string,
    *     slug: string,
    *     language: string,
    *     version: string,
    *     updated: string,
    *     package: string,
    *     autoupdate: bool
    * }
     */
    public function toArray(Request $request): array
    {


        $data = [
            'type' => 'theme',
            'slug' => $this->resource->slug,
            'language' => 'en_US',
            'version' => '1.5',
            'updated' => '2021-06-01 12:00:00',
            'package' => 'https://downloads.wordpress.org/translation/theme/themeslug/1.5/en_US.zip',
            'autoupdate' => true,
        ];

        return $data;
    }

}
