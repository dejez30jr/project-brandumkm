<?php

namespace App\Jobs;

use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CompressUmkmVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 600;

    public function __construct(
        public string $path,
        public string $disk = 'public',
    ) {}

    public function handle(): void
    {
        if (!Storage::disk($this->disk)->exists($this->path)) {
            return;
        }

        $fullPath = Storage::disk($this->disk)->path($this->path);
        $fileSize = filesize($fullPath);

        // Skip jika file sudah kecil (< 10MB)
        if ($fileSize < 10 * 1024 * 1024) {
            return;
        }

        $compressedPath = $this->path . '_compressed.mp4';

        try {
            $format = (new X264('aac'))
                ->setKiloBitrate(1000)
                ->setAudioKiloBitrate(128);

            $format->setAdditionalParameters([
                '-preset', 'fast',
                '-crf', '28',
                '-vf', 'scale=-2:720',
                '-movflags', '+faststart',
            ]);

            FFMpeg::fromDisk($this->disk)
                ->open($this->path)
                ->export()
                ->inFormat($format)
                ->toDisk($this->disk)
                ->save($compressedPath);

            $compressedFullPath = Storage::disk($this->disk)->path($compressedPath);
            $compressedSize = filesize($compressedFullPath);

            // Replace original hanya jika compressed lebih kecil
            if ($compressedSize > 0 && $compressedSize < $fileSize) {
                rename($compressedFullPath, $fullPath);
                Log::info("Video compressed: {$this->path} ({$this->formatSize($fileSize)} → {$this->formatSize($compressedSize)})");
            } else {
                Storage::disk($this->disk)->delete($compressedPath);
            }
        } catch (\Throwable $e) {
            Storage::disk($this->disk)->delete($compressedPath);
            Log::warning("Video compression failed: {$this->path} - {$e->getMessage()}");
        }
    }

    private function formatSize(int $bytes): string
    {
        return round($bytes / 1024 / 1024, 1) . 'MB';
    }
}
