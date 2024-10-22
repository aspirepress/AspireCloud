<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class ThemeInformationRequest extends Data
{
    public const ACTION = 'theme_information';

    /**
     * @param string $slug
     * @param ?array<string,bool> $fields
     */
    public function __construct(
        public readonly string $slug,
        public readonly ?array $fields = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return static::from($request->query('request'));
    }
}
