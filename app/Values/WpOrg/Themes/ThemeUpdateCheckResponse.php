<?php

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use Bag\Bag;
use Illuminate\Support\Collection;

readonly class ThemeUpdateCheckResponse extends Bag
{
    /**
     * @param Collection<string, ThemeUpdateData> $themes
     * @param Collection<string, ThemeUpdateData> $no_update
     * @param Collection<array-key, mixed> $translations
     */
    public function __construct(
        public Collection $themes,
        public Collection $no_update,
        public Collection $translations,
    ) {}

    /**
     * @param iterable<array-key, Theme> $themes
     * @param iterable<array-key, Theme> $no_update
     */
    public static function fromResults(iterable $themes, iterable $no_update): self
    {
        return new self(
            themes: ThemeUpdateData::collect($themes)->keyBy('theme'),
            no_update: ThemeUpdateData::collect($no_update)->keyBy('theme'),
            translations: collect(), // TODO
        );
    }
}
