<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OptimizeUmkmPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $path,
        public string $disk = 'public',
        public int $quality = 75,
    ) {}

    public function handle(): void
    {
        $fullPath = Storage::disk($this->disk)->path($this->path);

        if (!file_exists($fullPath)) {
            return;
        }

        $info = getimagesize($fullPath);
        if (!$info) {
            return;
        }

        $image = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($fullPath),
            'image/png' => imagecreatefrompng($fullPath),
            'image/webp' => imagecreatefromwebp($fullPath),
            default => null,
        };

        if (!$image) {
            return;
        }

        // Re-save with compression
        match ($info['mime']) {
            'image/jpeg' => imagejpeg($image, $fullPath, $this->quality),
            'image/png' => imagepng($image, $fullPath, (int) round((100 - $this->quality) / 11)),
            'image/webp' => imagewebp($image, $fullPath, $this->quality),
            default => null,
        };

        imagedestroy($image);
    }
}
