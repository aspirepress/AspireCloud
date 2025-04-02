<?php

namespace App\Http\Resources;

use App\Models\WpOrg\Author;
use App\Models\WpOrg\Theme;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
     *     requires: array<string, string>,
     *     requires_php: string,
     *     is_commercial: bool,
     *     external_support_url: string|bool,
     *     is_community: bool,
     *     external_repository_url: string
     * }
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;
        assert($resource instanceof Theme);
        $author = $resource->author->toArray();
        unset($author['id']);

        $tags = $resource->tagsArray();
        ksort($tags);

        return [
            'name' => $resource->name,
            'slug' => $resource->slug,
            'version' => $resource->version,
            'preview_url' => $resource->preview_url,
            'author' => $this->whenField('extended_author', $author, $resource->author->user_nicename),
            'description' => $this->whenField('description', fn() => $resource->description),
            'screenshot_url' => $this->whenField('screenshot_url', fn() => $resource->screenshot_url),
            'ratings' => $this->whenField('ratings', fn() => (object) $resource->ratings),  // need the object cast when all keys are numeric
            'rating' => $this->whenField('rating', fn() => $resource->rating),
            'num_ratings' => $this->whenField('rating', fn() => $resource->num_ratings),
            'reviews_url' => $this->whenField('reviews_url', $resource->reviews_url),
            'downloaded' => $this->whenField('downloaded', fn() => $resource->downloaded),
            'active_installs' => $this->whenField('active_installs', $resource->active_installs),
            'last_updated' => $this->whenField('last_updated', fn() => $resource->last_updated->format('Y-m-d')),
            'last_updated_time' => $this->whenField('last_updated', fn() => $resource->last_updated->format('Y-m-d H:i:s')),
            'creation_time' => $this->whenField('creation_time', fn() => $resource->creation_time->format('Y-m-d H:i:s')),
            'homepage' => $this->whenField('homepage', fn() => "https://wordpress.org/themes/{$resource->slug}/"),
            'sections' => $this->whenField('sections', fn() => $resource->sections),
            'download_link' => $this->whenField('downloadlink', fn() => $resource->download_link ?? ''),
            'tags' => $this->whenField('tags', fn() => $tags),
            'versions' => $this->whenField('versions', fn() => $resource->versions),
            'requires' => $this->whenField('requires', $resource->requires),
            'requires_php' => $this->whenField('requires_php', $resource->requires_php),
            'is_commercial' => $this->whenField('is_commercial', fn() => $resource->is_commercial),
            'external_support_url' => $this->whenField('external_support_url', fn() => $resource->is_commercial ? $resource->external_support_url : false),
            'is_community' => $this->whenField('is_community', fn() => $resource->is_community),
            'external_repository_url' => $this->whenField('external_repository_url', fn() => $resource->is_community ? $resource->external_repository_url : ''),
        ];
    }

    private function whenField(string $fieldName, mixed $value, mixed $default = null): mixed
    {
        $include = $this->additional['fields'][$fieldName] ?? false;
        // calling with default: null behaves differently from leaving it out
        if (func_num_args() === 3) {
            return $this->when($include, $value, $default);
        }
        return $this->when($include, $value);
    }
}
