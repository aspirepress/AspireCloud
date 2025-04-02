<?php

namespace App\Values\WpOrg\Themes;

use Bag\Attributes\StripExtraParameters;
use Bag\Bag;
use Illuminate\Http\Request;

#[StripExtraParameters]
readonly class ThemeInformationRequest extends Bag
{
    use ThemeFields;

    public const ACTION = 'theme_information';

    /** @param array<string,bool>|null $fields */
    public function __construct(
        public string $slug,
        public ?array $fields = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        // this sort of defeats the purpose of Bag, but Bag doesn't throw validation failure on missing props, since it
        // checks for missing props before it runs validation rules (which is why overriding rules() won't work either).
        // TODO: generalize from on request classes to convert MissingPropertiesException to ValidationException
        $req = $request->validate(['slug' => 'required']);

        $defaultFields = [
            'sections' => true,
            'rating' => true,
            'downloaded' => true,
            'download_link' => true,
            'last_updated' => true,
            'last_updated_time' => true,
            'homepage' => true,
            'tags' => true,
            'template' => true,
        ];

        if (version_compare($request->route('version'), '1.2', '>=')) {
            $defaultFields['reviews_url'] = true;
            $defaultFields['creation_time'] = true;
        }

        $req['fields'] = static::getFields($request, $defaultFields);
        $req['last_updated_time'] = $req['last_updated'] ?? true;

        return static::from($req);
    }
}
