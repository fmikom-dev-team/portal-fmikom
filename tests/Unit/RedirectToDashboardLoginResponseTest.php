<?php

use App\Http\Responses\RedirectToDashboardLoginResponse;
use App\Http\Responses\RedirectToDashboardTwoFactorLoginResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class);

test('login response always redirects to dashboard even when intended url points to admin history', function () {
    $request = Request::create('/login', 'POST');
    $request->setLaravelSession(app('session')->driver());
    $request->session()->put('url.intended', '/admin/history');

    $response = (new RedirectToDashboardLoginResponse())->toResponse($request);

    expect($response->getTargetUrl())->toBe(route('dashboard', absolute: false));
});

test('two factor login response redirects to dashboard for html requests and keeps json behaviour', function () {
    $htmlRequest = Request::create('/two-factor-challenge', 'POST');
    $htmlRequest->setLaravelSession(app('session')->driver());
    $htmlResponse = (new RedirectToDashboardTwoFactorLoginResponse())->toResponse($htmlRequest);

    expect($htmlResponse->getTargetUrl())->toBe(route('dashboard', absolute: false));

    $jsonRequest = Request::create('/two-factor-challenge', 'POST', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
    ]);
    $jsonRequest->setLaravelSession(app('session')->driver());
    $jsonResponse = (new RedirectToDashboardTwoFactorLoginResponse())->toResponse($jsonRequest);

    expect($jsonResponse->getStatusCode())->toBe(204);
});
