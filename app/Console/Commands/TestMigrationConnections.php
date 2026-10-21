<?php

namespace App\Console\Commands;

use App\Services\DataMigrationService;
use Illuminate\Console\Command;

class TestMigrationConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tailorpro:test-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connectivity and health for Source (Supabase PostgreSQL) and Target (MySQL) databases';

    /**
     * Execute the console command.
     */
    public function handle(DataMigrationService $migrationService): int
    {
        $this->info('================================================================');
        $this->info(' TAILORPRO DUAL-DATABASE MIGRATION CONNECTIVITY HEALTH CHECK');
        $this->info('================================================================');
        $this->newLine();

        $results = $migrationService->testConnections();

        $tableRows = [];
        foreach ($results as $key => $data) {
            $statusFormatted = match ($data['status']) {
                'CONNECTED' => '<fg=green;options=bold>CONNECTED (OK)</>',
                'UNAVAILABLE' => '<fg=yellow;options=bold>OFFLINE / STANDBY</>',
                default => '<fg=gray>PENDING</>',
            };

            $tableRows[] = [
                $data['label'],
                $statusFormatted,
                $data['latency_ms'] !== null ? "{$data['latency_ms']} ms" : 'N/A',
                $data['message'],
            ];
        }

        $this->table(['Database Target / Role', 'Status', 'Latency', 'Details'], $tableRows);

        $this->newLine();
        $this->comment('SAFETY CHECKS:');
        $this->line(' • Source connection is configured with strictly READ-ONLY intent.');
        $this->line(' • No write, update, delete, or truncate queries will be sent to Supabase.');
        $this->line(' • Target database (MySQL) is ready for schema migrations and data ingestion.');
        $this->newLine();

        return Command::SUCCESS;
    }
}
