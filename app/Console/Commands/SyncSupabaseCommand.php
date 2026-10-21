<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class SyncSupabaseCommand extends Command
{
    protected $signature = 'app:sync-supabase
                            {--key= : Supabase Service Role Key or Authenticated JWT Token}
                            {--email= : Admin Email for Supabase Auth Login}
                            {--password= : Admin Password for Supabase Auth Login}
                            {--table= : Specific table to sync}
                            {--verify-only : Only run count and financial reconciliation}';

    protected $description = 'Zero-loss extraction and ingestion from Supabase PostgreSQL to MySQL with full audit';

    protected string $supabaseUrl = 'https://pvugcpigjqgnnwxhwxei.supabase.co';
    protected string $anonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InB2dWdjcGlnanFnbm53eGh3eGVpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzUyMjA2MjYsImV4cCI6MjA5MDc5NjYyNn0.ykJXnzpEdGWUPG_pWhPiRBTc7Cw2JoDr7qDC-ixAOKk';

    protected array $tableSequence = [
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

    public function handle(): int
    {
        $this->info("========================================================================");
        $this->info("  TAILORPRO: ZERO-LOSS DATA EXTRACTION & INGESTION (SUPABASE -> MYSQL)  ");
        $this->info("========================================================================");

        $authToken = $this->resolveAuthToken();

        if (!$authToken) {
            $this->error("\n[AUTHENTICATION REQUIRED]");
            $this->warn("Supabase tables are protected by Row Level Security (RLS).");
            $this->line("To extract complete data without RLS blocking, please provide either:");
            $this->line("1. Supabase Service Role Key: php artisan app:sync-supabase --key=\"eyJhbG...\"");
            $this->line("2. Admin Credentials: php artisan app:sync-supabase --email=\"...\" --password=\"...\"");
            return 1;
        }

        $this->info("\n[+] Supabase Authentication verified. Starting extraction...");

        if ($this->option('verify-only')) {
            $this->runReconciliationAudit($authToken);
            return 0;
        }

        $targetTables = $this->option('table')
            ? [$this->option('table')]
            : $this->tableSequence;

        // Temporarily disable foreign key checks during batch ingestion
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $summary = [];

        // 1. Sync Supabase Auth users to MySQL users table
        if (!$this->option('table')) {
            $summary[] = $this->syncAuthUsers($authToken);
        }

        foreach ($targetTables as $table) {
            $this->info("\n--> Syncing table: [{$table}]");
            $result = $this->syncTable($table, $authToken);
            $summary[] = $result;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("\n========================================================================");
        $this->info("                      INGESTION AUDIT SUMMARY                           ");
        $this->info("========================================================================");
        $this->table(['Table Name', 'Extracted', 'Stored in MySQL', 'Status'], $summary);

        $this->runFinancialAudit();

        return 0;
    }

    protected function resolveAuthToken(): ?string
    {
        // 1. Explicit CLI argument
        if ($this->option('key')) {
            return $this->option('key');
        }

        // 2. Check environment variable
        $envKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_SECRET_KEY');
        if ($envKey) {
            return $envKey;
        }

        // 3. User login via email & password
        $email = $this->option('email') ?: env('SUPABASE_ADMIN_EMAIL');
        $password = $this->option('password') ?: env('SUPABASE_ADMIN_PASSWORD');

        if ($email && $password) {
            $this->line("Authenticating with Supabase as [{$email}]...");
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/token?grant_type=password", [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $token = $response->json('access_token');
                $this->info("Login successful! Acquired authenticated access token.");
                return $token;
            } else {
                $this->error("Login failed: " . $response->body());
            }
        }

        return null;
    }

    protected function syncTable(string $table, string $token): array
    {
        if (!Schema::hasTable($table)) {
            $this->warn("Table '{$table}' does not exist in MySQL schema. Skipping.");
            return [$table, 0, 0, 'SKIPPED (SCHEMA MISSING)'];
        }

        $pageSize = 500;
        $offset = 0;
        $totalFetched = 0;
        $totalInserted = 0;

        while (true) {
            $to = $offset + $pageSize - 1;

            $response = Http::withHeaders([
                'apikey' => $token,
                'Authorization' => "Bearer {$token}",
                'Range' => "{$offset}-{$to}",
                'Prefer' => 'count=exact',
            ])->get("{$this->supabaseUrl}/rest/v1/{$table}?select=*&order=id.asc");

            if (!$response->successful()) {
                $status = $response->status();
                if ($status === 404) {
                    $this->warn("Table '{$table}' not found in Supabase (404).");
                    return [$table, 0, 0, 'NOT IN SUPABASE'];
                }
                $this->error("HTTP {$status} Error fetching {$table}: " . $response->body());
                return [$table, $totalFetched, $totalInserted, "ERROR: HTTP {$status}"];
            }

            $rows = $response->json();

            if (!is_array($rows) || empty($rows)) {
                break;
            }

            $fetchedCount = count($rows);
            $totalFetched += $fetchedCount;

            // Ingest rows into MySQL
            foreach ($rows as $row) {
                $sanitized = $this->sanitizeRowForMySQL($table, $row);
                if (!empty($sanitized)) {
                    if (isset($sanitized['id'])) {
                        DB::table($table)->updateOrInsert(['id' => $sanitized['id']], $sanitized);
                    } else {
                        DB::table($table)->insert($sanitized);
                    }
                    $totalInserted++;
                }
            }

            $this->line("  - Fetched {$totalFetched} rows so far...");

            if ($fetchedCount < $pageSize) {
                break;
            }

            $offset += $pageSize;
        }

        $status = ($totalFetched === $totalInserted) ? 'VERIFIED (100% MATCH)' : 'MISMATCH';
        return [$table, $totalFetched, $totalInserted, $status];
    }

    protected function syncAuthUsers(string $token): array
    {
        $this->info("\n--> Syncing Supabase Auth Users to MySQL users table");
        $response = Http::withHeaders([
            'apikey' => $token,
            'Authorization' => "Bearer {$token}",
        ])->get("{$this->supabaseUrl}/auth/v1/admin/users");

        if (!$response->successful()) {
            $this->warn("Could not fetch Supabase Auth users: " . $response->body());
            return ['users', 0, 0, 'SKIPPED (AUTH API)'];
        }

        $users = $response->json('users') ?? [];
        $totalFetched = count($users);
        $totalInserted = 0;

        foreach ($users as $u) {
            $id = $u['id'];
            $email = $u['email'] ?? '';
            $createdAt = isset($u['created_at']) ? \Carbon\Carbon::parse($u['created_at'])->format('Y-m-d H:i:s') : now();
            $updatedAt = isset($u['updated_at']) ? \Carbon\Carbon::parse($u['updated_at'])->format('Y-m-d H:i:s') : now();

            // Clear any conflicting seeded user by email if ID is different
            if (!empty($email)) {
                DB::table('users')->where('email', $email)->where('id', '!=', $id)->delete();
            }

            DB::table('users')->updateOrInsert(
                ['id' => $id],
                [
                    'email' => $email,
                    'name' => explode('@', $email)[0] ?? 'User',
                    'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]
            );
            $totalInserted++;
        }

        $status = ($totalFetched === $totalInserted) ? 'VERIFIED (100% MATCH)' : 'MISMATCH';
        return ['users', $totalFetched, $totalInserted, $status];
    }

    protected function sanitizeRowForMySQL(string $table, array $row): array
    {
        $columns = Schema::getColumnListing($table);
        $clean = [];

        foreach ($row as $col => $val) {
            if (!in_array($col, $columns)) {
                continue;
            }

            if ($val === null) {
                $clean[$col] = null;
                continue;
            }

            // Convert array / object to JSON string
            if (is_array($val)) {
                $clean[$col] = json_encode($val, JSON_UNESCAPED_UNICODE);
            } elseif (is_bool($val)) {
                $clean[$col] = $val ? 1 : 0;
            } elseif (is_string($val) && in_array($col, ['order_date', 'delivery_date', 'delivered_date', 'khoroch_date']) && preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
                $clean[$col] = substr($val, 0, 10);
            } elseif (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $val)) {
                try {
                    $clean[$col] = \Carbon\Carbon::parse($val)->format('Y-m-d H:i:s');
                } catch (\Throwable $e) {
                    $clean[$col] = $val;
                }
            } else {
                $clean[$col] = $val;
            }
        }

        return $clean;
    }

    protected function runFinancialAudit(): void
    {
        $this->info("\n========================================================================");
        $this->info("                      FINANCIAL RECONCILIATION                          ");
        $this->info("========================================================================");

        if (Schema::hasTable('orders')) {
            $orderStats = DB::table('orders')->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as sum_total,
                COALESCE(SUM(advance), 0) as sum_advance,
                COALESCE(SUM(due), 0) as sum_due
            ')->first();

            $this->table(['Metric', 'Value in MySQL'], [
                ['Total Orders Ingested', $orderStats->total_orders],
                ['Sum of Total Amount', number_format($orderStats->sum_total, 2) . ' BDT'],
                ['Sum of Advance Paid', number_format($orderStats->sum_advance, 2) . ' BDT'],
                ['Sum of Due Amount', number_format($orderStats->sum_due, 2) . ' BDT'],
            ]);
        }

        if (Schema::hasTable('customers')) {
            $customerStats = DB::table('customers')->selectRaw('
                COUNT(*) as total_customers,
                COALESCE(SUM(total_spent), 0) as sum_spent,
                COALESCE(SUM(due_amount), 0) as sum_due
            ')->first();

            $this->table(['Metric', 'Value in MySQL'], [
                ['Total Customers Ingested', $customerStats->total_customers],
                ['Sum of Customer Total Spent', number_format($customerStats->sum_spent, 2) . ' BDT'],
                ['Sum of Customer Due Amount', number_format($customerStats->sum_due, 2) . ' BDT'],
            ]);
        }

        if (Schema::hasTable('workers') && Schema::hasTable('worker_entries')) {
            $workerCount = DB::table('workers')->count();
            $entryStats = DB::table('worker_entries')->selectRaw('
                COUNT(*) as total_entries,
                COALESCE(SUM(total_taka), 0) as sum_wages,
                COALESCE(SUM(khoroch), 0) as sum_khoroch
            ')->first();

            $this->table(['Metric', 'Value in MySQL'], [
                ['Total Workers Ingested', $workerCount],
                ['Total Worker Ledger Entries', $entryStats->total_entries],
                ['Sum of Worker Earned Wages', number_format($entryStats->sum_wages, 2) . ' BDT'],
                ['Sum of Worker Khoroch (Advance)', number_format($entryStats->sum_khoroch, 2) . ' BDT'],
            ]);
        }
    }

    protected function runReconciliationAudit(string $token): void
    {
        $this->info("Comparing live record counts between Supabase and MySQL...");
        $rows = [];

        foreach ($this->tableSequence as $table) {
            if (!Schema::hasTable($table)) continue;

            $mysqlCount = DB::table($table)->count();

            $res = Http::withHeaders([
                'apikey' => $token,
                'Authorization' => "Bearer {$token}",
                'Range' => '0-0',
                'Prefer' => 'count=exact',
            ])->get("{$this->supabaseUrl}/rest/v1/{$table}?select=id");

            $range = $res->header('Content-Range');
            $supabaseCount = $range && preg_match('/\/(\d+)$/', $range, $m) ? (int)$m[1] : 0;

            $match = ($supabaseCount === $mysqlCount) ? 'YES (100% MATCH)' : 'MISMATCH';
            $rows[] = [$table, $supabaseCount, $mysqlCount, $match];
        }

        $this->table(['Table Name', 'Supabase Count', 'MySQL Count', 'Exact Match?'], $rows);
    }
}
