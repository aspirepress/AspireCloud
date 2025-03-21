<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class QueryThemesRequest extends Data
{
    use ThemeFields;

    public const ACTION = 'query_themes';

    /**
     * @param ?string[] $tags
     * @param string|array<string,bool> $fields
     */
    public function __construct(
        public readonly ?string $search = null, // text to search
        public readonly ?array $tags = null,    // tag or set of tags
        public readonly ?string $theme = null,  // slug of a specific theme
        public readonly ?string $author = null, // wp.org username of author
        public readonly ?string $browse = null, // one of popular|featured|updated|new
        public readonly mixed $fields = null,
        public readonly int $page = 1,
        public readonly int $per_page = 24,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $req = $request->query();

        // 'tag' is the query parameter, but we store it on the 'tags' field
        // we could probably do this with a mapping and cast instead, but this works too,
        // and we have to do custom processing on fields anyway.
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

        $req['fields'] = self::getFields($request, $defaultFields);
        return static::from($req);
    }
}
