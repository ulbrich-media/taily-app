<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;

// Keep this file minimal. `composer update` never replaces it, so anything
// configured here would drift from the installed Taily version. Scheduled
// tasks, middleware and CSRF exceptions are registered by Taily itself.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    // Required even without a callback: defines Laravel's default global
    // middleware and the `web` / `api` groups Taily's routes use.
    ->withMiddleware()
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
