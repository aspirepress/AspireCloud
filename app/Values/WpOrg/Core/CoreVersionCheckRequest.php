<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Core;

use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

#[StripExtraParameters]
readonly class CoreVersionCheckRequest extends Bag
{
    /**
     * Everything but $translations gets passed in the query string, and every param is optional, even $version.
     * Since we don't worry about json encoding, optional props are default to null instead of Optional.
     *
     * @param Collection<string, string>|null $extensions
     * @param array{os: string, bits: int}|null $platform_flags
     * @param array<string, array<string, array<string, string>>> $translations
     */
    public function __construct(
        public string|null $version = null,
        public string|null $locale = null,
        public string|null $php = null,
        public string|null $mysql = null,
        public string|null $local_package = null,
        public int|null $num_blogs = null,
        public int|null $num_users = null,
        public bool|null $multisite_enabled = null,
        public string|null $initial_db_version = null,
        public Collection|null $extensions = null,
        public array|null $platform_flags = null,
        public Collection|null $translations = null,
    ) {}

    #[Transforms(Request::class)]
    public static function _arrayFromRequest(Request $request): self
    {
        return self::from($request->all());
    }
}


// {
//   "translations": {
//     "admin": {
//       "de_DE": {
//         "POT-Creation-Date": "",
//         "PO-Revision-Date": "2025-04-06 15:27:56+0000",
//         "Project-Id-Version": "WordPress - 6.7.x - Administration",
//         "X-Generator": "GlotPress\/4.0.1"
//       }
//     },

