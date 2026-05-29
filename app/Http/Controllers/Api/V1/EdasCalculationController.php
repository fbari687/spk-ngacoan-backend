<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EdasCalculationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EdasCalculationController extends Controller
{
    use ApiResponse;

    protected EdasCalculationService $edasService;

    public function __construct(EdasCalculationService $edasService)
    {
        $this->edasService = $edasService;
    }

    public function calculate(Request $request): JsonResponse
    {
        try {
            // Eksekusi algoritma
            $result = $this->edasService->calculate();

            return $this->successResponse(
                [
                    'rankings' => $result['rankings'],
                    'calculation_steps' => $result['calculation_steps'],
                ],
                'Perhitungan algoritma EDAS berhasil dieksekusi dan disimpan.',
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Gagal melakukan perhitungan: ' . $e->getMessage(),
                500
            );
        }
    }
}
