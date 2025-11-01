<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {

            if ($request->expectsJson()) {

                if ($e instanceof ValidationException) {
                    return $this->validationErrorResponse($e);
                }

                if ($e instanceof ModelNotFoundException) {
                    $model = class_basename($e->getModel());
                    return $this->errorResponse("{$model} not found", 404);
                }

                if ($e instanceof NotFoundHttpException) {
                    return $this->errorResponse('Route not found', 404);
                }

                if ($e instanceof AuthenticationException) {
                    return $this->errorResponse('Unauthenticated', 401);
                }

                if ($e instanceof AuthorizationException) {
                    return $this->errorResponse('Unauthorized', 403);
                }
                return $this->exceptionResponse($e);
            }
        });
    }
}
