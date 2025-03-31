<?php

declare(strict_types=1);

namespace App\Values\Props;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Override;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Optional;

final readonly class PluginProps extends ModelProps
{
    /**
     * @param Optional|array<string, mixed> $tags
     * @param Optional|array<string, mixed>|null $ac_raw_metadata
     */
    public function __construct(
        #[Uuid]
        public Optional|string $id,
        #[Between(1, 255)]
        public string $slug,
        #[Between(1, 255)]
        public string $name,
        #[Between(0, 150)]
        public string $short_description,
        #[Between(1, 1024 * 128)]
        public string $description,
        #[Between(1, 32)]
        public string $version,
        #[Between(1, 255)]
        public string $author,
        #[Between(1, 32)]
        public string $requires,
        #[Between(1, 32)]
        public Optional|string|null $requires_php,
        #[Between(1, 32)]
        public string $tested,
        #[Url]
        #[Max(1024)]
        public string $download_link,
        public DateTimeInterface $added,
        public ?DateTimeInterface $last_updated,
        #[Url]
        #[Max(1024)]
        public Optional|string|null $author_profile,
        #[Between(0, 100)]
        public Optional|int $rating,
        #[Min(0)]
        public Optional|int $num_ratings,
        #[Min(0)]
        public Optional|int $support_threads,
        #[Min(0)]
        public Optional|int $support_threads_resolved,
        #[Min(0)]
        public Optional|int $active_installs,
        #[Min(0)]
        public Optional|int $downloaded,
        #[Url]
        #[Max(1024)]
        public Optional|string|null $homepage,
        #[Url]
        #[Max(1024)]
        public Optional|string|null $donate_link,
        #[Max(255)]
        public Optional|string|null $business_model,
        #[Url]
        #[Max(1024)]
        public Optional|string|null $commercial_support_url,
        #[Url]
        #[Max(1024)]
        public Optional|string|null $support_url,
        #[Url]
        #[Max(1024)]
        public Optional|string|null $preview_link,
        #[Url]
        #[Max(1024)]
        public Optional|string|null $repository_url,

        // associations
        public Optional|array $tags, // TODO

        // AC-specific
        #[Between(1, 32)]
        public Optional|string $ac_origin,
        public Optional|CarbonImmutable|null $ac_created,
        public Optional|array|null $ac_raw_metadata,
    ) {}

    /**
     * @param array<string, mixed> $extra
     */
    public static function make(
        string $slug,
        string $name,
        string $short_description,
        string $description,
        string $version,
        string $author,
        string $requires,
        string $tested,
        string $download_link,
        DateTimeInterface $added,
        ?DateTimeInterface $last_updated,
        array $extra = [],
    ): self {
        $args = [
            ...$extra,
            ...compact(
                'slug',
                'name',
                'short_description',
                'description',
                'version',
                'author',
                'requires',
                'tested',
                'download_link',
                'added',
                'last_updated',
            ),
        ];
        return self::from($args);
    }

    #[Override] // empty override to narrow the return type (BaseData::from is not generic)
    public static function from(
        mixed ...$args,
    ): static {
        return parent::from(...$args);
    }
}
