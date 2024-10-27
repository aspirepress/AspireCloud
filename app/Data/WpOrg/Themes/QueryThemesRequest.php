<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class QueryThemesRequest extends Data
{
    use ThemeFields;

    public const ACTION = 'query_themes';

    /**
     * @param ?string $search
     * @param ?string[] $tags
     * @param ?string $theme
     * @param ?string $author
     * @param ?string $browse
     * @param ?array<string,bool> $fields
     * @param int $page
     * @param int $per_page
     */
    public function __construct(
        public readonly ?string $search = null, // text to search
        public readonly ?array $tags = null,    // tag or set of tags
        public readonly ?string $theme = null,  // slug of a specific theme
        public readonly ?string $author = null, // wp.org username of author
        public readonly ?string $browse = null, // one of popular|featured|updated|new
        public readonly ?array $fields = null,
        public readonly int $page = 1,
        public readonly int $per_page = 24,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $req = $request->query('request');

        if (is_array($req) && array_key_exists('tag', $req)) {
            $req['tags'] = is_array($req['tag']) ? $req['tag'] : [$req['tag']];
            unset($req['tag']);
        }
        $defaultFields = [
            'description' => true,
            'rating'      => true,
            'homepage'    => true,
            'template'    => true,
        ];
        if (version_compare($request->route('version'), '1.2', '>=')) {
            $defaultFields['extended_author'] = true;
            $defaultFields['num_ratings'] = true;
            $defaultFields['parent'] = true;
            $defaultFields['requires'] = true;
            $defaultFields['requires_php'] = true;
            $defaultFields['is_commercial'] = true;
            $defaultFields['is_community'] = true;
            $defaultFields['external_repository_url'] = true;
            $defaultFields['external_support_url'] = true;
        }

        $req['fields'] = self::getFields($request, $defaultFields);
        return static::from($req);
    }
}

// public const FIELDS_DEFAULT = [
//     'description' => false,
//     'sections' => false,
//     'rating' => true,
//     'ratings' => false,
//     'downloaded' => true,
//     'download_link' => true,
//     'last_updated' => true,
//     'homepage' => true,
//     'tags' => true,
//     'template' => true,
//     'parent' => false,
//     'versions' => false,
//     'screenshot_url' => true,
//     'active_installs' => false,
// ];
