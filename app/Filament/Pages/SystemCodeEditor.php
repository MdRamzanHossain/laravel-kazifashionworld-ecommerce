<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Filament\Notifications\Notification;
use Symfony\Component\Finder\Finder;

class SystemCodeEditor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Code Editor';
    protected static ?string $title = 'Live Code Editor';
    protected static string $view = 'filament.pages.system-code-editor';

    public $filePath = '';
    public $fileContent = '';
    public $searchQuery = '';
    public $searchResults = [];
    public $isUnlocked = false;
    public $pinCode = '';

        public function unlockEditor()
    {
        // Change this PIN to whatever you want, or set EDITOR_PIN in your .env file
        $secretPin = env('EDITOR_PIN', '94252');

        if ($this->pinCode === $secretPin) {
            $this->isUnlocked = true;
            $this->pinCode = ''; // Clear for security
            Notification::make()->title('Access Granted!')->success()->send();
        } else {
            $this->pinCode = '';
            Notification::make()->title('Access Denied. Incorrect PIN.')->danger()->send();
        }
    }

    public function mount()
    {
        $this->filePath = base_path('resources/views/errors/404.blade.php');
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) < 3) {
            $this->searchResults = [];
            return;
        }

        try {
            $finder = new Finder();
            
            // Only search within specific directories to prevent timeouts/memory limits
            $searchDirs = [];
            foreach (['app', 'resources', 'routes', 'config'] as $dir) {
                if (is_dir(base_path($dir))) {
                    $searchDirs[] = base_path($dir);
                }
            }

            $finder->files()
                ->in($searchDirs)
                ->name('*' . $this->searchQuery . '*');

            $results = [];
            $count = 0;
            foreach ($finder as $file) {
                $results[] = $file->getRealPath();
                $count++;
                if ($count >= 30) {
                    break;
                }
            }

            $this->searchResults = $results;
        } catch (\Exception $e) {
            $this->searchResults = [];
        }
    }

    public function selectFile($path)
    {
        $this->filePath = $path;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->loadFile();
    }

    public function loadFile()
    {
        if (empty($this->filePath) || !File::exists($this->filePath)) {
            Notification::make()->title('File not found at this path!')->danger()->send();
            return;
        }

        $this->fileContent = File::get($this->filePath);
        Notification::make()->title('File loaded successfully!')->success()->send();
    }

    public function saveFile()
    {
        if (empty($this->filePath)) {
            Notification::make()->title('File path is required!')->danger()->send();
            return;
        }

        try {
            File::put($this->filePath, $this->fileContent);
            Notification::make()->title('File saved successfully!')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Error saving file: ' . $e->getMessage())->danger()->send();
        }
    }
}