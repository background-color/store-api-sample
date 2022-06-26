<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Traits\ResponseTrait;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                Log::error('API: '.$request->method().': '.$request->fullUrl());
                Log::error('Class: ' . get_class($e));
                Log::error('Message: ' . $e->getMessage());
                
                if ($this->isHttpException($e)) {
                    $message = $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()];
                    return $this->getErrorResponse($message, $e->getStatusCode());
                }
                if ($e instanceof AuthenticationException) {
                    return $this->getErrorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
                }
                /*
                if ($e instanceof ValidationException) {

                }
                */
            }
        });
    }
}
