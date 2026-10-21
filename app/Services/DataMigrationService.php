<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DataMigrationService
{
    /**
     * Test connection health for both Source (Supabase PostgreSQL) and Target (MySQL).
     *
     * @return array<string, array{status: string, message: string, latency_ms: float|null}>
     */
    public function testConnections(): array
    {
        $results = [
            'source' => [
                'label' => 'SOURCE: Supabase PostgreSQL (STRICT READ-ONLY)',
                'status' => 'PENDING',
                'message' => '',
                'latency_ms' => null,
            ],
            'target' => [
                'label' => 'TARGET: MySQL (PRODUCTION APPLICATION DB)',
                'status' => 'PENDING',
                'message' => '',
                'latency_ms' => null,
            ],
        ];

        // 1. Check Source Database (Read-Only)
        try {
            $start = microtime(true);
            $sourceConn = DB::connection('supabase_source');
            $sourceConn->select('SELECT 1 AS health_check');
            $duration = round((microtime(true) - $start) * 1000, 2);

            $results['source']['status'] = 'CONNECTED';
            $results['source']['message'] = 'Successfully connected to Supabase PostgreSQL in read-only mode.';
            $results['source']['latency_ms'] = $duration;
        } catch (\Throwable $e) {
            $results['source']['status'] = 'UNAVAILABLE';
            $results['source']['message'] = 'Direct PostgreSQL connection error: '.$e->getMessage().' (Note: PostgREST REST / JSON fallback active).';
        }

        // 2. Check Target Database
        try {
            $start = microtime(true);
            $targetConn = DB::connection('mysql_target');
            $targetConn->select('SELECT 1 AS health_check');
            $duration = round((microtime(true) - $start) * 1000, 2);

            $results['target']['status'] = 'CONNECTED';
            $results['target']['message'] = 'Successfully connected to MySQL target database.';
            $results['target']['latency_ms'] = $duration;
        } catch (\Throwable $e) {
            $results['target']['status'] = 'UNAVAILABLE';
            $results['target']['message'] = 'Target MySQL connection error: '.$e->getMessage();
        }

        return $results;
    }

    /**
     * Transform a source row into target MySQL format.
     */
    public function transformRow(string $table, array $row): array
    {
        $transformed = [];

        foreach ($row as $col => $val) {
            // Handle nulls
            if ($val === null) {
                $transformed[$col] = null;

                continue;
            }

            // Handle JSON/Structured data
            if (in_array($col, ['measurements', 'downloaded_categories'])) {
                if (is_array($val)) {
                    $transformed[$col] = json_encode($val, JSON_UNESCAPED_UNICODE);
                } elseif (is_string($val)) {
                    // validate json
                    json_decode($val);
                    $transformed[$col] = (json_last_error() === JSON_ERROR_NONE) ? $val : json_encode($val);
                } else {
                    $transformed[$col] = json_encode($val);
                }

                continue;
            }

            // Handle Booleans (PostgreSQL true/false -> MySQL 1/0)
            if (is_bool($val)) {
                $transformed[$col] = $val ? 1 : 0;

                continue;
            }

            // Handle Timestamps
            if (in_array($col, ['created_at', 'updated_at', 'last_used_at', 'delivered_date', 'order_date', 'delivery_date', 'entry_date', 'khoroch_date', 'payment_date'])) {
                if (is_string($val) && str_contains($val, 'T')) {
                    $transformed[$col] = date('Y-m-d H:i:s', strtotime($val));
                } else {
                    $transformed[$col] = $val;
                }

                continue;
            }

            $transformed[$col] = $val;
        }

        return $transformed;
    }

    /**
     * Get or create a checkpoint record for a table.
     */
    public function getCheckpoint(string $table): object
    {
        $checkpoint = DB::table('migration_checkpoints')->where('table_name', $table)->first();

        if (! $checkpoint) {
            $id = DB::table('migration_checkpoints')->insertGetId([
                'table_name' => $table,
                'status' => 'pending',
                'total_rows_source' => 0,
                'migrated_rows_target' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('migration_checkpoints')->where('id', $id)->first();
        }

        return $checkpoint;
    }

    /**
     * Update checkpoint progress.
     */
    public function updateCheckpoint(string $table, array $data): void
    {
        $data['updated_at'] = now();
        DB::table('migration_checkpoints')->where('table_name', $table)->update($data);
    }
}
