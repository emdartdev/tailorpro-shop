<?php

namespace App\Console\Commands;

use App\Services\Migration\MigrationManager;
use Illuminate\Console\Command;

class MigrationStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the real-time migration status and row counts across all tables';

    /**
     * Execute the console command.
     */
    public function handle(MigrationManager $manager): int
    {
        $this->info('================================================================');
        $this->info(' TAILORPRO POSTGRESQL ➔ MYSQL MIGRATION STATUS MATRIX');
        $this->info('================================================================');
        $this->newLine();

        $statusList = $manager->getStatus();
        $tableRows = [];

        foreach ($statusList as $row) {
            $statusFormatted = match ($row['status']) {
                'COMPLETED' => '<fg=green;options=bold>COMPLETED</>',
                'IN_PROGRESS' => '<fg=yellow;options=bold>IN PROGRESS</>',
                'FAILED' => '<fg=red;options=bold>FAILED</>',
                default => '<fg=gray>PENDING</>',
            };

            $tableRows[] = [
                $row['order'],
                $row['table'],
                $statusFormatted,
                $row['source_rows'],
                $row['migrated_rows'],
                $row['completed_at'],
                $row['last_error'] ?? '-',
            ];
        }

        $this->table(
            ['#', 'Table Name', 'Status', 'Source Rows', 'Migrated Rows', 'Completed At', 'Last Error'],
            $tableRows
        );

        $this->newLine();

        return Command::SUCCESS;
    }
}
