<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

trait OptimizesImages
{
    public function optimizeImage($path)
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return $path;
        }

        return $path; // Bypass WebP conversion completely due to server limits
        if (Str::endsWith(strtolower($path), '.webp')) {
            return $path;
        }

        try {
            $manager = new ImageManager(new Driver());
            $fullPath = Storage::disk('public')->path($path);
            
            $image = $manager->read($fullPath);
            
            $newPath = preg_replace('/\.[a-zA-Z]+$/', '.webp', $path);
            
            // Encode to WebP 80% and save via Storage
            $encodedWebp = $image->toWebp(80)->toString();
            Storage::disk('public')->put($newPath, $encodedWebp);
            
            // Generate Thumbnail (e.g. 400px width)
            $thumbPath = preg_replace('/\.[a-zA-Z]+$/', '-thumb.webp', $path);
            
            $image->scale(width: 400);
            $encodedThumb = $image->toWebp(75)->toString();
            Storage::disk('public')->put($thumbPath, $encodedThumb);

            // Delete original file
            Storage::disk('public')->delete($path);
            
            return $newPath;
        } catch (\Exception $e) {
            return $path;
        }
    }
}
