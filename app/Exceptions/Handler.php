<?php
 
namespace App\Exceptions;
 
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation'
    ];
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    //render Json response for API exceptions
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
 
            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
 
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $exception->errors(),
                ], 422);
            }
 
            if ($exception instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'An HTTP error occurred.',
                ], $exception->getStatusCode());
            }
        }
        return parent::render($request, $exception);
    }  
}