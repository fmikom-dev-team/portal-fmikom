<?php

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureApprovalAccess;
use App\Http\Middleware\EnsureDosenAccess;
use App\Http\Middleware\EnsureFastUserAccess;
use App\Http\Middleware\EnsureStaffAccess;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'admin.access'   => EnsureAdminAccess::class,
            'approval.access'=> EnsureApprovalAccess::class,
            'dosen.access'   => EnsureDosenAccess::class,
            'fast.user'      => EnsureFastUserAccess::class,
            'staff.access'   => EnsureStaffAccess::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            AuthenticationException::class,
            AuthorizationException::class,
            ValidationException::class,
            HttpException::class,
        ]);

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            $status = $exception->getStatusCode();
            $expectsHtml = ! $request->expectsJson() && ! $request->wantsJson();

            if (! $expectsHtml || ! in_array($status, [403, 404, 419, 429, 500, 503], true)) {
                return null;
            }

            if ($status === 419) {
                return back()->with('error', 'Sesi Anda telah berakhir. Silakan coba lagi.');
            }

            return Inertia::render('Error', [
                'status' => $status,
                'message' => match ($status) {
                    403 => 'Anda tidak memiliki akses ke halaman ini.',
                    404 => 'Halaman yang Anda tuju tidak ditemukan.',
                    429 => 'Terlalu banyak permintaan. Silakan tunggu sebentar lalu coba lagi.',
                    500 => 'Terjadi kesalahan pada server saat memuat halaman.',
                    503 => 'Layanan sedang tidak tersedia untuk sementara.',
                    default => 'Terjadi kesalahan yang tidak terduga.',
                },
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
