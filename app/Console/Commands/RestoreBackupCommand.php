<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Exception;
use Illuminate\Console\Command;

class RestoreBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-restore {filename : The backup archive or SQL file name} {--force : Force restore without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore application source code or database from a backup archive';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filename = $this->argument('filename');
        $force = $this->option('force');

        if (!$force && !$this->confirm("Are you sure you want to restore from {$filename}? This will overwrite existing files/data.")) {
            $this->warn("Restore aborted by user.");
            return Command::SUCCESS;
        }

        $this->info("Restoring from {$filename}...");

        try {
            $result = BackupService::restoreArchive($filename);
            $this->info("✓ " . ($result['message'] ?? 'Restore completed successfully!'));
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("✗ Restore failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
