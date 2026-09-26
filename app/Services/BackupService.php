<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BackupService
{
    /**
     * Directory path where backup files are stored.
     */
    public static function getBackupDirectory(): string
    {
        $path = storage_path('app/backups');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
        return $path;
    }

    /**
     * Create a complete SQL database dump file.
     */
    public static function createDatabaseBackup(): array
    {
        $dir = self::getBackupDirectory();
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "database_backup_{$timestamp}.sql";
        $filepath = "{$dir}/{$filename}";

        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database', 'beauty_ecommerce');
        $tableKey = "Tables_in_{$dbName}";

        $handle = fopen($filepath, 'w+');
        if (!$handle) {
            throw new Exception("Could not create backup file at {$filepath}");
        }

        // Header Comments
        fwrite($handle, "-- --------------------------------------------------------\n");
        fwrite($handle, "-- Kazi Fashion World - Database Backup\n");
        fwrite($handle, "-- Generated: " . Carbon::now()->toDateTimeString() . "\n");
        fwrite($handle, "-- Database: `{$dbName}`\n");
        fwrite($handle, "-- --------------------------------------------------------\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$tableKey ?? reset($tableObj);

            // 1. Table Schema
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for table `{$tableName}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

            $createTableStmt = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createTableStmt[0]->{'Create Table'} ?? '';
            fwrite($handle, $createSql . ";\n\n");

            // 2. Table Data
            $rows = DB::table($tableName)->get();
            if ($rows->isNotEmpty()) {
                fwrite($handle, "-- Dumping data for table `{$tableName}`\n");
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_map(fn ($col) => "`{$col}`", array_keys($rowArray));
                    $values = array_map(function ($val) {
                        if (is_null($val)) {
                            return 'NULL';
                        }
                        return "'" . addslashes((string) $val) . "'";
                    }, array_values($rowArray));

                    $insertSql = "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                    fwrite($handle, $insertSql);
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        $size = filesize($filepath);

        Log::info("Database backup created successfully: {$filename} (" . self::formatBytes($size) . ")");

        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'size'     => $size,
            'type'     => 'database',
        ];
    }

    /**
     * Create a complete application Source Code backup archive (.zip).
     * Backs up app, config, database, resources, routes, public (excluding uploads), root configs.
     */
    public static function createCodeBackup(?string $customNote = null): array
    {
        $dir = self::getBackupDirectory();
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $zipFilename = "code_backup_{$timestamp}.zip";
        $zipFilepath = "{$dir}/{$zipFilename}";

        $zip = new ZipArchive();
        if ($zip->open($zipFilepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip file at {$zipFilepath}");
        }

        $basePath = base_path();
        $fileCount = 0;

        // Directories to include in code backup
        $codeDirectories = [
            'app',
            'config',
            'database',
            'resources',
            'routes',
            'bootstrap',
        ];

        foreach ($codeDirectories as $subDir) {
            $fullDirPath = "{$basePath}/{$subDir}";
            if (File::exists($fullDirPath)) {
                $files = File::allFiles($fullDirPath);
                foreach ($files as $file) {
                    $relative = $subDir . '/' . $file->getRelativePathname();
                    // Skip cache files inside bootstrap
                    if (str_contains($relative, 'bootstrap/cache')) {
                        continue;
                    }
                    $zip->addFile($file->getRealPath(), $relative);
                    $fileCount++;
                }
            }
        }

        // Public folder assets (excluding storage symlinks, hot reloader, build cache)
        $publicPath = public_path();
        if (File::exists($publicPath)) {
            $publicFiles = File::allFiles($publicPath);
            foreach ($publicFiles as $pFile) {
                $relativeP = 'public/' . $pFile->getRelativePathname();
                if (str_starts_with($pFile->getRelativePathname(), 'storage') || 
                    str_starts_with($pFile->getRelativePathname(), 'hot')) {
                    continue;
                }
                $zip->addFile($pFile->getRealPath(), $relativeP);
                $fileCount++;
            }
        }

        // Root Configuration Files
        $rootFiles = [
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'vite.config.js',
            'tailwind.config.js',
            'postcss.config.js',
            'artisan',
            'phpunit.xml',
            'README.md',
            '.editorconfig',
        ];

        foreach ($rootFiles as $rFile) {
            $fullRPath = "{$basePath}/{$rFile}";
            if (File::exists($fullRPath)) {
                $zip->addFile($fullRPath, $rFile);
                $fileCount++;
            }
        }

        // Add Manifest
        $manifestContent = json_encode([
            'site_name'    => config('app.name', 'Kazi Fashion World'),
            'backup_type'  => 'code',
            'created_at'   => Carbon::now()->toDateTimeString(),
            'file_count'   => $fileCount,
            'laravel_ver'  => app()->version(),
            'php_version'  => PHP_VERSION,
            'note'         => $customNote ?: 'Application source code snapshot',
        ], JSON_PRETTY_PRINT);
        $zip->addFromString('backup_manifest.json', $manifestContent);

        $zip->close();

        $size = filesize($zipFilepath);

        Log::info("Code backup created successfully: {$zipFilename} ({$fileCount} files, " . self::formatBytes($size) . ")");

        return [
            'filename'   => $zipFilename,
            'filepath'   => $zipFilepath,
            'size'       => $size,
            'file_count' => $fileCount,
            'type'       => 'code',
        ];
    }

    /**
     * Create a full snapshot archive (Source Code + Database SQL dump + Media Uploads).
     */
    public static function createFullBackup(?string $customNote = null): array
    {
        $dir = self::getBackupDirectory();
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $zipFilename = "full_snapshot_{$timestamp}.zip";
        $zipFilepath = "{$dir}/{$zipFilename}";

        // Step 1: Create Database Dump
        $dbBackup = self::createDatabaseBackup();
        $sqlPath = $dbBackup['filepath'];

        // Step 2: Create Zip Archive
        $zip = new ZipArchive();
        if ($zip->open($zipFilepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip file at {$zipFilepath}");
        }

        // Add Database Dump to Zip
        $zip->addFile($sqlPath, 'database/' . basename($sqlPath));

        $basePath = base_path();
        $fileCount = 0;

        // Add Source Code Directories
        $codeDirectories = [
            'app',
            'config',
            'database',
            'resources',
            'routes',
            'bootstrap',
        ];

        foreach ($codeDirectories as $subDir) {
            $fullDirPath = "{$basePath}/{$subDir}";
            if (File::exists($fullDirPath)) {
                $files = File::allFiles($fullDirPath);
                foreach ($files as $file) {
                    $relative = $subDir . '/' . $file->getRelativePathname();
                    if (str_contains($relative, 'bootstrap/cache')) {
                        continue;
                    }
                    $zip->addFile($file->getRealPath(), $relative);
                    $fileCount++;
                }
            }
        }

        // Public assets
        $publicPath = public_path();
        if (File::exists($publicPath)) {
            $publicFiles = File::allFiles($publicPath);
            foreach ($publicFiles as $pFile) {
                $relativeP = 'public/' . $pFile->getRelativePathname();
                if (str_starts_with($pFile->getRelativePathname(), 'storage') || 
                    str_starts_with($pFile->getRelativePathname(), 'hot')) {
                    continue;
                }
                $zip->addFile($pFile->getRealPath(), $relativeP);
                $fileCount++;
            }
        }

        // Root Files
        $rootFiles = [
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'vite.config.js',
            'tailwind.config.js',
            'postcss.config.js',
            'artisan',
            'phpunit.xml',
            'README.md',
        ];

        foreach ($rootFiles as $rFile) {
            $fullRPath = "{$basePath}/{$rFile}";
            if (File::exists($fullRPath)) {
                $zip->addFile($fullRPath, $rFile);
                $fileCount++;
            }
        }

        // Add Uploaded Public Storage Files
        $publicStoragePath = storage_path('app/public');
        if (File::exists($publicStoragePath)) {
            $storageFiles = File::allFiles($publicStoragePath);
            foreach ($storageFiles as $sFile) {
                $relativePath = 'uploads/' . $sFile->getRelativePathname();
                $zip->addFile($sFile->getRealPath(), $relativePath);
                $fileCount++;
            }
        }

        // Manifest
        $manifestContent = json_encode([
            'site_name'    => config('app.name', 'Kazi Fashion World'),
            'backup_type'  => 'full_snapshot',
            'created_at'   => Carbon::now()->toDateTimeString(),
            'file_count'   => $fileCount,
            'php_version'  => PHP_VERSION,
            'laravel_ver'  => app()->version(),
            'note'         => $customNote ?: 'Full Codebase + Database + Media Snapshot',
        ], JSON_PRETTY_PRINT);
        $zip->addFromString('backup_manifest.json', $manifestContent);

        $zip->close();

        // Clean up temp SQL dump
        if (File::exists($sqlPath)) {
            File::delete($sqlPath);
        }

        $size = filesize($zipFilepath);

        Log::info("Full snapshot created successfully: {$zipFilename} ({$fileCount} files, " . self::formatBytes($size) . ")");

        return [
            'filename'   => $zipFilename,
            'filepath'   => $zipFilepath,
            'size'       => $size,
            'file_count' => $fileCount,
            'type'       => 'full',
        ];
    }

    /**
     * Get list of all existing backup archives.
     */
    public static function getBackupsList(): array
    {
        $dir = self::getBackupDirectory();
        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $extension = strtolower($file->getExtension());

            if (!in_array($extension, ['sql', 'zip', 'gz'])) {
                continue;
            }

            $size = $file->getSize();
            $mtime = $file->getMTime();
            $createdAt = Carbon::createFromTimestamp($mtime);

            if (str_starts_with($filename, 'code_backup_')) {
                $type = 'Codebase Only';
                $badgeColor = 'info';
                $icon = 'heroicon-o-code-bracket';
            } elseif (str_starts_with($filename, 'full_snapshot_') || str_contains($filename, 'full')) {
                $type = 'Full Snapshot (Code + DB)';
                $badgeColor = 'primary';
                $icon = 'heroicon-o-archive-box';
            } else {
                $type = 'Database SQL Dump';
                $badgeColor = 'success';
                $icon = 'heroicon-o-circle-stack';
            }

            $backups[] = [
                'filename'       => $filename,
                'filepath'       => $file->getRealPath(),
                'size'           => $size,
                'formatted_size' => self::formatBytes($size),
                'type'           => $type,
                'badge_color'    => $badgeColor,
                'icon'           => $icon,
                'extension'      => strtoupper($extension),
                'created_at'     => $createdAt->format('Y-m-d H:i:s'),
                'relative_age'   => $createdAt->diffForHumans(),
            ];
        }

        // Sort latest backups first
        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Restore from a backup archive (.zip code/full or .sql database).
     */
    public static function restoreArchive(string $filename): array
    {
        $dir = self::getBackupDirectory();
        $filepath = "{$dir}/{$filename}";

        if (!File::exists($filepath)) {
            throw new Exception("Backup archive {$filename} does not exist on disk.");
        }

        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if ($extension === 'sql') {
            self::restoreDatabase($filename);
            return [
                'type'    => 'database',
                'message' => "Database successfully restored from {$filename}.",
            ];
        }

        if ($extension === 'zip') {
            return self::restoreZipArchive($filepath);
        }

        throw new Exception("Unsupported backup format .{$extension}");
    }

    /**
     * Restore Code & Database from a Zip Archive.
     */
    protected static function restoreZipArchive(string $zipFilepath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilepath) !== true) {
            throw new Exception("Could not open zip archive at {$zipFilepath}");
        }

        $basePath = base_path();
        $restoredFiles = 0;
        $restoredDb = false;

        // Extract files
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            
            // Skip directory entries
            if (str_ends_with($entryName, '/')) {
                continue;
            }

            // Skip manifest
            if ($entryName === 'backup_manifest.json') {
                continue;
            }

            // Check if it's an uploaded media file
            if (str_starts_with($entryName, 'uploads/')) {
                $relativeStoragePath = substr($entryName, strlen('uploads/'));
                $targetStorageFile = storage_path('app/public/' . $relativeStoragePath);
                
                File::ensureDirectoryExists(dirname($targetStorageFile));
                $content = $zip->getFromIndex($i);
                File::put($targetStorageFile, $content);
                $restoredFiles++;
                continue;
            }

            // Check if it's a database SQL dump inside the zip
            if (str_ends_with($entryName, '.sql') && (str_starts_with($entryName, 'database/') || !str_contains($entryName, '/'))) {
                $sqlContent = $zip->getFromIndex($i);
                if (!empty($sqlContent)) {
                    DB::unprepared($sqlContent);
                    $restoredDb = true;
                }
                continue;
            }

            // Otherwise, it is a code file (app, config, resources, routes, database migrations, etc.)
            $targetFile = "{$basePath}/{$entryName}";
            File::ensureDirectoryExists(dirname($targetFile));
            $content = $zip->getFromIndex($i);
            File::put($targetFile, $content);
            $restoredFiles++;
        }

        $zip->close();

        // Clear all caches
        try {
            Artisan::call('optimize:clear');
        } catch (Exception $e) {
            Log::warning("Cache clear warning during restore: " . $e->getMessage());
        }

        $msg = "Restored {$restoredFiles} code/media files.";
        if ($restoredDb) {
            $msg .= " Database tables were also restored.";
        }

        Log::info("Archive restored successfully: " . basename($zipFilepath) . " ({$msg})");

        return [
            'type'           => 'zip',
            'restored_files' => $restoredFiles,
            'restored_db'    => $restoredDb,
            'message'        => $msg,
        ];
    }

    /**
     * Restore database from a SQL backup file.
     */
    public static function restoreDatabase(string $filename): void
    {
        $dir = self::getBackupDirectory();
        $filepath = "{$dir}/{$filename}";

        if (!File::exists($filepath)) {
            throw new Exception("Backup file {$filename} not found.");
        }

        $sqlContent = File::get($filepath);
        if (empty($sqlContent)) {
            throw new Exception("Backup SQL file is empty.");
        }

        DB::unprepared($sqlContent);

        try {
            Artisan::call('optimize:clear');
        } catch (Exception $e) {}

        Log::info("Database successfully restored from backup file: {$filename}");
    }

    /**
     * Delete a backup file from disk.
     */
    public static function deleteBackup(string $filename): bool
    {
        $dir = self::getBackupDirectory();
        $filepath = "{$dir}/{$filename}";

        if (File::exists($filepath)) {
            return File::delete($filepath);
        }

        return false;
    }

    /**
     * Generate file download response.
     */
    public static function downloadBackup(string $filename): BinaryFileResponse
    {
        $dir = self::getBackupDirectory();
        $filepath = "{$dir}/{$filename}";

        if (!File::exists($filepath)) {
            abort(404, "Backup file not found.");
        }

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Format bytes into human readable format (KB, MB, GB).
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
