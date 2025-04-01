<?php

namespace App\Values\WpOrg\Themes;

use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $query = $request->query();

        $query['tags'] = (array)Arr::pull($query, 'tag', []);

        $defaultFields = [
            'description' => true,
            'rating' => true,
            'homepage' => true,
            'template' => true,
        ];

        $query['fields'] = self::getFields($request, $defaultFields);
        return $query;
    }
}
