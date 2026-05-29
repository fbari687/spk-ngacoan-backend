<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->authenticate($request->validated());

            return $this->successResponse([
                'token' => $result['token'],
                'user' => new UserResource($result['user'])
            ], 'Login berhasil', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Validasi gagal.',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function me(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            // Ambil data user yang sudah dilewatkan oleh middleware Sanctum
            $userWithRole = $this->authService->getProfile($request->user());

            // Kembalikan respon sukses yang seragam menggunakan UserResource
            return $this->successResponse(
                new UserResource($userWithRole),
                'Data profil berhasil diambil.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Gagal mengambil data profil.',
                500
            );
        }
    }

    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil.', 200);
    }
}
