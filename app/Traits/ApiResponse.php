<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data, string $message = 'Operasi berhasil', int $code = 200): JsonResponse
    {
        return response()->json([
            'meta' => [
                'code' => $code,
                'status' => 'success',
                'message' => $message,
            ],
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message, int $code, mixed $errors = null): JsonResponse
    {
        $response = [
            'meta' => [
                'code' => $code,
                'status' => 'error',
                'message' => $message,
            ],
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
