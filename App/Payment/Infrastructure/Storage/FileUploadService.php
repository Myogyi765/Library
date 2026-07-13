<?php

namespace App\Payment\Infrastructure\Storage;

class FileUploadService
{
    public function store(array $file): string
    {
        $targetDir = BASE_PATH . '/public/storage/payment_screenshots/';
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetPath = $targetDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Failed to upload screenshot.');
        }
        
        return 'storage/payment_screenshots/' . $filename;
    }
}