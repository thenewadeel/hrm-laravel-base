<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Cache optimization middleware for production performance
 *
 * Implements intelligent caching strategies for API responses
 * and frequently accessed data.
 */
class CacheOptimizer
{
    /**
     * Cache duration configuration by route pattern
     */
    protected array $cacheDurations = [
        'api/accounts' => 300, // 5 minutes
        'api/organizations' => 600, // 10 minutes
        'api/reports/*' => 1800, // 30 minutes
        'api/inventory/*' => 300, // 5 minutes
        'dashboard' => 60, // 1 minute
        'reports/*' => 1800, // 30 minutes
    ];

    /**
     * Handle an incoming request with caching optimization.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip caching for certain requests
        if ($this->shouldSkipCaching($request)) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($request);

        // Check if response is cached
        if ($request->isMethod('GET') && $cached = Cache::get($cacheKey)) {
            return response($cached['content'])
                ->header('Content-Type', $cached['content_type'] ?? 'application/json')
                ->header('X-Cache', 'HIT')
                ->header('X-Cache-Age', now()->diffInSeconds($cached['cached_at']));
        }

        $response = $next($request);

        // Cache successful GET requests
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $this->cacheResponse($request, $response, $cacheKey);
        }

        // Add cache headers
        $response->header('X-Cache', 'MISS');
        $response->header('X-Cache-Key', $cacheKey);

        return $response;
    }

    /**
     * Determine if caching should be skipped for this request.
     */
    protected function shouldSkipCaching(Request $request): bool
    {
        // Skip non-GET requests
        if (! $request->isMethod('GET')) {
            return true;
        }

        // Skip authenticated requests with write permissions
        if (auth()->check() && $this->hasWritePermissions()) {
            return true;
        }

        // Skip specific routes
        $skipPatterns = [
            'api/health*',
            'telescope*',
            'horizon*',
            'debug*',
            'admin/*',
            'setup/*',
            'scanner',
            'cards/scan',
        ];

        foreach ($skipPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate cache key for request.
     */
    protected function generateCacheKey(Request $request): string
    {
        $keyParts = [
            'route' => $request->route()->getName(),
            'url' => $request->fullUrl(),
            'user' => auth()->id(),
            'org' => auth()->user()?->current_organization_id,
            'page' => $request->get('page', 1),
            'per_page' => $request->get('per_page', 15),
        ];

        // Include relevant query parameters
        $relevantParams = ['search', 'filter', 'sort', 'status', 'type'];
        foreach ($relevantParams as $param) {
            if ($request->has($param)) {
                $keyParts[$param] = $request->get($param);
            }
        }

        return 'response_cache:'.md5(serialize($keyParts));
    }

    /**
     * Cache the response data.
     */
    protected function cacheResponse(Request $request, $response, string $cacheKey): void
    {
        $duration = $this->getCacheDuration($request);

        if ($duration <= 0) {
            return;
        }

        $cacheData = [
            'content' => $response->getContent(),
            'content_type' => $response->headers->get('Content-Type'),
            'cached_at' => now(),
            'status_code' => $response->getStatusCode(),
        ];

        Cache::put($cacheKey, $cacheData, $duration);
    }

    /**
     * Get cache duration for request.
     */
    protected function getCacheDuration(Request $request): int
    {
        $routeName = $request->route()->getName();

        // Check exact matches first
        if (isset($this->cacheDurations[$routeName])) {
            return $this->cacheDurations[$routeName];
        }

        // Check pattern matches
        foreach ($this->cacheDurations as $pattern => $duration) {
            if ($request->is($pattern)) {
                return $duration;
            }
        }

        // Default cache duration
        return config('cache.default_duration', 300); // 5 minutes
    }

    /**
     * Check if current user has write permissions.
     */
    protected function hasWritePermissions(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Check for write-related permissions
        $writePermissions = [
            'create_records',
            'edit_records',
            'delete_records',
            'manage_users',
            'manage_organization',
        ];

        foreach ($writePermissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear cache for specific patterns.
     */
    public static function clearCachePattern(string $pattern): int
    {
        $cleared = 0;

        // This would need to be implemented based on cache driver
        // For Redis: Cache::getRedis()->del(Cache::getRedis()->keys($pattern));
        // For file: glob and unlink

        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);

                if (! empty($keys)) {
                    $cleared = $redis->del($keys);
                }
            } else {
                // For other cache drivers, implement as needed
                $cleared = Cache::flush(); // Fallback
            }
        } catch (\Exception $e) {
            logger()->error('Cache clear error: '.$e->getMessage());
        }

        return $cleared;
    }

    /**
     * Warm up cache for frequently accessed data.
     */
    public static function warmupCache(): void
    {
        // Pre-cache common data
        $warmupData = [
            'organizations_list' => function () {
                return \App\Models\Organization::active()->get(['id', 'name']);
            },
            'user_permissions' => function () {
                if (auth()->check()) {
                    return auth()->user()->getAllPermissions();
                }

                return [];
            },
            'chart_of_accounts' => function () {
                if (auth()->check()) {
                    return \App\Models\Accounting\ChartOfAccount::where('organization_id', auth()->user()->current_organization_id)
                        ->get(['id', 'code', 'name', 'type']);
                }

                return collect();
            },
        ];

        foreach ($warmupData as $key => $callback) {
            try {
                Cache::remember($key, 3600, $callback); // Cache for 1 hour
            } catch (\Exception $e) {
                logger()->error("Cache warmup error for {$key}: ".$e->getMessage());
            }
        }
    }
}
