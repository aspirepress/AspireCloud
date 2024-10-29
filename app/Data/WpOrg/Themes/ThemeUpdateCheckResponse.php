<?php

namespace App\Data\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;
use App\Data\WpOrg\Themes\ThemeUpdateData;

class ThemeUpdateCheckResponse extends Data
{
    /**
     * @param Collection<string,ThemeUpdateData> $themes
     * @param Collection<string,ThemeUpdateData> $no_update
     * @param mixed $translations
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
            translations: []
        );
    }
}
