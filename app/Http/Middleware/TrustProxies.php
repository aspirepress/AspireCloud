<?php

namespace App\Http\Middleware;

class TrustProxies extends \Illuminate\Http\Middleware\TrustProxies
{
    protected $proxies = "*";
}
