<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = 'Operation successful.', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function errorResponse(string $message = 'Something went wrong.', string $error = null, int $status = 500): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if ($error) {
            $response['error'] = $error;
        }

        return response()->json($response, $status);
    }

    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], 404);
    }
}
