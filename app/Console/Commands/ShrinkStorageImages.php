<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Reduce las imágenes subidas por el panel que quedaron con resolución de
 * cámara. Se muestran a lo sumo al ancho del viewport, pero el navegador del
 * visitante decodifica el original entero y lo mantiene como textura: una foto
 * de 4096x2748 son ~43 MB de VRAM, y las del slider de portada viven además en
 * una capa GPU permanente junto al hero.
 *
 * Las subidas nuevas ya entran reducidas desde el navegador (ver
 * UploadMorph.downscaleImage en partials/upload-morph.blade.php). Este comando
 * es para las que ya estaban.
 *
 * Siempre respalda antes de tocar nada, y por defecto no escribe: hay que
 * pasarle --force.
 */
class ShrinkStorageImages extends Command
{
    protected $signature = 'images:shrink
        {--path= : Subcarpeta de storage/app/public (por defecto, todas)}
        {--max=2560 : Ancho máximo en píxeles}
        {--quality=82 : Calidad JPEG/WebP}
        {--force : Escribir de verdad (sin esto sólo lista lo que haría)}
        {--no-backup : No respaldar (desaconsejado)}';

    protected $description = 'Reduce imágenes subidas que superan el ancho máximo, con respaldo previo';

    public function handle(): int
    {
        $root = storage_path('app/public');
        $sub  = trim((string) $this->option('path'), '/');
        $base = $sub !== '' ? $root.'/'.$sub : $root;

        if (! is_dir($base)) {
            $this->error("No existe: {$base}");

            return self::FAILURE;
        }

        $max     = max(320, (int) $this->option('max'));
        $quality = min(100, max(40, (int) $this->option('quality')));
        $force   = (bool) $this->option('force');

        $engine = $this->detectEngine();
        if ($engine === null) {
            $this->error('No hay con qué procesar imágenes: falta la extensión Imagick o GD de PHP, y tampoco está el binario `magick`/`convert`.');
            $this->line('  Instalá una: <comment>apt install php-gd</comment> o <comment>apt install imagemagick</comment>');

            return self::FAILURE;
        }
        $this->line("Motor: <info>{$engine}</info> · máximo {$max}px · calidad {$quality}");

        $targets = $this->findOversized($base, $max);

        if ($targets === []) {
            $this->info('Nada que reducir: ninguna imagen supera los '.$max.'px de ancho.');

            return self::SUCCESS;
        }

        $backupDir = null;
        if ($force && ! $this->option('no-backup')) {
            $backupDir = storage_path('app/image-backups/'.now()->format('Ymd-His'));
            if (! is_dir($backupDir) && ! mkdir($backupDir, 0775, true) && ! is_dir($backupDir)) {
                $this->error("No se pudo crear el respaldo en {$backupDir}");

                return self::FAILURE;
            }
            $this->line("Respaldo: <info>{$backupDir}</info>");
        }

        $rows = [];
        $before = 0;
        $after = 0;

        foreach ($targets as $t) {
            $rel = ltrim(str_replace($root, '', $t['path']), '/');
            $before += $t['bytes'];

            if (! $force) {
                $after += (int) ($t['bytes'] * 0.15);   // estimación sólo para el resumen
                $rows[] = [$rel, $t['width'].'px', $this->human($t['bytes']), '—'];
                continue;
            }

            if ($backupDir !== null && ! $this->backup($t['path'], $root, $backupDir)) {
                $this->warn("  No se pudo respaldar {$rel}; se omite.");
                $after += $t['bytes'];
                continue;
            }

            if (! $this->shrink($engine, $t['path'], $max, $quality)) {
                $this->warn("  Falló al procesar {$rel}; queda como estaba.");
                $after += $t['bytes'];
                continue;
            }

            clearstatcache(true, $t['path']);
            $newBytes = (int) filesize($t['path']);
            $after += $newBytes;
            $rows[] = [$rel, $t['width'].'px → '.$max.'px', $this->human($t['bytes']), $this->human($newBytes)];
        }

        $this->newLine();
        $this->table(['archivo', 'ancho', 'antes', 'después'], $rows);

        $this->info(sprintf(
            '%d imagen/es · %s → %s (%s menos)',
            count($targets),
            $this->human($before),
            $this->human($after),
            $before > 0 ? round((1 - $after / $before) * 100).'%' : '0%'
        ));

        if (! $force) {
            $this->newLine();
            $this->comment('Esto fue una simulación. Volvé a correrlo con --force para escribir.');
        }

        return self::SUCCESS;
    }

    /** Imagick y GD son preferibles; el binario es el último recurso. */
    private function detectEngine(): ?string
    {
        if (extension_loaded('imagick')) {
            return 'imagick';
        }
        if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
            return 'gd';
        }
        foreach (['magick', 'convert'] as $bin) {
            exec('command -v '.escapeshellarg($bin).' 2>/dev/null', $out, $code);
            if ($code === 0 && ! empty($out)) {
                return $bin;
            }
        }

        return null;
    }

    /** @return array<int, array{path:string,width:int,bytes:int}> */
    private function findOversized(string $base, int $max): array
    {
        $found = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }
            // Los respaldos viven fuera de public, pero por si acaso.
            if (str_contains($file->getPathname(), '/image-backups/')) {
                continue;
            }

            $size = @getimagesize($file->getPathname());
            if ($size === false || (int) $size[0] <= $max) {
                continue;
            }

            $found[] = [
                'path'  => $file->getPathname(),
                'width' => (int) $size[0],
                'bytes' => (int) $file->getSize(),
            ];
        }

        usort($found, fn ($a, $b) => $b['bytes'] <=> $a['bytes']);

        return $found;
    }

    private function backup(string $path, string $root, string $backupDir): bool
    {
        $rel = ltrim(str_replace($root, '', $path), '/');
        $dest = $backupDir.'/'.$rel;
        $dir = dirname($dest);

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return false;
        }

        return copy($path, $dest);
    }

    private function shrink(string $engine, string $path, int $max, int $quality): bool
    {
        try {
            return match ($engine) {
                'imagick' => $this->shrinkImagick($path, $max, $quality),
                'gd'      => $this->shrinkGd($path, $max, $quality),
                default   => $this->shrinkCli($engine, $path, $max, $quality),
            };
        } catch (\Throwable $e) {
            $this->warn('  '.$e->getMessage());

            return false;
        }
    }

    private function shrinkImagick(string $path, int $max, int $quality): bool
    {
        $img = new \Imagick($path);
        $img->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
        $img->resizeImage($max, 0, \Imagick::FILTER_LANCZOS, 1);
        $img->stripImage();
        $img->setImageCompressionQuality($quality);
        $ok = $img->writeImage($path);
        $img->clear();

        return (bool) $ok;
    }

    private function shrinkGd(string $path, int $max, int $quality): bool
    {
        $info = getimagesize($path);
        if ($info === false) {
            return false;
        }

        $src = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default        => null,
        };
        if (! $src) {
            return false;
        }

        $w = $max;
        $h = (int) round(imagesy($src) * ($max / imagesx($src)));
        $dst = imagecreatetruecolor($w, $h);

        // PNG y WebP pueden traer transparencia.
        if (in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
        imagedestroy($src);

        $ok = match ($info[2]) {
            IMAGETYPE_JPEG => imagejpeg($dst, $path, $quality),
            IMAGETYPE_PNG  => imagepng($dst, $path, 8),
            IMAGETYPE_WEBP => imagewebp($dst, $path, $quality),
            default        => false,
        };
        imagedestroy($dst);

        return (bool) $ok;
    }

    private function shrinkCli(string $bin, string $path, int $max, int $quality): bool
    {
        $cmd = sprintf(
            '%s %s -auto-orient -resize %s -strip -quality %d %s 2>&1',
            escapeshellarg($bin),
            escapeshellarg($path),
            escapeshellarg($max.'x'),
            $quality,
            escapeshellarg($path)
        );
        exec($cmd, $out, $code);

        return $code === 0;
    }

    private function human(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}
