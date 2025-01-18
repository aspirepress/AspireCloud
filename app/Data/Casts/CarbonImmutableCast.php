<?php

namespace App\Data\Casts;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use DateTimeZone;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\IterableItemCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Exceptions\CannotCastDate;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class CarbonImmutableCast implements Cast, IterableItemCast
{
    public function __construct(
        protected string|array|null $format = null,
        protected ?string $type = null,
        protected ?string $setTimeZone = null,
        protected ?string $timeZone = null,
    ) {}

    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context,
    ): DateTimeInterface|Uncastable {
        return $this->castValue(
            $this->type ?? $property->type->type->findAcceptedTypeForBaseType(DateTimeInterface::class),
            $value,
        );
    }

    public function castIterableItem(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context,
    ): DateTimeInterface|Uncastable {
        return $this->castValue($property->type->iterableItemType, $value);
    }

    protected function castValue(
        ?string $type,
        mixed $value,
    ): Uncastable|CarbonImmutable|null {
        $formats = collect($this->format ?? config('data.date_format'));

        if ($type === null) {
            return Uncastable::create();
        }

        /** @var CarbonImmutable|null $datetime */
        $datetime = rescue(fn() => CarbonImmutable::parse($value), report: false);

        if (!$datetime) {
            throw CannotCastDate::create($formats->toArray(), $type, $value);
        }

        $this->setTimeZone ??= config('data.date_timezone');

        if ($this->setTimeZone) {
            return $datetime->setTimezone(new DateTimeZone($this->setTimeZone));
        }

        return $datetime;
    }
}
