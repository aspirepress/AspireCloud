<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ThemeInformationRequest extends Data
{
    public const ACTION = 'theme_information';

    /**
     * @param string $slug
     * @param ?array<string,bool> $fields
     */
    public function __construct(
        #[Required]
        public readonly string $slug,
        public readonly ?array $fields = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = ($request->query('request') != null) ? static::from($request->query('request')) : static::from($request->all());
        self::validate($payload);
        return self::from($payload);
    }
}
