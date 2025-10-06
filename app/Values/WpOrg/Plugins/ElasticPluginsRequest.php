<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Plugins;

use App\Values\DTO;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Illuminate\Http\Request;

#[StripExtraParameters]
readonly class ElasticPluginsRequest extends DTO
{
    /**
     * @param list<string>|null $tags
     * @param list<string>|null $tagAnd
     * @param list<string>|null $tagOr
     * @param list<string>|null $tagNot
     * @param string|list<string>|null $fields
     */
    public function __construct(
        public ?string $search = null,
        public ?array $tags = null,
        public ?string $tag = null,
        public ?array $tagsAnd = null,
        public ?array $tagsOr = null,
        public ?array $tagsNot = null,
        public ?string $plugin = null,
        public ?string $author = null,
        public ?string $browse = null,
        public mixed $fields = null,
        public int $page = 1,
        public int $per_page = 24,
        public ?int $offset = null,
        public ?int $limit = null,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $query = $request->query->all();
        // search
        $query['search'] = trim((string) $request->query('search', ''));
        // compute offset/limit
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, (int) ($query['per_page'] ?? 24));
        $query['offset'] = ($page - 1) * $perPage;
        $query['limit'] = $perPage;
        // normalize all tag related params
        foreach (['tags', 'tag', 'tagsAnd', 'tagsOr', 'tagsNot'] as $key) {
            if (array_key_exists($key, $query)) {
                $query[$key] = self::normalizeTags($query[$key]);
            }
        }

        return $query;
    }

    /**
     * Normalize tags into lowercase, trimmed strings.
     *
     * @param string|array|null $input
     * @return list<string>
     */
    public static function normalizeTags(string|array|null $input): array
    {
        // array of strings
        if (is_array($input)) {
            return array_values(
                array_filter(
                    array_map(fn($tag) => strtolower(trim($tag)), $input)
                )
            );
        }
        // string of comma-separated values
        if (is_string($input)) {
            return array_values(
                array_filter(
                    array_map(fn($tag) => strtolower(trim($tag)), explode(',', $input))
                )
            );
        }

        return [];
    }
}
