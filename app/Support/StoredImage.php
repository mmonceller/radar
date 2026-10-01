<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoredImage
{
    public static function store(UploadedFile $upload, string $directory): string
    {
        return $upload->store($directory, 'public');
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function replace(?string $currentPath, UploadedFile $upload, string $directory): string
    {
        $path = self::store($upload, $directory);
        self::delete($currentPath);

        return $path;
    }
}
