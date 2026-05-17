<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class QurbanService
{
    private string $disk   = 'local';
    private string $folder = 'qurban'; // BUAT FILE JSON DI STROGE/QURBAN/NAMA FILE.JSON

    public function get(string $slug): ?array
    {
        $path = "{$this->folder}/qurban.json";

        if (!Storage::disk($this->disk)->exists($path)) {
            return null;
        }

        return json_decode(
            Storage::disk($this->disk)->get($path),
            true
        );
    }

    public function save(string $slug, array $data): void
    {
        $path = "{$this->folder}/qurban.json";

        Storage::disk($this->disk)->put(
            $path,
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    public function isLocked(array $project): bool
    {
        if ($project['is_paid']) return false;

        $deploy  = \Carbon\Carbon::parse($project['deploy_date']);
        $elapsed = now()->diffInDays($deploy);

        return $elapsed >= $project['lock_days'];
    }

    public function daysLeft(array $project): int
    {
        $deploy  = \Carbon\Carbon::parse($project['deploy_date']);
        $elapsed = now()->diffInDays($deploy);

        return max(0, $project['lock_days'] - $elapsed);
    }
}