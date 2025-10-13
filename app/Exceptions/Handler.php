<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Route;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Exceptions that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Inputs that are never flashed for validation.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception callbacks.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Integrate logging or telemetry here if needed.
        });
    }

    /**
     * Customize responses for specific errors.
     */
    public function render($request, Throwable $e)
    {
        // Redirect to login when the session expires (CSRF token mismatch).
        if ($e instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('common.session_expired')], 419);
            }
            $loginUrl = Route::has('login') ? route('login') : url('/login');
            return redirect()->to($loginUrl)
                ->with('status', __('common.session_expired_prompt'));
        }

        // Handle HTTP 419 responses explicitly.
        if ($e instanceof HttpException && $e->getStatusCode() === 419) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('common.session_expired')], 419);
            }
            $loginUrl = Route::has('login') ? route('login') : url('/login');
            return redirect()->to($loginUrl)
                ->with('status', __('common.session_expired_prompt'));
        }

        return parent::render($request, $e);
    }
}
