<?php

namespace App\Values\WpOrg\Plugins;

use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Illuminate\Http\Request;

// Far simpler than ThemeInformationRequest, it takes a slug and that's it
#[StripExtraParameters]
readonly class PluginInformationRequest extends Bag
{
    public const ACTION = 'plugin_information';

    public function __construct(public string $slug) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function _arrayFromRequest(Request $request): array
    {
        // Bag throws 500 (RuntimeException) for missing fields, this throws a friendlier 422
        return $request->validate(['slug' => 'required']);
    }
}
