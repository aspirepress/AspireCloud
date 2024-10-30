<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ThemeInformationRequest extends Data
{
    use ThemeFields;
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
        $req = $request->query('request') ?? $request->all();

        $defaultFields = [
            'sections'     => true,
            'rating'       => true,
            'downloaded'   => true,
            'downloadlink' => true,
            'last_updated' => true,
            'homepage'     => true,
            'tags'         => true,
            'template'     => true,
        ];

        $req['fields'] = self::getFields($request, $defaultFields);
        self::validate($req);
        return static::from($req);
    }
}
