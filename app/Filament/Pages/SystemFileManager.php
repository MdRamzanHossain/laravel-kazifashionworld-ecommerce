<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Filament\Notifications\Notification;

class SystemFileManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'File Manager';
    protected static ?string $title = 'System File Manager (Upload & Overwrite)';
    protected static string $view = 'filament.pages.system-file-manager';

    public $uploadPath = '';
    public $base64File = null;
    public $fileName = '';

    public function mount()
    {
        $this->uploadPath = base_path();
    }

    public function uploadFile()
    {
        $this->validate([
            'uploadPath' => 'required|string',
            'base64File' => 'required|string',
            'fileName' => 'required|string',
        ]);

        try {
            $path = $this->uploadPath;
            $directory = dirname($path);
            
            if (is_dir($path)) {
                $directory = rtrim($path, '/\\');
                $path = $directory . DIRECTORY_SEPARATOR . $this->fileName;
            }

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $fileData = $this->base64File;
            if (strpos($fileData, ',') !== false) {
                $fileData = explode(',', $fileData)[1];
            }
            $decodedContent = base64_decode($fileData);

            if ($decodedContent === false) {
                throw new \Exception("Failed to decode file content.");
            }

            File::put($path, $decodedContent);

            Notification::make()
                ->title("File uploaded successfully")
                ->body("Saved to: " . $path)
                ->success()
                ->send();

            $this->reset(['base64File', 'fileName']);
            $this->dispatch('file-uploaded');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title("Upload failed")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}