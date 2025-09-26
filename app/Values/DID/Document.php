<?php

namespace App\Values\DID;

use App\Values\DTO;
use Bag\Attributes\MapOutputName;
use Bag\Attributes\Transforms;
use Bag\Mappers\Alias;

readonly class Document extends DTO
{
    /**
     * @param string|array<string> $context
     * @param array<string> $alsoKnownAs
     * @param string $id
     * @param array<array<string, string>> $service
     * @param array<array<string, string>> $verificationMethod
     */
    public function __construct(
        #[MapOutputName(Alias::class, '@context')]
        public string|array $context,
        public array $alsoKnownAs,
        public string $id,
        public array $service,
        public array $verificationMethod,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'context' => ['required'],
            'alsoKnownAs' => ['present', 'array'],
            'id' => ['required', 'string'],
            'service' => ['required', 'array'],
            'verificationMethod' => ['present', 'array'],
        ];
    }
}
