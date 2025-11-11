<?php

namespace App\Values\Packages;

use App\Values\DTO;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Illuminate\Http\Request;

#[StripExtraParameters]
readonly class PackageImportRequest extends DTO
{
    public const ACTION = 'package_import';

    public function __construct(
        public string $data,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        // Bag throws 500 (RuntimeException) for missing fields, this throws a friendlier 422
        return $request->validate(['data' => 'required']);
    }
}
