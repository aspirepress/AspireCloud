<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteTestCommand extends Command
{
    protected $signature = 'route:test {uri}';
    protected $description = 'Match path against routes';

    public function handle(): int
    {
        $uri = $this->argument('uri');
        $router = Route::getRoutes();
        $request = Request::create($uri);
        try {
            $route = $router->match($request);
            $this->info("MATCH: {$route->uri} [{$route->getName()}]");
        } catch (NotFoundHttpException) {
            $this->fail("Route not found for $uri");
        }
        return 0;
    }
}
