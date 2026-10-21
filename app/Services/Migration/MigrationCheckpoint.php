<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;

class MigrationCheckpoint
{
    public function get(string $tableName): object
    {
        $checkpoint = DB::table('migration_checkpoints')->where('table_name', $tableName)->first();

        if (! $checkpoint) {
            $id = DB::table('migration_checkpoints')->insertGetId([
                'table_name' => $tableName,
                'status' => 'pending',
                'total_rows_source' => 0,
                'migrated_rows_target' => 0,
                'started_at' => null,
                'completed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('migration_checkpoints')->where('id', $id)->first();
        }

        return $checkpoint;
    }

    public function all(): array
    {
        return DB::table('migration_checkpoints')->orderBy('id')->get()->toArray();
    }

    public function start(string $tableName, int $sourceCount): void
    {
        $this->update($tableName, [
            'status' => 'in_progress',
            'total_rows_source' => $sourceCount,
            'started_at' => now(),
            'last_error' => null,
        ]);
    }

    public function recordProgress(string $tableName, int $migratedCount, ?string $lastId = null): void
    {
        $this->update($tableName, [
            'migrated_rows_target' => $migratedCount,
            'last_processed_id' => $lastId,
        ]);
    }

    public function complete(string $tableName, int $totalMigrated, ?string $checksum = null): void
    {
        $this->update($tableName, [
            'status' => 'completed',
            'migrated_rows_target' => $totalMigrated,
            'checksum' => $checksum,
            'completed_at' => now(),
        ]);
    }

    public function fail(string $tableName, string $errorMessage): void
    {
        $this->update($tableName, [
            'status' => 'failed',
            'last_error' => $errorMessage,
        ]);
    }

    public function reset(string $tableName): void
    {
        $this->update($tableName, [
            'status' => 'pending',
            'last_processed_id' => null,
            'migrated_rows_target' => 0,
            'checksum' => null,
            'last_error' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    protected function update(string $tableName, array $data): void
    {
        $data['updated_at'] = now();
        DB::table('migration_checkpoints')->where('table_name', $tableName)->update($data);
    }
}
