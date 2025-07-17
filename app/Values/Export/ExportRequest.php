<?php

declare(strict_types=1);

namespace App\Values\Export;

use App\Values\DTO;
use Bag\Attributes\Transforms;
use Bag\Attributes\StripExtraParameters;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Inspired from QueryPluginsRequest.
#[StripExtraParameters]
readonly class ExportRequest extends DTO
{
    /** @param string|null $after */
    public function __construct(
        public ?string $after = null,  // date to query after
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request, string $type): array
    {
        if (!in_array($type, ['plugins', 'themes', 'closed_plugins'], true)) {
            throw ValidationException::withMessages([
                'type' => "Invalid export type: $type",
            ]);
        }

        $query = $request->validate(['after' => 'nullable|date|date_format:Y-m-d']);

        $query['type'] = $type;

        return $query;
    }
}
