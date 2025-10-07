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
     * @param string|list<string>|null $tags
     * @param list<string>|null $tagsAnd
     * @param list<string>|null $tagsOr
     * @param list<string>|null $tagsNot
     * @param string|list<string>|null $fields
     */
    public function __construct(
        public ?string $search = null,
        /** @var string|list<string>|null */
        public string|array|null $tags = null,
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

        $value = $request->query('search', '');

        if (is_array($value)) {
            // Join multiple search values into one string
            $value = implode(' ', $value);
        }

        $query['search'] = trim($value);
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, (int) ($query['per_page'] ?? 24));
        $query['offset'] = ($page - 1) * $perPage;
        $query['limit'] = $perPage;

        // normalize tags and tag operators
        foreach (['tags', 'tag', 'tagsAnd', 'tagsOr', 'tagsNot'] as $key) {
            if (array_key_exists($key, $query)) {
                $normalized = self::normalizeTags($query[$key]);
                // tag is a single string
                $query[$key] = $key === 'tag'
                    ? ($normalized[0] ?? null)
                    : $normalized;
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
        $tags = [];

        if (is_array($input)) {
            $tags = $input;
        } elseif (is_string($input)) {
            $tags = explode(',', $input);
        }

        return array_values(array_filter(
            array_map(static fn($tag) => strtolower(trim($tag)), $tags)
        ));
    }
}
