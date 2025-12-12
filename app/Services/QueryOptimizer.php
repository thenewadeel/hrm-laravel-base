<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Query Optimization Service
 *
 * Provides optimized query patterns and caching strategies
 * for high-performance database operations.
 */
class QueryOptimizer
{
    /**
     * Optimized paginated query with eager loading and caching.
     */
    public static function optimizedPaginate(
        Builder $query,
        int $perPage = 15,
        array $relations = [],
        int $cacheMinutes = 5,
        ?string $cacheKey = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {

        // Apply eager loading
        if (! empty($relations)) {
            $query->with($relations);
        }

        // Use caching for frequently accessed data
        if ($cacheKey && $cacheMinutes > 0) {
            return Cache::remember($cacheKey, $cacheMinutes, function () use ($query, $perPage) {
                return $query->paginate($perPage);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Chunked processing for large datasets with memory efficiency.
     */
    public static function chunkedProcess(
        Builder $query,
        callable $processor,
        int $chunkSize = 500,
        ?callable $progressCallback = null
    ): int {

        $processedCount = 0;

        $query->chunk($chunkSize, function ($chunk) use ($processor, &$processedCount, $progressCallback) {
            $chunk->each($processor);
            $processedCount += $chunk->count();

            if ($progressCallback) {
                $progressCallback($processedCount, $chunk->count());
            }
        });

        return $processedCount;
    }

    /**
     * Optimized search with multiple conditions.
     */
    public static function optimizedSearch(
        Builder $query,
        string $searchTerm,
        array $searchableFields = ['name'],
        string $mode = 'contains'
    ): Builder {

        if (empty($searchTerm)) {
            return $query;
        }

        $searchTerm = trim($searchTerm);

        return $query->where(function ($q) use ($searchTerm, $searchableFields, $mode) {
            foreach ($searchableFields as $field) {
                if ($mode === 'contains') {
                    $q->orWhere($field, 'like', "%{$searchTerm}%");
                } elseif ($mode === 'exact') {
                    $q->orWhere($field, '=', $searchTerm);
                } elseif ($mode === 'starts_with') {
                    $q->orWhere($field, 'like', "{$searchTerm}%");
                }
            }
        });
    }

    /**
     * Cached count query for large tables.
     */
    public static function cachedCount(
        Builder $query,
        int $cacheMinutes = 10,
        ?string $cacheKey = null
    ): int {

        $cacheKey = $cacheKey ?: 'count_'.md5($query->toSql().serialize($query->getBindings()));

        return Cache::remember($cacheKey, $cacheMinutes, function () use ($query) {
            return $query->count();
        });
    }

    /**
     * Optimized relationship loading to prevent N+1 queries.
     */
    public static function preventNPlusOne(
        Builder $query,
        array $relations = [],
        array $counts = []
    ): Builder {

        // Load relationships
        if (! empty($relations)) {
            $query->with($relations);
        }

        // Load relationship counts
        if (! empty($counts)) {
            foreach ($counts as $relation) {
                $query->withCount($relation);
            }
        }

        return $query;
    }

    /**
     * Efficient date range queries.
     */
    public static function dateRange(
        Builder $query,
        string $dateColumn,
        ?string $startDate = null,
        ?string $endDate = null
    ): Builder {

        if ($startDate) {
            $query->whereDate($dateColumn, '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate($dateColumn, '<=', $endDate);
        }

        return $query;
    }

    /**
     * Optimized organization-scoped queries.
     */
    public static function organizationScoped(
        Builder $query,
        ?int $organizationId = null
    ): Builder {

        if ($organizationId) {
            return $query->where('organization_id', $organizationId);
        }

        // Use current organization from context
        if (auth()->check()) {
            $orgId = auth()->user()->operatingOrganizationId;
            if ($orgId) {
                return $query->where('organization_id', $orgId);
            }
        }

        return $query;
    }

    /**
     * Batch insert/update operations.
     */
    public static function batchOperation(
        string $operation,
        string $table,
        array $data,
        array $updateColumns = []
    ): int {

        return DB::transaction(function () use ($operation, $table, $data, $updateColumns) {
            if ($operation === 'insert') {
                return DB::table($table)->insert($data);
            } elseif ($operation === 'upsert') {
                return DB::table($table)->upsert($data, ['id'], $updateColumns);
            } elseif ($operation === 'update') {
                $affected = 0;
                foreach ($data as $item) {
                    $affected += DB::table($table)
                        ->where('id', $item['id'])
                        ->update($item);
                }

                return $affected;
            }

            return 0;
        });
    }

    /**
     * Query performance analysis.
     */
    public static function analyzeQuery(Builder $query): array
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        return [
            'sql' => $sql,
            'bindings' => $bindings,
            'estimated_rows' => self::estimateRows($sql, $bindings),
            'uses_index' => self::usesIndex($sql),
            'complexity' => self::assessComplexity($sql),
        ];
    }

    /**
     * Estimate number of rows a query will return.
     */
    private static function estimateRows(string $sql, array $bindings): int
    {
        // Simple heuristic based on WHERE conditions
        $hasWhereClause = str_contains(strtolower($sql), 'where');
        $hasLimitClause = str_contains(strtolower($sql), 'limit');

        if ($hasLimitClause) {
            // Extract limit number
            preg_match('/limit\s+(\d+)/i', $sql, $matches);

            return (int) ($matches[1] ?? 1000);
        }

        if ($hasWhereClause) {
            return 100; // Estimated for filtered queries
        }

        return 1000; // Estimated for full table scans
    }

    /**
     * Check if query uses indexes effectively.
     */
    private static function usesIndex(string $sql): bool
    {
        $sqlLower = strtolower($sql);

        // Check for indexed columns in WHERE clauses
        $indexedPatterns = [
            'where organization_id',
            'where id',
            'where created_at',
            'where updated_at',
            'where status',
            'where date',
        ];

        foreach ($indexedPatterns as $pattern) {
            if (str_contains($sqlLower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Assess query complexity.
     */
    private static function assessComplexity(string $sql): string
    {
        $sqlLower = strtolower($sql);

        $joins = substr_count($sqlLower, 'join');
        $subqueries = substr_count($sqlLower, 'select') - 1;
        $unions = substr_count($sqlLower, 'union');

        if ($joins > 3 || $subqueries > 2 || $unions > 0) {
            return 'high';
        } elseif ($joins > 1 || $subqueries > 0) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Optimized report queries with caching.
     */
    public static function optimizedReport(
        Builder $query,
        array $groupBy = [],
        array $aggregates = [],
        int $cacheMinutes = 30
    ): Collection {

        $cacheKey = 'report_'.md5($query->toSql().serialize($query->getBindings()).serialize($groupBy).serialize($aggregates));

        return Cache::remember($cacheKey, $cacheMinutes, function () use ($query, $groupBy, $aggregates) {

            // Apply group by
            foreach ($groupBy as $field) {
                $query->groupBy($field);
            }

            // Apply aggregates
            foreach ($aggregates as $aggregate) {
                [$function, $field] = explode(':', $aggregate);
                $query->selectRaw("{$function}({$field}) as {$function}_{$field}");
            }

            return $query->get();
        });
    }
}
