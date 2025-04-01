<?php

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use Bag\Bag;
use Illuminate\Support\Collection;

readonly class ThemeUpdateCheckResponse extends Bag
{
    /**
     * @param Collection<string,ThemeUpdateData> $themes
     * @param Collection<string,ThemeUpdateData> $no_update
     */
    public function __construct(
        public Collection $themes,
        public Collection $no_update,
        public mixed $translations,
    ) {}

    /**
     * @param Collection<int,Theme> $themes
     * @param Collection<int,Theme> $noUpdate
     */
    public static function fromData(Collection $themes, Collection $noUpdate): self
    {
        return new self(
            themes: ThemeUpdateData::fromModelCollection($themes),
            no_update: ThemeUpdateData::fromModelCollection($noUpdate),
            translations: [],
        );
    }
}
