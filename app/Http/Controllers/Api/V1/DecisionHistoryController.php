<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DecisionHistory;
use App\Models\Ranking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DecisionHistoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $histories = DecisionHistory::with(['rankings.supplier' => function ($query) {
            $query->withTrashed();
        }])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($histories, 'Daftar riwayat keputusan berhasil diambil.');
    }

    // Menampilkan detail peringkat dari satu riwayat spesifik
    public function show($id): JsonResponse
    {
        $rankings = Ranking::with(['supplier' => function ($query) {
            $query->withTrashed();
        }])
            ->where('decision_history_id', $id)
            ->orderBy('rank')
            ->get();

        return $this->successResponse($rankings, 'Detail peringkat berhasil diambil.');
    }
}
