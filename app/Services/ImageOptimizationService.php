<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageOptimizationService
{
    public function store(?UploadedFile $image): array
    {
        if (! $image) {
            return ['', ''];
        }

        $source = match ($image->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($image->getRealPath()),
            'image/png' => imagecreatefrompng($image->getRealPath()),
            'image/gif' => imagecreatefromgif($image->getRealPath()),
            'image/webp' => imagecreatefromwebp($image->getRealPath()),
            default => false,
        };

        if (! $source) {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, 1600 / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        $background = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $background);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagewebp($target, null, 82);
        $contents = ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        $path = 'items/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $contents);

        return ['/storage/'.$path, $path];
    }
}
