<?php

namespace App\Services\Payment;

use Illuminate\Http\UploadedFile;

class FileService
{
    public function upload(UploadedFile $file, $path = '/'): string
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        $fileNameToStore = $filename.'_'.time().'.'.$extension;

        $path = $file->storeAs($path, $fileNameToStore);
         return storage_path('app/'.$path);
    }

    public function remove(string $path): bool
    {
        if (unlink($path) || !file_exists($path)) {
            return true;
        }

        return unlink($path);
    }
}
