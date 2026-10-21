<?php

namespace App\Console\Commands;

use App\Services\Migration\MigrationManager;
use Illuminate\Console\Command;

class MigrationRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:run
                            {--table= : Migrate a specific table only}
                            {--chunk=250 : Chunk size per batch transaction}
                            {--dry-run : Validate and simulate transformations without writing to MySQL}
                            {--resume : Resume from last recorded checkpoint}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute production-safe data migration from PostgreSQL to MySQL in chunked transactions';

    /**
     * Execute the console command.
     */
    public function handle(MigrationManager $manager): int
    {
        $table = $this->option('table');
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');
        $resume = (bool) $this->option('resume');

        $this->info('================================================================');
        $this->info(' TAILORPRO POSTGRESQL ➔ MYSQL MIGRATION ENGINE');
        $this->info('================================================================');

        if ($dryRun) {
            $this->warn(' [DRY-RUN MODE ACTIVE] Simulating data transformations with ZERO writes to MySQL.');
        }

        $this->line(" • Chunk Size: {$chunkSize}");
        $this->line(' • Resume Mode: '.($resume ? 'ENABLED' : 'DISABLED'));
        if ($table) {
            $this->line(" • Target Filter: Table '{$table}'");
        }
        $this->newLine();

        $bar = null;

        $results = $manager->run(
            $table,
            $chunkSize,
            $dryRun,
            $resume,
            function (int $current, int $total, string $message) use (&$bar) {
                if ($bar === null) {
                    $bar = $this->output->createProgressBar($total);
                    $bar->start();
                }
                $bar->setProgress($current);
            }
        );

        if ($bar !== null) {
            $bar->finish();
            $this->newLine();
        }

        $summaryRows = [];
        foreach ($results as $tbl => $res) {
            $statusFormatted = match ($res['status']) {
                'COMPLETED' => '<fg=green;options=bold>COMPLETED</>',
                'SKIPPED' => '<fg=yellow>SKIPPED</>',
                'FAILED' => '<fg=red;options=bold>FAILED</>',
                default => $res['status'],
            };

            $summaryRows[] = [
                $tbl,
                $statusFormatted,
                $res['source_count'] ?? 0,
                $res['migrated_count'] ?? 0,
                $res['checksum'] ?? '-',
            ];
        }

        $this->newLine();
        $this->table(['Table Name', 'Result', 'Source Rows', 'Migrated Rows', 'Checksum'], $summaryRows);
        $this->newLine();
        $this->info('Migration batch execution completed.');

        return Command::SUCCESS;
    }
}
