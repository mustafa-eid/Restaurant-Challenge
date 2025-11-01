<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    protected function successResponse($data = null, string $message = 'Request successful', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Error response (general)
     */
    protected function errorResponse(string $message = 'Something went wrong', int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    /**
     * Not Found response (404)
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => null,
        ], 404);
    }

    /**
     * Validation error response (422)
     */
    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $exception->errors(),
        ], 422);
    }

    /**
     * Global exception response (500)
     */
    protected function exceptionResponse(\Throwable $exception, int $status = 500): JsonResponse
    {
        Log::error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Internal server error',
            'error'   => config('app.debug') ? $exception->getMessage() : null,
        ], $status);
    }
}
