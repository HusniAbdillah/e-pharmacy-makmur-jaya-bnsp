<?php

namespace App\Jobs;

use App\Models\Obat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProcessCsvImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $filePath) {}

    public function handle(): void
    {
        // Import CSV via Laravel Excel
        $data = Excel::toArray([], storage_path("app/" . $this->filePath))[0];

        foreach ($data as $index => $row) {
            if ($index === 0) {
                continue;
            } // Skip header

            Obat::create([
                "kategori_obat_id" => $row[0],
                "name" => $row[1],
                "description" => $row[2] ?? null,
                "composition" => $row[3] ?? null,
                "dose" => $row[4],
                "side_effects" => $row[5] ?? null,
                "price" => $row[6],
                "minimum_stock" => $row[7] ?? 10,
                "requires_prescription" => $row[8] ?? false,
            ]);
        }

        Log::info("CSV import selesai: {$this->filePath}");
    }
}
