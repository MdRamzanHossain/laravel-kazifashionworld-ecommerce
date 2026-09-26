<?php

namespace App\Filament\Pages;

use App\Services\BackupService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManageBackups extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.manage-backups';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?string $navigationLabel = 'Code & DB Backups';

    protected static ?string $title = 'Code & Database Backups';

    protected static ?int $navigationSort = 90;

    public array $backups = [];

    public function mount(): void
    {
        $this->loadBackups();
    }

    public function loadBackups(): void
    {
        $this->backups = BackupService::getBackupsList();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_code_backup')
                ->label('Backup Source Code')
                ->icon('heroicon-m-code-bracket')
                ->color('info')
                ->form([
                    TextInput::make('note')
                        ->label('Backup Note / Label')
                        ->placeholder('e.g. Before modifying header layout or updating themes')
                        ->maxLength(120),
                ])
                ->modalHeading('Create Codebase Backup')
                ->modalDescription('Generate a compressed .zip snapshot of all application code (controllers, models, views, styles, migrations, routes, and configs).')
                ->modalSubmitActionLabel('Create Code Backup')
                ->action(function (array $data) {
                    try {
                        $result = BackupService::createCodeBackup($data['note'] ?? null);
                        $this->loadBackups();

                        Notification::make()
                            ->title('Codebase Backup Created!')
                            ->body("Successfully generated {$result['filename']} ({$result['file_count']} files, " . BackupService::formatBytes($result['size']) . ")")
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Code Backup Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('create_db_backup')
                ->label('Backup Database')
                ->icon('heroicon-m-circle-stack')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Create Database Backup')
                ->modalDescription('Generate an immediate SQL dump of all database tables and records.')
                ->modalSubmitActionLabel('Create DB Backup')
                ->action(function () {
                    try {
                        $result = BackupService::createDatabaseBackup();
                        $this->loadBackups();

                        Notification::make()
                            ->title('Database Backup Created!')
                            ->body("Successfully generated {$result['filename']} (" . BackupService::formatBytes($result['size']) . ")")
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Backup Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('create_full_backup')
                ->label('Full Snapshot (Code + DB)')
                ->icon('heroicon-m-archive-box')
                ->color('primary')
                ->form([
                    TextInput::make('note')
                        ->label('Snapshot Description')
                        ->placeholder('e.g. Complete pre-launch snapshot')
                        ->maxLength(120),
                ])
                ->modalHeading('Create Full Snapshot')
                ->modalDescription('Generate a complete compressed .zip archive containing application source code, full database SQL dump, and media uploads.')
                ->modalSubmitActionLabel('Create Full Snapshot')
                ->action(function (array $data) {
                    try {
                        $result = BackupService::createFullBackup($data['note'] ?? null);
                        $this->loadBackups();

                        Notification::make()
                            ->title('Full Snapshot Created!')
                            ->body("Successfully generated {$result['filename']} ({$result['file_count']} files, " . BackupService::formatBytes($result['size']) . ")")
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Full Snapshot Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('upload_backup')
                ->label('Upload & Restore Backup')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('backup_file')
                        ->label('Select Backup Archive (.zip or .sql)')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'application/sql', 'text/plain', 'text/x-sql', 'application/octet-stream'])
                        ->disk('local')
                        ->directory('temp-backups')
                        ->required(),
                ])
                ->modalHeading('Upload & Restore Backup Archive')
                ->modalDescription('Upload a previously downloaded .zip code snapshot or .sql database dump from your computer.')
                ->modalSubmitActionLabel('Upload & Save to Backups')
                ->action(function (array $data) {
                    try {
                        $tempPath = storage_path('app/' . $data['backup_file']);
                        if (!File::exists($tempPath)) {
                            throw new Exception("Uploaded file could not be found.");
                        }

                        $originalName = basename($tempPath);
                        $destDir = BackupService::getBackupDirectory();
                        $destPath = "{$destDir}/{$originalName}";

                        File::move($tempPath, $destPath);
                        $this->loadBackups();

                        Notification::make()
                            ->title('Backup Uploaded Successfully!')
                            ->body("File {$originalName} is now in your backup list. You can click 'Restore' or 'Download' anytime.")
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Upload Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function download(string $filename): BinaryFileResponse
    {
        return BackupService::downloadBackup($filename);
    }

    public function delete(string $filename): void
    {
        if (BackupService::deleteBackup($filename)) {
            $this->loadBackups();

            Notification::make()
                ->title('Backup Deleted')
                ->body("Archive {$filename} was removed from disk.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Delete Failed')
                ->body("Could not delete {$filename}.")
                ->danger()
                ->send();
        }
    }

    public function restore(string $filename): void
    {
        try {
            $result = BackupService::restoreArchive($filename);
            $this->loadBackups();

            Notification::make()
                ->title('Restore Completed Successfully!')
                ->body($result['message'] ?? "Application and data successfully restored from {$filename}.")
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Restore Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
