<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use App\Models\Supplier;
use App\Models\Ranking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        try {
            $totalCriteria = Criterion::count();
            $totalSuppliers = Supplier::count();

            // Mengambil peringkat 1 dari riwayat komputasi (Decision History) terbaru
            $topRanking = Ranking::with('supplier')
                ->where('rank', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            $topSupplier = $topRanking ? $topRanking->supplier->code . ' - ' . $topRanking->supplier->name : 'Belum Dihitung';

            // Logika status sistem
            if ($totalCriteria == 0 || $totalSuppliers == 0) {
                $status = 'Menunggu Data';
            } else {
                $status = 'Optimal';
            }

            return $this->successResponse([
                'total_criteria' => $totalCriteria,
                'total_suppliers' => $totalSuppliers,
                'top_supplier' => $topSupplier,
                'system_status' => $status
            ], 'Metrik dashboard berhasil diambil.');

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memuat metrik dashboard.', 500);
        }
    }
}
