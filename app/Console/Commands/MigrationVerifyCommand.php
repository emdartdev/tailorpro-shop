<?php

namespace App\Console\Commands;

use App\Services\Migration\MigrationManager;
use Illuminate\Console\Command;

class MigrationVerifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform exhaustive three-way verification comparing Source, Checksum, and Target MySQL rows';

    /**
     * Execute the console command.
     */
    public function handle(MigrationManager $manager): int
    {
        $this->info('================================================================');
        $this->info(' TAILORPRO POSTGRESQL ➔ MYSQL MIGRATION VERIFICATION AUDIT');
        $this->info('================================================================');
        $this->newLine();

        $reports = $manager->verify();
        $tableRows = [];
        $hasFailures = false;

        foreach ($reports as $tbl => $rep) {
            $isPass = $rep['status'] === 'PASS';
            if (! $isPass) {
                $hasFailures = true;
            }

            $statusFormatted = $isPass
                ? '<fg=green;options=bold>PASS</>'
                : '<fg=red;options=bold>FAIL</>';

            $issueSummary = empty($rep['issues']) ? '100% Parity' : implode('; ', $rep['issues']);

            $tableRows[] = [
                $tbl,
                $statusFormatted,
                $rep['source_count'],
                $rep['target_count'],
                count($rep['missing_ids']),
                count($rep['duplicate_ids']),
                $issueSummary,
            ];
        }

        $this->table(
            ['Table Name', 'Status', 'Source Rows', 'Target Rows', 'Missing IDs', 'Duplicate IDs', 'Parity Notes'],
            $tableRows
        );

        $this->newLine();
        if ($hasFailures) {
            $this->warn('Verification identified discrepancies. Inspect logs or run with --resume.');

            return Command::FAILURE;
        }

        $this->info('All tables verified with 100% cryptographic and row count parity.');

        return Command::SUCCESS;
    }
}
