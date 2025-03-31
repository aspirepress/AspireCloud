<?php

namespace App\Values\WpOrg\Themes;

use Bag\Bag;
use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\Validation\Required;

readonly class ThemeInformationRequest extends Bag
{
    use ThemeFields;

    public const ACTION = 'theme_information';

    /**
     * @param ?array<string,bool> $fields
     */
    public function __construct(
        #[Required]
        public string $slug,
        public ?array $fields = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $req = $request->all();

        $defaultFields = [
            'sections' => true,
            'rating' => true,
            'downloaded' => true,
            'downloadlink' => true,
            'last_updated' => true,
            'homepage' => true,
            'tags' => true,
            'template' => true,
        ];

        if (version_compare($request->route('version'), '1.2', '>=')) {
            $defaultFields['reviews_url'] = true;
            $defaultFields['creation_time'] = true;
        }

        $req['fields'] = self::getFields($request, $defaultFields);
        self::validate($req);
        return static::from($req);
    }
}
