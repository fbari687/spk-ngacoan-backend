<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SupplierRequest;
use App\Http\Resources\Api\V1\SupplierResource;
use App\Services\SupplierService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    use ApiResponse;

    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(): JsonResponse
    {
        try {
            $suppliers = $this->supplierService->getAllSuppliers();
            return $this->successResponse(
                SupplierResource::collection($suppliers),
                'Data supplier berhasil diambil.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada server.', 500);
        }
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->storeSupplier($request->validated());
            return $this->successResponse(
                new SupplierResource($supplier),
                'Supplier baru berhasil ditambahkan.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menambahkan supplier.', 500);
        }
    }

    public function update(SupplierRequest $request, $id): JsonResponse
    {
        try {
            $supplier = $this->supplierService->updateSupplier($id, $request->validated());
            return $this->successResponse(
                new SupplierResource($supplier),
                'Data supplier berhasil diperbarui.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui data supplier.', 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->supplierService->deleteSupplier($id);
            return $this->successResponse(null, 'Supplier berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menghapus supplier.', 500);
        }
    }
}
