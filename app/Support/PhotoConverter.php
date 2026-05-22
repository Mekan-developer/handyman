<?php

namespace App\Support;

use RuntimeException;

class PhotoConverter
{
    private const MAX_HEIGHT = 1800;

    private const WEBP_QUALITY = 85;

    /**
     * Convert an image file to WebP, scaling down if height > 1080px.
     * Returns the absolute path of the new .webp file.
     */
    public static function convert(string $absolutePath): string
    {
        $info = @getimagesize($absolutePath);

        if ($info === false) {
            throw new RuntimeException("Cannot read image: {$absolutePath}");
        }

        $width = $info[0];
        $height = $info[1];
        $mimeType = $info['mime'];

        $image = self::loadImage($absolutePath, $mimeType);

        if ($height > self::MAX_HEIGHT) {
            $image = self::scaleDown($image, $width, $height);
        }

        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $absolutePath);

        if (imagewebp($image, $webpPath, self::WEBP_QUALITY) === false) {
            imagedestroy($image);
            throw new RuntimeException("Failed to write WebP: {$webpPath}");
        }

        imagedestroy($image);

        return $webpPath;
    }

    /** @return \GdImage */
    private static function loadImage(string $path, string $mimeType): mixed
    {
        $image = match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => throw new RuntimeException("Unsupported image type: {$mimeType}"),
        };

        if ($image === false) {
            throw new RuntimeException("Failed to load image: {$path}");
        }

        return $image;
    }

    /** @param \GdImage $image */
    private static function scaleDown(mixed $image, int $width, int $height): mixed
    {
        $ratio = self::MAX_HEIGHT / $height;
        $newWidth = (int) round($width * $ratio);

        $resized = imagecreatetruecolor($newWidth, self::MAX_HEIGHT);

        if ($resized === false) {
            imagedestroy($image);
            throw new RuntimeException('Failed to create resized canvas.');
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, self::MAX_HEIGHT, $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
