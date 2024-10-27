<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;

class ThemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
    *     name: string,
    *     slug: string,
    *     version: string,
    *     preview_url: string,
    *     author: Author,
    *     screenshot_url: string,
    *     ratings: array{1:int, 2:int, 3:int, 4:int, 5:int},
    *     rating: int,
    *     num_ratings: int,
    *     reviews_url: string,
    *     downloaded: int,
    *     active_installs: int,
    *     last_updated: CarbonImmutable,
    *     last_updated_time: CarbonImmutable,
    *     creation_time: CarbonImmutable,
    *     homepage: string,
    *     sections: array<string, string>,
    *     download_link: string,
    *     tags: array<string, string>,
    *     versions: array<string, string>,
    *     requires: bool,
    *     requires_php: string,
    *     is_commercial: bool,
    *     external_support_url: string|bool,
    *     is_community: bool,
    *     external_repository_url: string
    * }
     */
    public function toArray(Request $request): array
    {
        $data = [
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'version' => $this->resource->version,
            'preview_url' => $this->resource->preview_url,
            'author' => $this->whenField('extended_author', $this->resource->author, $this->resource->author->user_nicename),
            'author_profile' => null, // TODO: $this->resource->author_profile,
            'screenshot_url' => $this->resource->screenshot_url,
            'ratings' => ['1' => 0,'2' => 0,'3' => 0,'4' => 0,'5' => 0], // TODO: Ratings missing: $this->mapRatings($this->resource->ratings),
            'rating' => $this->resource->rating,
            'num_ratings' => $this->resource->num_ratings,
            'reviews_url' => '', // TODO: $this->resource->reviews_url,
            'downloaded' =>  0, // TODO: $this->resource->downloaded,
            'active_installs' =>  0, // TODO: $this->resource->active_installs,
            'last_updated' =>  new CarbonImmutable(), // TODO: $this->resource->last_updated?->format('Y-m-d H:i:s'),
            'last_updated_time' => new CarbonImmutable(), // TODO: $this->resource->last_updated_time?->format('Y-m-d H:i:s'),
            'creation_time' => new CarbonImmutable(), // TODO: $this->resource->creation_time?->format('Y-m-d H:i:s'),
            'homepage' => $this->resource->homepage,
            'download_link' =>  '', // TODO: $this->resource->download_link,
            'tags' =>  [], // TODO: $this->resource->tags,
            'requires' => $this->resource->requires,
            'requires_php' => $this->resource->requires_php,
            'is_commercial' => $this->resource->is_commercial,
            'external_support_url' => $this->resource->external_support_url,
            'is_community' => $this->resource->is_community,
            'external_repository_url' => $this->resource->external_repository_url,
        ];
        // Add fields specific to the theme list
        if ($request->query('action') === 'query_themes') {
            $data['short_description'] = null; // TODO:  $this->resource->short_description;
            $data['description'] = $this->resource->description;
            $data['icons'] = null; // TODO: $this->resource->icons;
        }

        // Add fields specific to single plugin
        if ($request->query('action') === 'theme_information') {
            $data['sections'] = $this->resource->sections;
            $data['versions'] = $this->resource->versions;
            $data['contributors'] = $this->resource->contributors;
            $data['screenshots'] = $this->resource->screenshots;
        }

        return $data;
    }

    /**
     * When the given field is included, the value is returned.
     * Otherwise, the default value is returned.
     *
     * @param string $fieldName
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    private function whenField(string $fieldName, $value, $default)
    {
        $include = false;
        $includedFields = $this->additional['fields'] ?? [];
        $include = $includedFields[$fieldName] ?? false;
        return $this->when($include, $value, $default);
    }

}
