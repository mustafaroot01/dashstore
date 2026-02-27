<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

trait HandlesImageUploads
{
    /**
     * Optimize and store an image.
     * Resizes to a max width and converts to WebP for high compression.
     */
    public function optimizeAndStore(UploadedFile $file, string $directory, int $width = 1024, int $quality = 80): string
    {
        $manager = new ImageManager(new Driver());
        
        // Read the image
        $image = $manager->read($file);
        
        // Resize if width is larger than the limit
        if ($image->width() > $width) {
            $image->scale(width: $width);
        }
        
        // Encode to WebP
        $encoded = $image->toWebp($quality);
        
        // Clean filename (always .webp)
        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . '.webp';
        $path = "{$directory}/{$filename}";
        
        // Store in public disk
        Storage::disk('public')->put($path, (string) $encoded);
        
        return $path;
    }
}
