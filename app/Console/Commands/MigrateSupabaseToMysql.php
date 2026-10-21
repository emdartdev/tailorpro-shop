<?php

namespace App\Console\Commands;

use App\Services\DataMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateSupabaseToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tailorpro:migrate-supabase
                            {--table= : Specific table to migrate}
                            {--chunk=250 : Number of records per chunk}
                            {--dry-run : Validate and simulate transformations without writing to MySQL}
                            {--resume : Resume from last recorded checkpoint}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingest data from Supabase PostgreSQL into MySQL in transactional chunks with resumability';

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

    /**
     * Execute the console command.
     */
    public function handle(DataMigrationService $migrationService): int
    {
        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');
        $specificTable = $this->option('table');
        $resume = $this->option('resume');

        $this->info('================================================================');
        $this->info(' TAILORPRO SUPABASE ➔ MYSQL DATA INGESTION ENGINE');
        $this->info('================================================================');

        if ($dryRun) {
            $this->warn('DRY-RUN MODE ENABLED: No changes will be written to MySQL.');
        }

        $tables = $specificTable ? [$specificTable] : $this->migrationOrder;

        foreach ($tables as $tableName) {
            $this->processTable($tableName, $migrationService, $chunkSize, $dryRun, $resume);
        }

        $this->newLine();
        $this->info('All operations completed.');

        return Command::SUCCESS;
    }

    protected function processTable(string $table, DataMigrationService $service, int $chunkSize, bool $dryRun, bool $resume): void
    {
        $this->line("• Processing table: <fg=cyan>{$table}</>");

        $checkpoint = $service->getCheckpoint($table);

        if ($resume && $checkpoint->status === 'completed') {
            $this->comment("  ↳ Skipped: Table '{$table}' already completed at checkpoint.");

            return;
        }

        try {
            $service->updateCheckpoint($table, ['status' => 'in_progress', 'started_at' => now()]);

            // Safe Read-only count from source
            $totalCount = 0;
            $hasDirectSource = false;

            try {
                $totalCount = DB::connection('supabase_source')->table($table)->count();
                $hasDirectSource = true;
            } catch (\Throwable $e) {
                // Fallback to local backup json if available
                $backupFile = base_path("../backup/database/{$table}.json");
                if (file_exists($backupFile)) {
                    $jsonContent = json_decode(file_get_contents($backupFile), true) ?? [];
                    $totalCount = count($jsonContent);
                }
            }

            $this->line("  ↳ Total records discovered in source: <fg=yellow>{$totalCount}</>");

            if ($totalCount === 0) {
                $service->updateCheckpoint($table, [
                    'status' => 'completed',
                    'total_rows_source' => 0,
                    'migrated_rows_target' => 0,
                    'completed_at' => now(),
                ]);
                $this->info("  ↳ Table '{$table}' contains 0 records. Checkpoint saved.");

                return;
            }

            $bar = $this->output->createProgressBar($totalCount);
            $bar->start();

            $migratedCount = 0;

            if ($hasDirectSource) {
                DB::connection('supabase_source')->table($table)->orderBy('id')->chunk($chunkSize, function ($rows) use ($table, $service, $dryRun, &$migratedCount, $bar) {
                    $transformedRows = [];
                    foreach ($rows as $row) {
                        $transformedRows[] = $service->transformRow($table, (array) $row);
                    }

                    if (! $dryRun) {
                        DB::connection('mysql_target')->transaction(function () use ($table, $transformedRows) {
                            DB::connection('mysql_target')->table($table)->upsert($transformedRows, ['id']);
                        });
                    }

                    $migratedCount += count($transformedRows);
                    $bar->advance(count($transformedRows));
                });
            } else {
                $backupFile = base_path("../backup/database/{$table}.json");
                if (file_exists($backupFile)) {
                    $jsonContent = json_decode(file_get_contents($backupFile), true) ?? [];
                    foreach (array_chunk($jsonContent, $chunkSize) as $chunk) {
                        $transformedRows = [];
                        foreach ($chunk as $row) {
                            $transformedRows[] = $service->transformRow($table, (array) $row);
                        }

                        if (! $dryRun) {
                            DB::connection('mysql_target')->transaction(function () use ($table, $transformedRows) {
                                DB::connection('mysql_target')->table($table)->upsert($transformedRows, ['id']);
                            });
                        }

                        $migratedCount += count($transformedRows);
                        $bar->advance(count($transformedRows));
                    }
                }
            }

            $bar->finish();
            $this->newLine();

            $service->updateCheckpoint($table, [
                'status' => 'completed',
                'total_rows_source' => $totalCount,
                'migrated_rows_target' => $migratedCount,
                'completed_at' => now(),
            ]);

            $this->info("  ↳ Successfully migrated {$migratedCount} rows for '{$table}'.");

        } catch (\Throwable $e) {
            $service->updateCheckpoint($table, [
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);
            $this->error("  ↳ Error during migration of '{$table}': ".$e->getMessage());
            Log::error("Migration failed for {$table}", ['exception' => $e]);
        }
    }
}
