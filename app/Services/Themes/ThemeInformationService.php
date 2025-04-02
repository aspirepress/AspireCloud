<?php

namespace App\Services\Themes;

use App\Exceptions\NotFoundException;
use App\Models\WpOrg\Theme;
use App\Values\WpOrg\Themes\ThemeInformationRequest;
use App\Values\WpOrg\Themes\ThemeResponse;
use Bag\Values\Optional;

class ThemeInformationService
{
    public function info(ThemeInformationRequest $req): ThemeResponse
    {
        $theme = Theme::query()->where('slug', $req->slug)->first() or throw new NotFoundException("Theme not found");
        $theme = ThemeResponse::from($theme);
        if ($req->fields) {
            $none = new Optional();

            $omit = collect($req->fields)
                ->filter(fn($val, $key) => !$val && $key !== 'extended_author')
                ->mapWithKeys(fn($val, $key) => [$key => $none])
                ->toArray();

            $theme = $theme->with($omit);

            if (!($req->fields['extended_author'] ?? false)) {
                $theme = $theme->with(['author' => $theme->author->user_nicename]);
            }
        }
        return $theme;
    }
}
