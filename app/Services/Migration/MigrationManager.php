<?php

namespace App\Services\Migration;

class MigrationManager
{
    protected array $migrationOrder = [
        'running_years',
        'item_catalog',
        'airman_item_catalog',
        'worker_item_catalog',
        'workers',
        'customers',
        'orders',
        'order_items',
        'payment_history',
        'worker_entries',
        'staff_payments',
        'profiles',
        'user_roles',
    ];

    public function __construct(
        protected TableMigrator $tableMigrator,
        protected MigrationCheckpoint $checkpoint,
        protected MigrationVerifier $verifier,
        protected MigrationLogger $logger
    ) {}

    public function getMigrationOrder(): array
    {
        return $this->migrationOrder;
    }

    /**
     * Run migration across all tables or a specific table.
     */
    public function run(
        ?string $table = null,
        int $chunkSize = 250,
        bool $dryRun = false,
        bool $resume = false,
        ?callable $progressCallback = null
    ): array {
        $tables = $table ? [$table] : $this->migrationOrder;
        $results = [];

        $this->logger->info('Starting MigrationManager execution', [
            'tables_count' => count($tables),
            'dry_run' => $dryRun,
            'resume' => $resume,
        ]);

        foreach ($tables as $tableName) {
            $result = $this->tableMigrator->migrate(
                $tableName,
                $chunkSize,
                $dryRun,
                $resume,
                3,
                $progressCallback
            );

            $results[$tableName] = $result;
        }

        return $results;
    }

    /**
     * Get migration status matrix for all tables.
     */
    public function getStatus(): array
    {
        $statusList = [];
        $checkpoints = $this->checkpoint->all();
        $cpMap = [];

        foreach ($checkpoints as $cp) {
            $cpMap[$cp->table_name] = $cp;
        }

        foreach ($this->migrationOrder as $index => $tableName) {
            $cp = $cpMap[$tableName] ?? $this->checkpoint->get($tableName);
            $sourceRows = $this->tableMigrator->fetchSourceRecords($tableName);

            $statusList[] = [
                'order' => $index + 1,
                'table' => $tableName,
                'status' => strtoupper($cp->status),
                'source_rows' => count($sourceRows),
                'migrated_rows' => $cp->migrated_rows_target,
                'started_at' => $cp->started_at ?? 'N/A',
                'completed_at' => $cp->completed_at ?? 'N/A',
                'last_error' => $cp->last_error,
            ];
        }

        return $statusList;
    }

    /**
     * Verify data integrity across all tables.
     */
    public function verify(): array
    {
        $verificationReports = [];

        foreach ($this->migrationOrder as $tableName) {
            $sourceRows = $this->tableMigrator->fetchSourceRecords($tableName);
            $report = $this->verifier->verifyTable($tableName, $sourceRows);
            $verificationReports[$tableName] = $report;
        }

        return $verificationReports;
    }
}
