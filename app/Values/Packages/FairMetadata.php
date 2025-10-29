<?php
declare(strict_types=1);

namespace App\Values\Packages;

use App\Enums\PackageType;
use App\Models\Package;
use App\Utils\Patterns;
use App\Values\DTO;
use Bag\Attributes\Hidden;
use Bag\Attributes\MapOutputName;
use Bag\Attributes\Transforms;
use Bag\Mappers\Alias;
use Bag\Validation\Rules\OptionalOr;
use Bag\Values\Optional;

/**
 * Represents metadata for a package in the FAIR protocol
 * with the addition of raw_metadata for storing the original metadata.
 *
 * @see https://github.com/fairpm/fair-protocol/blob/add-file-spec/specification.md#metadata-document
 */
readonly class FairMetadata extends DTO
{
    public const string CONTEXT = 'https://fair.pm/ns/metadata/v1';

    /**
     * @param string|array<string> $context
     * @param array<array<string, mixed>> $authors
     * @param array<array<string, mixed>> $security
     * @param array<array<string, mixed>> $releases
     * @param array<string> $keywords
     * @param array<string, mixed> $sections
     * @param array<string> $_links
     * @param array<string, mixed> $raw_metadata
     */
    public function __construct(
        // #[MapInputName(Alias::class, '@context')] // currently mapped by hand in fromMetadata()
        #[MapOutputName(Alias::class, '@context')]
        public string|array $context, // can be a string or an array of contexts

        public string $id,
        public string $type,
        public string $license,
        public array $authors,
        public Optional|array $security,
        public array $releases,
        public Optional|array $keywords,
        public Optional|array $sections,
        public Optional|array $_links,
        public string $slug, // Optional in FAIR requirements.
        public string $name, // Optional in FAIR requirements.
        public Optional|string $filename,
        public Optional|string $description,
        #[Hidden]
        public array $raw_metadata = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    #[Transforms('array')]
    public static function fromMetadata(array $data): array
    {
        $ret = [
            'context' => $data['@context'],
            'id' => $data['id'],
            'type' => $data['type'],
            'license' => $data['license'],
            'authors' => $data['authors'],
            'security' => $data['security'],
            'releases' => $data['releases'],
            'slug' => $data['slug'] ?? null,
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'raw_metadata' => $data,
        ];

        if (array_key_exists('sections', $data)) {
            $ret['sections'] = $data['sections'];
        }
        if (array_key_exists('keywords', $data)) {
            $ret['keywords'] = $data['keywords'];
        }
        if (array_key_exists('_links', $data)) {
            $ret['_links'] = $data['_links'];
        }

        return $ret;
    }

    /**
     * @param Package $package
     * @return array<string, mixed>
     */
    #[Transforms(Package::class)]
    public static function fromPackage(Package $package): array
    {
        $releases = $package
            ->releases
            ->map(fn($release) => [
                'version' => $release->version,
                'artifacts' => $release->artifacts,
                'provides' => $release->provides,
                'requires' => $release->requires,
                'suggests' => $release->suggests,
            ])
            ->toArray();

        $ret = [
            'context' => self::CONTEXT,
            'id' => $package->did,
            'type' => $package->type,
            'license' => $package->license,
            'authors' => $package
                ->authors
                ->map(fn($author) => array_filter([
                    'name' => $author->display_name,
                    'url' => $author->author_url,
                    // @todo - maybe store email in Author model, if it exists on the FAIR package
                ]))
                ->toArray(),
            'security' => $package->metas['metadata']['security'] ?? [],
            'releases' => $releases ?? [],
            'slug' => $package->slug,
            'name' => $package->name,
            'description' => $package->description,
            'raw_metadata' => $package->raw_metadata,
        ];

        if ($package->metas['metadata']['sections'] ?? false) {
            $ret['sections'] = $package->metas['metadata']['sections'];
        }

        if ($package->tags->isNotEmpty()) {
            $ret['keywords'] = $package->tags->pluck('name')->toArray();
        }

        return $ret;
    }

    /**
     * Validation rules for the FAIR metadata.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            '@context' => fn ($value) => is_array($value)
                    ? $value[0] === self::CONTEXT
                    : $value === self::CONTEXT,
            'id' => ['required', 'string'],
            'type' => ['required', 'string', 'in:' . implode(',', PackageType::values())],
            'license' => ['required', 'string'], // @todo - validate against SPDX licenses?
            'slug' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'keywords' => [new OptionalOr(['nullable', 'array'])],
            'keywords.*' => ['string'],
            'sections' => [new OptionalOr(['nullable', 'array'])],
            'sections.changelog' => ['nullable', 'string'],
            'sections.description' => ['nullable', 'string'],
            'sections.security' => ['nullable', 'string'],
            '_links' => [new OptionalOr(['nullable', 'array'])],
            ...self::authorsRules(),
            ...self::securityRules(),
            ...self::releasesRules(),
        ];
    }

    /**
     * Validation rules for the authors section.
     *
     * @return array<string, mixed>
     */
    private static function authorsRules(): array
    {
        // [chuck 2025-09-19] disabled for similar reasons as security, this won't handle a blank array
        return [
            'authors' => ['required', 'array'],
        ];
        // return [
        //     'authors' => ['required', 'array', 'min:1'],
        //     'authors.*' => [
        //         'required',
        //         'array',
        //         function (string $attribute, mixed $value, \Closure $fail) {
        //             if (empty($value['url']) && empty($value['email'])) {
        //                 $fail("Each author must have at least one of 'url' or 'email'.");
        //             }
        //         },
        //     ],
        //     'authors.*.name' => ['required', 'string'],
        //     'authors.*.url' => ['nullable', 'string', 'url'],
        //     'authors.*.email' => ['nullable', 'string', 'email'],
        // ];
    }

    /**
     * Validation rules for the security section.
     *
     * @return array<string, mixed>
     */
    private static function securityRules(): array
    {
        // [chuck 2025-09-19] largely disabled for now: some packages make this blank, which aborts the whole import.
        return [
            // 'security' => ['required', 'array'], // [chuck 2025-10-29] disabled entirely
        ];
        // return [
        //     'security' => ['required', 'array', 'min:1'],
        //     'security.*' => [
        //         'required',
        //         'array',
        //         function (string $attribute, mixed $value, \Closure $fail) {
        //             if (empty($value['url']) && empty($value['email'])) {
        //                 $fail("Each security contact must have at least one of 'url' or 'email'.");
        //             }
        //         },
        //     ],
        // ];
    }

    /**
     * Validation rules for the releases section.
     *
     * @return array<string, mixed>
     */
    private static function releasesRules(): array
    {
        return [
            'releases' => ['required', 'array'],
            'releases.*.version' => [
                'required',
                'string',
                // [chuck 2025-09-19] disabled for now, some packages have good versions that don't match this.
                // 'regex:' . Patterns::SEMANTIC_VERSION,
            ],
            'releases.*.artifacts' => ['required', 'array', 'min:1'],
            'releases.*.artifacts.*' => ['required', 'array'],
            'releases.*.artifacts.package' => ['required', 'array'],
            'releases.*.artifacts.package.*' => [
                'required',
                'array',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (empty($value['url'])) {
                        $fail("Each package artifact must include a 'url'.");
                    }
                },
            ],
        ];
    }
}
