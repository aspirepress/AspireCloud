<?php

namespace App\Data\WpOrg\Themes;

use App\Data\WpOrg\AbstractWpOrgRequest;
use Symfony\Component\HttpFoundation\Request;

readonly class ThemeInformationRequest extends AbstractWpOrgRequest
{
    public const ACTION = 'theme_information';

    /**
     * @param string $slug
     * @param ?array<string,bool> $fields
     */
    public function __construct(
        public string $slug,
        public ?array $fields = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $slug = $request->query->get('slug');
        $fields = $request->query->all('fields');
        return new self($slug, $fields);
    }
}
