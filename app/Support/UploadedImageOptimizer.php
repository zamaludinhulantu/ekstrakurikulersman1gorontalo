<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class UploadedImageOptimizer
{
    public function store(
        UploadedFile $file,
        string $absoluteDirectory,
        string $relativeDirectory,
        ?string $baseName = null,
        int $maxWidth = 1600,
        int $maxHeight = 1200,
    ): string {
        if (! File::isDirectory($absoluteDirectory)) {
            File::makeDirectory($absoluteDirectory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $safeBaseName = filled($baseName) ? Str::slug($baseName) : Str::uuid()->toString();
        $filename = $safeBaseName.'-'.Str::uuid().'.'.$extension;
        $destination = $absoluteDirectory.DIRECTORY_SEPARATOR.$filename;

        $sourceBytes = File::get($file->getRealPath());
        $source = @imagecreatefromstring($sourceBytes);
        if (! $source) {
            throw new RuntimeException('Gambar yang diunggah tidak dapat diproses.');
        }

        try {
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);
            $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);

            if ($extension === 'png' || $extension === 'webp') {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
                imagefill($target, 0, 0, $transparent);
            }

            imagecopyresampled(
                $target,
                $source,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight
            );

            $stored = match ($extension) {
                'jpg' => imagejpeg($target, $destination, 82),
                'png' => imagepng($target, $destination, 8),
                'webp' => imagewebp($target, $destination, 82),
                default => false,
            };
            imagedestroy($target);

            if (! $stored) {
                throw new RuntimeException('Gambar gagal disimpan setelah dioptimalkan.');
            }
        } finally {
            imagedestroy($source);
        }

        return trim($relativeDirectory, '/').'/'.$filename;
    }
}
