<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\Log;

class MigrationLogger
{
    protected string $logFile;

    public function __construct()
    {
        $this->logFile = storage_path('logs/migration.log');
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = ! empty($context) ? ' '.json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $formatted = "[{$timestamp}] [{$level}] {$message}{$contextStr}".PHP_EOL;

        if (! file_exists(dirname($this->logFile))) {
            @mkdir(dirname($this->logFile), 0777, true);
        }

        @file_put_contents($this->logFile, $formatted, FILE_APPEND);
        Log::channel('single')->log($level, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warn(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
}
