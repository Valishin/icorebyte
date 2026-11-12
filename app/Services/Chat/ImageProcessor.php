<?php

namespace App\Services\Chat;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Valida, re-codifica y guarda imágenes de chat.
 *
 * Requisitos de PHP:
 *   - upload_max_filesize >= 5M
 *   - post_max_size       >= 6M
 *   - memory_limit        >= 256M  (GD + imagen ≤25 MP consume ~100-200 MB)
 */
class ImageProcessor
{
    private const MAX_MEGAPIXELS = 25_000_000;
    private const MAX_SIDE       = 2000;
    private const JPEG_QUALITY   = 82;

    /**
     * Valida, redimensiona y guarda una imagen.
     * Devuelve el nombre de fichero (sin ruta) almacenado en el disco privado.
     *
     * @throws ValidationException
     */
    public static function process(UploadedFile $file): string
    {
        $realMime = $file->getMimeType(); // finfo internamente

        // HEIC excluido: GD no lo soporta nativamente
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($realMime, $allowed, true)) {
            throw ValidationException::withMessages([
                'attachment' => 'Solo se permiten imágenes JPG, PNG o WEBP.',
            ]);
        }

        $imageInfo = @getimagesize($file->getRealPath());
        if (! $imageInfo) {
            throw ValidationException::withMessages([
                'attachment' => 'No se pudo leer la imagen.',
            ]);
        }

        [$originalW, $originalH] = $imageInfo;
        if ($originalW * $originalH > self::MAX_MEGAPIXELS) {
            throw ValidationException::withMessages([
                'attachment' => 'La imagen es demasiado grande (máximo 25 megapíxeles).',
            ]);
        }

        $img = match ($realMime) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png'  => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
        };

        if (! $img) {
            throw ValidationException::withMessages([
                'attachment' => 'No se pudo procesar la imagen.',
            ]);
        }

        $w = imagesx($img);
        $h = imagesy($img);

        if ($w > self::MAX_SIDE || $h > self::MAX_SIDE) {
            if ($w >= $h) {
                $newW = self::MAX_SIDE;
                $newH = (int) round($h * self::MAX_SIDE / $w);
            } else {
                $newH = self::MAX_SIDE;
                $newW = (int) round($w * self::MAX_SIDE / $h);
            }

            $canvas = imagecreatetruecolor($newW, $newH);

            // Fondo blanco para PNG/WEBP con transparencia convertidos a JPEG
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);

            imagecopyresampled($canvas, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($img);
            $img = $canvas;
        }

        // Re-encode como JPEG — el re-encode descarta todos los metadatos EXIF/GPS
        $filename = Str::uuid() . '.jpg';
        $tmpPath  = sys_get_temp_dir() . '/' . $filename;

        imagejpeg($img, $tmpPath, self::JPEG_QUALITY);
        imagedestroy($img);

        Storage::disk('private')->put('chat/attachments/' . $filename, file_get_contents($tmpPath));
        @unlink($tmpPath);

        return $filename;
    }
}
