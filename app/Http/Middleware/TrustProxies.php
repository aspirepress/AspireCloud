<?php
declare(strict_types=1);

namespace App\Http\Middleware;

class TrustProxies extends \Illuminate\Http\Middleware\TrustProxies
{
    protected $proxies = "*";
}
