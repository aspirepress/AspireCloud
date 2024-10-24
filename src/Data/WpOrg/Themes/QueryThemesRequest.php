<?php

namespace App\Data\WpOrg\Themes;

use App\Data\WpOrg\AbstractWpOrgRequest;
use Symfony\Component\HttpFoundation\Request;

readonly class QueryThemesRequest extends AbstractWpOrgRequest {
    public const string ACTION = 'query_themes';

    /** @noinspection PhpUnused */
    public const array FIELDS_DEFAULT = [
        'description' => false,
        'sections' => false,
        'rating' => true,
        'ratings' => false,
        'downloaded' => true,
        'download_link' => true,
        'last_updated' => true,
        'homepage' => true,
        'tags' => true,
        'template' => true,
        'parent' => false,
        'versions' => false,
        'screenshot_url' => true,
        'active_installs' => false,
    ];

    /**
     * @param ?string $search
     * @param ?string[] $tags
     * @param ?string $theme
     * @param ?string $author
     * @param ?string $browse
     * @param array<string,bool>|null $fields
     * @param int $page
     * @param int $per_page
     */
    public function __construct(
        public ?string $search = null, // text to search
        public ?array $tags = null,    // tag or set of tags
        public ?string $theme = null,  // slug of a specific theme
        public ?string $author = null, // wp.org username of author
        public ?string $browse = null, // one of popular|featured|updated|new
        public ?array $fields = null,
        public int $page = 1,
        public int $per_page = 24,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $req = $request->query->all('request');
        if (array_key_exists('tag', $req) && !array_key_exists('tags', $req)) {
            $req['tags'] = is_array($req['tag']) ? $req['tag'] : [$req['tag']];
            unset($req['tag']);
        }
        return new self(
            search: $req['search'] ?? null,
            tags: $req['tags'] ?? [],
            theme: $req['theme'] ?? null,
            author: $req['author'] ?? null,
            browse: $req['browse'] ?? null,
            fields: $req['fields'] ?? null,
            page: $req['page'] ?? 1,
            per_page: $req['per_page'] ?? 24,
        );
    }
}

