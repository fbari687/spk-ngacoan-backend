<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BulkEvaluationRequest;
use App\Services\EvaluationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    use ApiResponse;

    protected EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function index(): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getAllEvaluations();

            return $this->successResponse(
                $evaluations,
                'Data matriks penilaian berhasil diambil.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada server.', 500);
        }
    }

    public function bulkStore(BulkEvaluationRequest $request): JsonResponse
    {
        try {
            // Ambil array 'evaluations' yang sudah divalidasi
            $data = $request->validated()['evaluations'];

            $this->evaluationService->bulkSaveEvaluations($data);

            return $this->successResponse(
                null,
                'Matriks penilaian berhasil disimpan.',
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menyimpan matriks penilaian.', 500);
        }
    }
}
