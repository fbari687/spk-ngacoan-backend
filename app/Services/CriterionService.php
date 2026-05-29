<?php

namespace App\Services;

use App\Models\Criterion;
use Illuminate\Support\Facades\DB;

class CriterionService
{
    public function getAllCriteria()
    {
        return Criterion::orderBy('code', 'asc')->get();
    }

    public function updateCriterion($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            // 1. Temukan dan update data kriteria yang diminta
            $criterion = Criterion::findOrFail($id);
            $criterion->update($data);

            // 2. Jalankan fungsi hitung ulang normalisasi untuk SEMUA kriteria
            $this->normalizeAllWeights();

            // Refresh model untuk mengembalikan data terbaru beserta bobot normalisasinya
            return $criterion->fresh();
        });
    }

    public function storeCriterion(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Auto-generate Kode (Misal: Jika terakhir C5, maka jadi C6)
            $lastCriterion = Criterion::orderBy('id', 'desc')->first();
            $lastNumber = $lastCriterion ? (int) substr($lastCriterion->code, 1) : 0;
            $data['code'] = 'C' . ($lastNumber + 1);

            $data['normalized_weight'] = 0;

            // Buat kriteria baru
            $criterion = Criterion::create($data);

            // Hitung ulang normalisasi seluruh kriteria yang ada
            $this->normalizeAllWeights();

            return $criterion->fresh();
        });
    }

    public function deleteCriterion($id)
    {
        return DB::transaction(function () use ($id) {
            $criterion = Criterion::findOrFail($id);
            $criterion->delete();

            // Jika ada kriteria yang dihapus, bobot yang lain harus dihitung ulang agar totalnya tetap 1
            $this->normalizeAllWeights();

            return true;
        });
    }

    /**
     * Fungsi internal untuk menghitung ulang bobot normalisasi (weight / total_weight)
     */
    private function normalizeAllWeights(): void
    {
        $allCriteria = Criterion::all();

        // Hitung total nilai raw input (misal: 5+4+5+3+4 = 21)
        $totalWeightInput = $allCriteria->sum('weight_input');

        if ($totalWeightInput > 0) {
            foreach ($allCriteria as $item) {
                // Kalkulasi normalisasi, bulatkan 4 angka di belakang koma
                $normalized = round($item->weight_input / $totalWeightInput, 4);

                // Gunakan update senyap (tanpa memicu events jika tidak perlu)
                $item->update(['normalized_weight' => $normalized]);
            }
        }
    }

}
