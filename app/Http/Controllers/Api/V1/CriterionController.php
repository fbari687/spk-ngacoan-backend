<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CriterionRequest;
use App\Http\Resources\Api\V1\CriterionResource;
use App\Services\CriterionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CriterionController extends Controller
{
    use ApiResponse;

    protected CriterionService $criterionService;

    public function __construct(CriterionService $criterionService)
    {
        $this->criterionService = $criterionService;
    }

    public function index(): JsonResponse
    {
        try {
            $criteria = $this->criterionService->getAllCriteria();

            return $this->successResponse(
                CriterionResource::collection($criteria),
                'Data kriteria berhasil diambil.',
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada server.', 500);
        }
    }

    public function store(CriterionRequest $request): JsonResponse
    {
        try {
            $criterion = $this->criterionService->storeCriterion($request->validated());

            return $this->successResponse(
                new CriterionResource($criterion),
                'Kriteria baru berhasil ditambahkan dan bobot telah disesuaikan.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->criterionService->deleteCriterion($id);

            return $this->successResponse(
                null,
                'Kriteria berhasil dihapus dan bobot telah disesuaikan ulang.',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Kriteria tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menghapus kriteria. ' . $e->getMessage(), 500);
        }
    }

    public function update(CriterionRequest $request, $id): JsonResponse
    {
        try {
            // Validasi data sudah ditangani oleh CriterionRequest
            $criterion = $this->criterionService->updateCriterion($id, $request->validated());

            return $this->successResponse(
                new CriterionResource($criterion),
                'Kriteria dan normalisasi bobot berhasil diperbarui.',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Kriteria tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui kriteria.', 500);
        }
    }
}
