<?php

namespace App\Values\WpOrg\Themes;

use Bag\Attributes\StripExtraParameters;
use Bag\Bag;
use Illuminate\Http\Request;

#[StripExtraParameters]
readonly class QueryThemesRequest extends Bag
{
    use ThemeFields;

    public const ACTION = 'query_themes';

    /**
     * @param ?string[] $tags
     * @param string|array<string,bool> $fields
     */
    public function __construct(
        public ?string $search = null, // text to search
        public ?array $tags = null,    // tag or set of tags
        public ?string $theme = null,  // slug of a specific theme
        public ?string $author = null, // wp.org username of author
        public ?string $browse = null, // one of popular|featured|updated|new
        public mixed $fields = null,
        public int $page = 1,
        public int $per_page = 24,
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
            'rating' => true,
            'homepage' => true,
            'template' => true,
        ];

        $req['fields'] = self::getFields($request, $defaultFields);
        return static::from($req);
    }
}
