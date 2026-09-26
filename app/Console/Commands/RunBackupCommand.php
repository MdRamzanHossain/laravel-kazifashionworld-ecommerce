<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Exception;
use Illuminate\Console\Command;

class RunBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-run {--type=code : The type of backup (code, db, or full)} {--note= : Optional description note}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a scheduled or on-demand code, database, or full site snapshot backup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = strtolower($this->option('type') ?? 'code');
        $note = $this->option('note');

        $this->info("Initiating " . strtoupper($type) . " backup for Kazi Fashion World...");

        try {
            if ($type === 'code') {
                $result = BackupService::createCodeBackup($note);
            } elseif ($type === 'full') {
                $result = BackupService::createFullBackup($note);
            } else {
                $result = BackupService::createDatabaseBackup();
            }

            $formattedSize = BackupService::formatBytes($result['size']);
            $this->info("✓ Backup completed successfully!");
            $this->table(
                ['File Name', 'Size', 'Type', 'Location'],
                [[$result['filename'], $formattedSize, strtoupper($result['type'] ?? $type), $result['filepath']]]
            );

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("✗ Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
