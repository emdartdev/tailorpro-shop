<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;

class MigrationVerifier
{
    /**
     * Verify migration integrity for a single table.
     *
     * @return array{table: string, status: string, source_count: int, target_count: int, missing_ids: array, duplicate_ids: array, issues: array}
     */
    public function verifyTable(string $tableName, array $sourceRows = []): array
    {
        $report = [
            'table' => $tableName,
            'status' => 'PASS',
            'source_count' => count($sourceRows),
            'target_count' => 0,
            'missing_ids' => [],
            'duplicate_ids' => [],
            'issues' => [],
        ];

        // 1. Target Row Count
        try {
            $targetQuery = DB::connection('mysql_target')->table($tableName);
            $targetCount = $targetQuery->count();
            $report['target_count'] = $targetCount;
        } catch (\Throwable $e) {
            // Fallback for default testing connection
            $targetCount = DB::table($tableName)->count();
            $report['target_count'] = $targetCount;
        }

        // 2. Row Count Parity
        if ($report['source_count'] !== $report['target_count']) {
            $report['status'] = 'FAIL';
            $report['issues'][] = "Row count mismatch: Source has {$report['source_count']}, Target has {$report['target_count']}.";
        }

        // 3. ID Discrepancy & Duplicate Check
        if (! empty($sourceRows)) {
            $sourceIds = array_map(fn ($r) => strtolower((string) ($r['id'] ?? '')), $sourceRows);
            $sourceIdSet = array_flip($sourceIds);

            try {
                $targetIds = DB::connection('mysql_target')->table($tableName)->pluck('id')->map(fn ($id) => strtolower((string) $id))->toArray();
            } catch (\Throwable $e) {
                $targetIds = DB::table($tableName)->pluck('id')->map(fn ($id) => strtolower((string) $id))->toArray();
            }

            // Check duplicates in Target
            $counts = array_count_values($targetIds);
            foreach ($counts as $id => $cnt) {
                if ($cnt > 1) {
                    $report['duplicate_ids'][] = $id;
                    $report['status'] = 'FAIL';
                    $report['issues'][] = "Duplicate ID detected in target: {$id} (occurrences: {$cnt})";
                }
            }

            // Check missing IDs from Source
            $targetIdSet = array_flip($targetIds);
            foreach ($sourceIds as $sId) {
                if (! isset($targetIdSet[$sId])) {
                    $report['missing_ids'][] = $sId;
                    $report['status'] = 'FAIL';
                    $report['issues'][] = "Missing ID in target: {$sId}";
                }
            }
        }

        return $report;
    }
}
