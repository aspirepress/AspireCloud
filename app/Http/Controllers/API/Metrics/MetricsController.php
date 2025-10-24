<?php

namespace App\Http\Controllers\API\Metrics;

use App\Http\Controllers\Controller;
use App\Services\Metrics\MetricsService;
use App\Utils\Regex;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class MetricsController extends Controller
{
    private const string REQUESTS_COUNT = 'metrics_request_count';
    private const string REQUEST_COUNT_ROUTE = 'metrics_request_count_route_';

    public function __construct(
        private MetricsService $metricsService,
    ) {}

    public function __invoke(): Response
    {
        $lines = [];
        // total request count
        $lines[] = '# HELP requests_total Total number of requests';
        $lines[] = '# TYPE requests_total counter';
        $lines[] = 'requests_total ' . $this->metricsService->get(self::REQUESTS_COUNT);
        // requests by route
        $lines[] = '# HELP requests_by_route_total Requests by route';
        $lines[] = '# TYPE requests_by_route_total counter';

        $alreadySeen = [];

        $routes = Route::getRoutes()->getRoutes();
        foreach ($routes as $route) {
            $name = $route->getName();
            if (empty($name)) {
                $name = $route->uri();
            }
            // sanitize and skip duplicates
            $sanitized = self::sanitize($name);

            if (in_array($sanitized, $alreadySeen)) {
                continue;
            }
            $alreadySeen[] = $sanitized;

            $key = self::REQUEST_COUNT_ROUTE . $sanitized;
            $count = $this->metricsService->get($key);
            $escaped = addslashes($name);

            if ($count > 0) {
                $lines[] = "requests_by_route_total{route=\"{$escaped}\"} {$count}";
            }
        }

        // model counts
        $lines[] = '# HELP model_count Total rows per model';
        $lines[] = '# TYPE model_count gauge';
        $lines[] = 'model_count{model="plugins"} ' . DB::table('plugins')->count();

        return response(
            implode("\n", $lines),
            200,
            [
                'Content-Type' => 'text/plain; version=0.0.4',
            ],
        );
    }

    /**
     * @param string $value
     * @return string
     */
    private static function sanitize(string $value): string
    {
        return Regex::replace('/[\/\.\-\{\}\:]/', '_', $value);
    }
}
