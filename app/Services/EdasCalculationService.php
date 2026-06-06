<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\Supplier;
use App\Models\Evaluation;
use App\Models\DecisionHistory;
use App\Models\Ranking;
use Illuminate\Support\Facades\DB;

class EdasCalculationService
{
    public function calculate()
    {
        return DB::transaction(function () {
            // 1. Ambil Data Master
            $criteria = Criterion::all()->keyBy('id');
            $suppliers = Supplier::all();
            $evaluations = Evaluation::all();

            if ($criteria->isEmpty() || $suppliers->isEmpty() || $evaluations->isEmpty()) {
                throw new \Exception("Data kriteria, supplier, atau matriks evaluasi masih kosong.");
            }

            // Variabel penampung seluruh langkah untuk dikirim ke frontend
            $calculationSteps = [];

            // 2. Susun Matriks Keputusan Awal (X)
            $matrixX = [];
            foreach ($evaluations as $eval) {
                $matrixX[$eval->supplier_id][$eval->criterion_id] = (float) $eval->actual_value;
            }
            $calculationSteps['decision_matrix'] = $matrixX;

            // 3. Langkah 1: Hitung Solusi Rata-rata (AV - Average Solution) untuk setiap kriteria
            $averageSolutions = [];
            foreach ($criteria as $cId => $criterion) {
                $sum = 0;
                foreach ($suppliers as $supplier) {
                    $sum += $matrixX[$supplier->id][$cId] ?? 0;
                }
                $averageSolutions[$cId] = $sum / $suppliers->count();
            }
            $calculationSteps['average_solutions'] = $averageSolutions;

            // 4. Langkah 2 & 3: Hitung Matriks PDA (Positive Distance) dan NDA (Negative Distance)
            $pda = [];
            $nda = [];
            foreach ($suppliers as $supplier) {
                foreach ($criteria as $cId => $criterion) {
                    $x = $matrixX[$supplier->id][$cId] ?? 0;
                    $av = $averageSolutions[$cId];

                    if ($av == 0) {
                        $pda[$supplier->id][$cId] = 0;
                        $nda[$supplier->id][$cId] = 0;
                        continue;
                    }

                    if ($criterion->type === 'benefit') {
                        $pda[$supplier->id][$cId] = max(0, ($x - $av)) / $av;
                        $nda[$supplier->id][$cId] = max(0, ($av - $x)) / $av;
                    } else { // Tipe Cost
                        $pda[$supplier->id][$cId] = max(0, ($av - $x)) / $av;
                        $nda[$supplier->id][$cId] = max(0, ($x - $av)) / $av;
                    }
                }
            }
            $calculationSteps['pda_matrix'] = $pda;
            $calculationSteps['nda_matrix'] = $nda;

            // 5. Langkah 4: Hitung Jumlah Terbobot PDA (SP) dan NDA (SN) untuk setiap supplier
            $sp = [];
            $sn = [];
            foreach ($suppliers as $supplier) {
                $sumSp = 0;
                $sumSn = 0;
                foreach ($criteria as $cId => $criterion) {
                    $w = (float) $criterion->normalized_weight; // Menggunakan bobot ternormalisasi
                    $sumSp += $pda[$supplier->id][$cId] * $w;
                    $sumSn += $nda[$supplier->id][$cId] * $w;
                }
                $sp[$supplier->id] = $sumSp;
                $sn[$supplier->id] = $sumSn;
            }
            $calculationSteps['sp_scores'] = $sp;
            $calculationSteps['sn_scores'] = $sn;

            // 6. Langkah 5: Normalisasi nilai SP (NSP) dan SN (NSS)
            $maxSp = max($sp);
            $maxSn = max($sn);

            $nsp = [];
            $nss = [];
            foreach ($suppliers as $supplier) {
                $nsp[$supplier->id] = $maxSp == 0 ? 1 : $sp[$supplier->id] / $maxSp;
                $nss[$supplier->id] = $maxSn == 0 ? 1 : 1 - ($sn[$supplier->id] / $maxSn);
            }
            $calculationSteps['nsp_scores'] = $nsp;
            $calculationSteps['nss_scores'] = $nss;

            // 7. Langkah 6: Hitung Skor Penilaian Simultan (AS - Appraisal Score) & Penentuan Rank
            $appraisalScores = [];
            $asScores = []; // <-- Tambahkan array penampung sementara

            foreach ($suppliers as $supplier) {
                $as = ($nsp[$supplier->id] + $nss[$supplier->id]) / 2;
                $appraisalScores[$supplier->id] = $as;
                $asScores[$supplier->id] = $as; // <-- Masukkan nilai AS ke penampung
            }

            // Titipkan array AS ke dalam objek langkah perhitungan untuk frontend
            $calculationSteps['as_scores'] = $asScores; // <--- INI KUNCI UTAMANYA

            // Urutkan supplier berdasarkan skor AS tertinggi untuk menentukan peringkat
            arsort($appraisalScores);

            // 8. Simpan Sesi ke Tabel Historis (Tetap berjalan seperti biasa)
            $history = DecisionHistory::create([
                'user_id' => auth()->id() ?: 1,
                'calculated_at' => now(),
            ]);

            $rank = 1;
            $finalRankingsData = [];
            foreach ($appraisalScores as $sId => $score) {
                Ranking::create([
                    'decision_history_id' => $history->id,
                    'supplier_id'         => $sId,
                    'appraisal_score'     => $score,
                    'rank'                => $rank
                ]);

                // Susun data untuk dikembalikan ke controller
                $finalRankingsData[] = [
                    'rank'            => $rank,
                    'supplier_id'     => $sId,
                    'supplier'        => $suppliers->find($sId),
                    'appraisal_score' => $score
                ];
                $rank++;
            }

            // Kembalikan dua objek: Hasil Akhir (Rankings) DAN Semua Langkah Rincian
            return [
                'rankings'          => $finalRankingsData,
                'calculation_steps' => $calculationSteps
            ];
        });
    }
}
