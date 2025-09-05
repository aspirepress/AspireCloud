<?php

namespace App\Values\Packages;

use App\Values\DTO;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Illuminate\Http\Request;

#[StripExtraParameters]
readonly class PackageInformationRequest extends DTO
{
    public const ACTION = 'package_information';

    public function __construct(public string $did) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        // Bag throws 500 (RuntimeException) for missing fields, this throws a friendlier 422
        return $request->validate(['did' => 'required']);
    }
}
