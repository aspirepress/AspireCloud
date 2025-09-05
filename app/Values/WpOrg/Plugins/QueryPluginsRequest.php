<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Plugins;

use App\Utils\Regex;
use App\Values\DTO;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

#[StripExtraParameters]
readonly class QueryPluginsRequest extends DTO
{
    /** @param list<string>|null $tags
     * @param list<string>|null $tagAnd
     * @param list<string>|null $tagOr
     * @param list<string>|null $tagNot
     * @param string|list<string>|null $fields
     */
    public function __construct(
        public ?string $search = null,
        public ?array $tags = null,
        public ?string $tag = null,
        public ?array $tagAnd = null,
        public ?array $tagOr = null,
        public ?array $tagNot = null,
        public ?string $plugin = null,
        public ?string $author = null,
        public ?string $browse = null,
        public mixed $fields = null,
        public int $page = 1,
        public int $per_page = 24,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $query = $request->query();

        // Pull base params
        $search = Arr::pull($query, 'search', null);
        $author = Arr::pull($query, 'author', null);

        // Normalize tags:
        $tags = (array) Arr::pull($query, 'tags', []);
        $singleTag = Arr::pull($query, 'tag', null);
        if (is_string($singleTag) && $singleTag !== '') {
            $tags[] = $singleTag;
        }
        $tags = self::normalizeStringList($tags);

        // Normalize operators
        $tagAnd = self::normalizeStringList((array) Arr::pull($query, 'tag-and', []));
        $tagOr  = self::normalizeStringList((array) Arr::pull($query, 'tag-or', []));
        $tagNot = self::normalizeStringList((array) Arr::pull($query, 'tag-not', []));

        $query['search'] = self::normalizeSearchString($search);
        $query['author'] = self::normalizeSearchString($author);
        $query['tags']   = $tags;
        $query['tagAnd'] = $tagAnd;
        $query['tagOr']  = $tagOr;
        $query['tagNot'] = $tagNot;

        return $query;
    }

    /** Normalize a search-like string (trim + compact whitespace + limited punctuation). */
    private static function normalizeSearchString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        $value = Regex::replace('/\s+/i', ' ', $value);
        return Regex::replace('/[^\w.,!?@#$_-]/i', ' ', $value);
    }

    /** @param array<int,mixed> $items  @return list<string> */
    private static function normalizeStringList(array $items): array
    {
        $out = [];
        foreach ($items as $v) {
            if (!is_string($v)) {
                if (is_scalar($v)) {
                    $v = (string) $v;
                } else {
                    continue;
                }
            }
            $v = self::normalizeSearchString($v) ?? '';
            if ($v !== '') {
                $out[] = $v;
            }
        }
        // unique and reindex
        return array_values(array_unique($out));
    }
}
