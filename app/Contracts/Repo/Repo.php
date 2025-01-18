<?php

declare(strict_types=1);

namespace App\Contracts\Repo;


/**
 * @template T
 */
interface Repo
{

    public function origin(): string;

}
