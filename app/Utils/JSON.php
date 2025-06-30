<?php

declare(strict_types=1);

namespace App\Utils;

use Safe\Exceptions\JsonException;
use stdClass;

class JSON
{
    public const DEFAULT_JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    /**
     * @param array<string, mixed> $value
     * @throws JsonException
     */
    public static function fromAssoc(array $value, int $flags = self::DEFAULT_JSON_OPTIONS, int $depth = 512): string
    {
        return static::encode($value, $flags, $depth);
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException
     */
    public static function toAssoc(string $json, int $depth = 512, int $flags = self::DEFAULT_JSON_OPTIONS): array
    {
        return static::decode($json, true, $depth, $flags);
    }

    /** @return array<string, mixed>|null */
    public static function tryToAssoc(string $json, int $depth = 512, int $flags = self::DEFAULT_JSON_OPTIONS): ?array
    {
        try {
            return static::toAssoc($json, $depth, $flags);
        } catch (JsonException) {
            return null;
        }
    }

    /** @throws JsonException */
    public static function toObject(string $json, int $depth = 512, int $flags = self::DEFAULT_JSON_OPTIONS): stdClass
    {
        return static::decode($json, false, $depth, $flags);
    }

    /** @throws JsonException */
    public static function encode(mixed $value, int $flags = self::DEFAULT_JSON_OPTIONS, int $depth = 512): string
    {
        return \Safe\json_encode($value, $flags, $depth);
    }

    /** @throws JsonException */
    public static function decode(
        string $json,
        bool $assoc = false,
        int $depth = 512,
        int $flags = self::DEFAULT_JSON_OPTIONS,
    ): mixed {
        return \Safe\json_decode($json, $assoc, $depth, $flags);
    }
}
