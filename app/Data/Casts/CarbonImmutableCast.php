<?php

namespace App\Data\Casts;

use Carbon\CarbonImmutable;
use DateTimeZone;
use Override;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Exceptions\CannotCastDate;

class CarbonImmutableCast extends DateTimeInterfaceCast
{
    #[Override]
    protected function castValue(
        ?string $type,
        mixed $value,
    ): Uncastable|CarbonImmutable|null {
        if ($type === null) {
            return Uncastable::create();
        }

        $datetime = CarbonImmutable::parse($value);

        $this->setTimeZone ??= config('data.date_timezone');

        if ($this->setTimeZone) {
            return $datetime->setTimezone(new DateTimeZone($this->setTimeZone));
        }

        return $datetime;
    }
}
