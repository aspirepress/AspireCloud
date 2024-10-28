<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\MissingValue;

use function Safe\preg_match_all;

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
        $screenshotBase = "https://wp-themes.com/wp-content/themes/{$this->resource->slug}/screenshot";

        $data = [
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'version' => $this->resource->version,
            'tesdt' => $this->when(false, 'test'),
            'preview_url' => $this->resource->preview_url,
            'author' => $this->whenField('extended_author', $this->resource->author, $this->resource->author->user_nicename),
            'screenshot_url' => $this->whenField('screenshot_url', function () {
                $screenshots = $this->resource->screenshots;
                return $this->whenField(
                    'photon_screenshots',
                    isset($screenshots[$this->resource->version]) ? sprintf('https://i0.wp.com/themes.svn.wordpress.org/%1$s/%2$s/%3$s', $this->resource->slug, $this->resource->version, $screenshots[$this->resource->version]) : null,
                    isset($screenshots[$this->resource->version]) ? sprintf('//ts.w.org/wp-content/themes/%1$s/%2$s?ver=%3$s', $this->resource->slug, $screenshots[$this->resource->version], $this->resource->version) : null
                );
            }),
            'screenshot_count' => $this->whenField('screenshot_count', fn() => max($this->resource->screenshot_count ?? 1, 1)),
            'screenshots' => $this->whenField('screenshots', function () use ($screenshotBase) {
                $screenshotCount = max($this->resource->screenshot_count ?? 1, 1);
                return collect(range(1, $screenshotCount))->map(fn($i) => "{$screenshotBase}-{$i}.png");
            }),
            'ratings' => $this->whenField('ratings', fn() => $this->mapRatings($this->resource->ratings)),
            'rating' => $this->whenField('rating', fn() => $this->resource->rating * 20),
            'num_ratings' => $this->whenField('rating', fn() => $this->resource->num_ratings),
            'reviews_url' => $this->whenField('reviews_url', fn() => 'https://wordpress.org/support/theme/' . $this->resource->slug . '/reviews/'),
            'downloaded' => $this->whenField('downloaded', function () {
                /*
                $key = "theme-down:{$this->resource->slug}";
                return cache()->remember($key, now()->addMinutes(60), function () {
                    return (int) \DB::table('theme_stats')->where('slug', $this->resource->slug)->sum('downloads');
                });*/
                return 0;
            }),
            'active_installs' => $this->whenField('active_installs', function () {
                // TODO: Add active_installs
                $installs =  ($this->resource?->active_installs ?? '0');
                return $installs < 10 ? 0 : ($installs >= 3000000 ? 3000000 : str_pad(substr($installs, 0, 1), strlen($installs), '0'));
            }),
            'last_updated' => $this->whenField('last_updated', fn() => new CarbonImmutable($this->resource->last_updated)),
            'last_updated_time' => $this->whenField('last_updated', fn() => new CarbonImmutable($this->resource->last_updated_time)),
            'creation_time' => $this->whenField('creation_time', fn() => new CarbonImmutable($this->resource->creation_time)),
            'homepage' => $this->whenField('homepage', fn() => "https://wordpress.org/themes/{$this->resource->slug}/"),
            'download_link' => $this->whenField('downloadlink', fn() => $this->getDownloadUrl($this->resource->version)),
            'tags' => $this->whenField('tags', function () {
                return [];
                // TODO: return collect($this->resource->tags)->mapWithKeys(fn($tag) => [$tag->slug => $tag->name]);
            }),
            'versions' => $this->whenField('versions', function () {
                return [];
                // TODO: return collect($this->resource->all_versions)->mapWithKeys(fn($version) => [$version => $this->getDownloadUrl($version)]);
            }),
            'parent' => $this->whenField('parent', function () {
                $parent = $this->resource->parent_theme;
                return $parent ? [
                    'slug' => $parent->slug,
                    'name' => $parent->name,
                    'homepage' => "https://wordpress.org/themes/{$parent->slug}/",
                ] : new MissingValue();
            }),
            'sections' => $this->whenField('sections', fn() => $this->getSections()),
            'description' => $this->whenField('description', fn() => $this->getDescription()),

            'requires' => $this->whenField('requires', $this->resource->requires),
            'requires_php' => $this->whenField('requires_php', $this->resource->requires_php),
            'is_commercial' => $this->whenField('is_commercial', fn() => $this->resource->is_commercial),
            'external_support_url' => $this->whenField('external_support_url', fn() => $this->resource->is_commercial ? $this->resource->external_support_url : false),
            'is_community' => $this->whenField('is_community', fn() => $this->resource->is_community),
            'external_repository_url' => $this->whenField('external_repository_url', fn() => $this->resource->is_community ? $this->resource->external_repository_url : ''),
        ];

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
    private function whenField(string $fieldName, $value, $default = null)
    {
        $include = false;
        $includedFields = $this->additional['fields'] ?? [];
        $include = $includedFields[$fieldName] ?? false;
        if (func_num_args() === 3) {
            return $this->when($include, $value, $default);
        }
        return $this->when($include, $value);
    }

    /**
     * Gets the sections of the theme.
     *
     * @return array<string, string>
     */
    private function getSections()
    {
        $sections = [];
        if (preg_match_all('|--theme-data-(.+?)-->(.*?)<!|ims', $this->resource->content ?? "", $matches)) {
            foreach ($matches[1] as $i => $section) {
                $sections[$section] = trim($matches[2][$i]);
            }
        } else {
            $sections['description'] = $this->fixMangledDescription(trim($this->resource->content ?? ""));
        }
        return $sections;
    }


    /**
    * @param array<int>|null $ratings
    * @return Collection<string, int>
     */
    private function mapRatings(array|null $ratings = []): Collection
    {
        return collect($ratings)
            ->mapWithKeys(fn($value, $key) => [(string) $key => $value]);
    }
    /**
     * Get the description of the theme.
     *
     * @return string
     */
    private function getDescription()
    {
        return strpos($this->resource->content ?? "", '<!--') !== false
            ? trim(substr($this->resource->content, 0, strpos($this->resource->content, '<!--')))
            : trim($this->resource->content);
    }

    /**
     * Fixes mangled descriptions.
     *
     * @param string $description
     * @return string
     */
    private function fixMangledDescription($description)
    {
        return str_replace(['[br]', '[p]'], ["\n", "\n\n"], $description);
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
