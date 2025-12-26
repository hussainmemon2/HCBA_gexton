<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class FileHelper
{
    /**
     * Upload a file to a given folder inside public directory
     * Creates folder if not exists
     *
     * @param UploadedFile $file
     * @param string $folder Path relative to public folder (example: 'assets/profile')
     * @return string The generated file name
     */
    public static function uploadToPublic(UploadedFile $file, string $folder): string
    {
        // Full path to folder in public
        $destinationPath = public_path($folder);

        // Create folder if it doesn't exist
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // Generate unique file name
        $filename = Str::random(10) . '.' . $file->getClientOriginalExtension();

        // Move file to folder
        $file->move($destinationPath, $filename);

        return $filename;
    }
}
