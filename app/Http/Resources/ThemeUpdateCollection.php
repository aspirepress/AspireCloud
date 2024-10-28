<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;

use function Safe\preg_match_all;

class ThemeUpdateCollection extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
    *     theme: string,
    *     slug: string,
    *     new_version: string,
    *     url: string,
    *     package: string,
    *     requires: bool,
    *     requires_php: string
    * }
     */
    public function toArray(Request $request): array
    {

        $data = [];
        $this->resource->each(function ($theme) use (&$data) {
            $data[$theme->slug] = $theme;
        });


        return $data;
    }

}
