<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Performance monitoring middleware for production optimization
 *
 * Tracks response times, query counts, and memory usage
 * to identify performance bottlenecks in production.
 */
class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip monitoring for certain routes (health checks, assets, etc.)
        if ($this->shouldSkipMonitoring($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Enable query logging for this request
        DB::enableQueryLog();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        // Calculate metrics
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // Convert to MB
        $queryCount = count(DB::getQueryLog());
        $slowQueries = $this->getSlowQueries(DB::getQueryLog());

        // Log performance metrics
        $this->logPerformanceMetrics([
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'query_count' => $queryCount,
            'slow_queries' => $slowQueries,
            'status_code' => $response->getStatusCode(),
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()?->current_organization_id,
            'timestamp' => now()->toISOString(),
        ]);

        // Add performance headers for debugging
        $response->headers->set('X-Execution-Time', $executionTime.'ms');
        $response->headers->set('X-Memory-Used', $memoryUsed.'MB');
        $response->headers->set('X-Query-Count', $queryCount);

        // Alert on performance issues
        if ($executionTime > 2000) { // > 2 seconds
            $this->alertSlowRequest($request, $executionTime, $queryCount);
        }

        if ($queryCount > 50) { // Too many queries
            $this->alertQueryHeavyRequest($request, $queryCount);
        }

        if (! empty($slowQueries)) {
            $this->alertSlowQueries($request, $slowQueries);
        }

        return $response;
    }

    /**
     * Determine if monitoring should be skipped for this request.
     */
    protected function shouldSkipMonitoring(Request $request): bool
    {
        $skipPatterns = [
            'health-check',
            'metrics',
            'telescope',
            'horizon',
            '_debugbar',
        ];

        $path = $request->path();

        foreach ($skipPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        // Skip static assets and API health checks
        return $request->is('api/health*')
            || $request->is('storage/*')
            || $request->is('_debugbar/*');
    }

    /**
     * Get slow queries from the query log.
     */
    protected function getSlowQueries(array $queryLog): array
    {
        $slowQueries = [];

        foreach ($queryLog as $query) {
            if (($query['time'] ?? 0) > 100) { // > 100ms
                $slowQueries[] = [
                    'sql' => $query['query'],
                    'time' => $query['time'],
                    'bindings' => $query['bindings'] ?? [],
                ];
            }
        }

        return $slowQueries;
    }

    /**
     * Log performance metrics to cache and/or external service.
     */
    protected function logPerformanceMetrics(array $metrics): void
    {
        // Store in cache for real-time monitoring
        $cacheKey = 'performance_metrics_'.date('Y-m-d-H');
        $existing = Cache::get($cacheKey, []);
        $existing[] = $metrics;

        // Keep only last 1000 entries per hour
        if (count($existing) > 1000) {
            $existing = array_slice($existing, -1000);
        }

        Cache::put($cacheKey, $existing, now()->addHours(2));

        // Log to Laravel logs for analysis
        $slowThreshold = config('performance.slow_threshold', 1000); // 1 second default
        $queryThreshold = config('performance.query_threshold', 20);

        if ($metrics['execution_time'] > $slowThreshold
            || $metrics['query_count'] > $queryThreshold
            || ! empty($metrics['slow_queries'])) {

            logger()->warning('Performance Alert', [
                'url' => $metrics['url'],
                'execution_time' => $metrics['execution_time'],
                'query_count' => $metrics['query_count'],
                'memory_used' => $metrics['memory_used'],
                'slow_queries_count' => count($metrics['slow_queries']),
            ]);
        }
    }

    /**
     * Alert on slow requests.
     */
    protected function alertSlowRequest(Request $request, float $executionTime, int $queryCount): void
    {
        $message = "Slow Request Detected: {$request->method()} {$request->fullUrl()} - {$executionTime}ms, {$queryCount} queries";

        // Send to monitoring service (configure as needed)
        if (config('performance.alerts.enabled', false)) {
            // Integration with monitoring service can be added here
            // e.g., Slack, Sentry, custom webhook
        }

        logger()->warning($message);
    }

    /**
     * Alert on query-heavy requests.
     */
    protected function alertQueryHeavyRequest(Request $request, int $queryCount): void
    {
        $message = "Query-Heavy Request: {$request->method()} {$request->fullUrl()} - {$queryCount} queries";

        logger()->warning($message);
    }

    /**
     * Alert on slow queries.
     */
    protected function alertSlowQueries(Request $request, array $slowQueries): void
    {
        $message = "Slow Queries in Request: {$request->method()} {$request->fullUrl()}";

        foreach ($slowQueries as $query) {
            $message .= "\n- {$query['time']}ms: {$query['sql']}";
        }

        logger()->warning($message);
    }
}
