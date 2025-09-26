<?php
declare(strict_types=1);

namespace App\Console\Commands;

/** Adds better type support to Laravel's built-in Command class */
abstract class Command extends \Illuminate\Console\Command
{
    /**
     * Get the value of a command argument.
     *
     * @param string|null $key
     * @return ($key is null ? array : string|bool|null)
     */
    public function argument(?string $key = null): array|string|bool|null
    {
        return parent::argument($key);
    }
}
