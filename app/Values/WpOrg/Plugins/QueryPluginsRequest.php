<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Plugins;

use App\Values\DTO;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

// Completely isomorphic to QueryThemesRequest, except $theme is replaced with $plugin.  Hmm.
// I'd look into refactoring it, but it's not like .org is going to add a new resource type anytime soon.
// We can clean things up in the 2.0 API.
#[StripExtraParameters]
readonly class QueryPluginsRequest extends DTO
{
    /** @param list<string>|null $tags */
    public function __construct(
        public ?string $search = null,  // text to search
        public ?array $tags = null,     // tag or set of tags
        public ?string $plugin = null,  // slug of a specific plugin
        public ?string $author = null,  // wp.org username of author
        public ?string $browse = null,  // one of popular|top-rated|updated|new
        public mixed $fields = null,    // ignored-- all fields are always returned
        public int $page = 1,
        public int $per_page = 24,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $query = $request->query();

        $query['tags'] = (array)Arr::pull($query, 'tag', []);

        // $defaultFields = [
        //     'description' => true,
        //     'rating' => true,
        //     'homepage' => true,
        //     'template' => true,
        // ];
        // $query['fields'] = self::getFields($request, $defaultFields);
        return $query;
    }
}
