<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class LogoResizer
{
    /**
     * In de PDF wordt het logo hooguit 180x86px getoond; dit is ruim genoeg
     * voor een scherpe afdruk.
     */
    public const MAX_DIMENSION = 800;

    /**
     * Verkleint een opgeslagen logo als het groter is dan MAX_DIMENSION.
     * DomPDF verwerkt (transparante) PNG's pixel voor pixel, waardoor een
     * groot logo de geheugenlimiet overschrijdt en de PDF-generatie met een
     * fatale fout afbreekt.
     */
    public function shrinkStored(?string $path, string $disk = 'local'): void
    {
        if (! filled($path) || ! function_exists('imagecreatefromstring') || ! Storage::disk($disk)->exists($path)) {
            return;
        }

        $contents = Storage::disk($disk)->get($path);
        $info = @getimagesizefromstring($contents);

        if ($info === false || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
            return;
        }

        [$width, $height, $type] = $info;

        if (max($width, $height) <= self::MAX_DIMENSION) {
            return;
        }

        // Het uitpakken van een groot logo kost ruim 4 bytes per pixel.
        if ($this->memoryLimitInBytes() !== -1 && $this->memoryLimitInBytes() < 512 * 1024 * 1024) {
            @ini_set('memory_limit', '512M');
        }

        $source = @imagecreatefromstring($contents);
        unset($contents);

        if ($source === false) {
            return;
        }

        $scale = self::MAX_DIMENSION / max($width, $height);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($newWidth, $newHeight);

        if ($type === IMAGETYPE_PNG) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
        }

        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        ob_start();
        $type === IMAGETYPE_PNG ? imagepng($target, null, 6) : imagejpeg($target, null, 90);
        $resized = ob_get_clean();
        imagedestroy($target);

        if (is_string($resized) && $resized !== '') {
            Storage::disk($disk)->put($path, $resized);
        }
    }

    private function memoryLimitInBytes(): int
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '' || $limit === '-1') {
            return -1;
        }

        $value = (int) $limit;

        return match (strtolower(substr($limit, -1))) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
