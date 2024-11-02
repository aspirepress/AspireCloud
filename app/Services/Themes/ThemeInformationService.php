<?php

namespace App\Services\Themes;

use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Http\Resources\ThemeResource;
use App\Models\WpOrg\Theme;
use App\NotFoundException;

class ThemeInformationService
{
    public function info(ThemeInformationRequest $request): ThemeResource
    {
        $theme = Theme::query()->where('slug', $request->slug)->first() or throw new NotFoundException("Theme not found");
        return (new ThemeResource($theme))->additional(['fields' => $request->fields]);
    }
}
