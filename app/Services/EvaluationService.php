<?php

namespace App\Services;

use App\Models\Evaluation;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    /**
     * Mengambil seluruh data matriks penilaian yang ada
     */
    public function getAllEvaluations()
    {
        // Mengambil data evaluation beserta relasinya agar frontend mudah me-mapping nama supplier & kriteria
        return Evaluation::with(['supplier', 'criterion'])->get();
    }

    /**
     * Menyimpan atau memperbarui data matriks secara massal (Bulk Action)
     */
    public function bulkSaveEvaluations(array $evaluationsData)
    {
        return DB::transaction(function () use ($evaluationsData) {
            $savedRecords = [];

            foreach ($evaluationsData as $item) {
                // updateOrCreate mencari data berdasarkan array parameter pertama,
                // lalu meng-update/insert kolom di array parameter kedua.
                $evaluation = Evaluation::updateOrCreate(
                    [
                        'supplier_id' => $item['supplier_id'],
                        'criterion_id' => $item['criterion_id'],
                    ],
                    [
                        'actual_value' => $item['actual_value']
                    ]
                );

                $savedRecords[] = $evaluation;
            }

            return $savedRecords;
        });
    }
}
