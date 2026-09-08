<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    // Requires an operator-configured cron entry invoking `schedule:run`
    // every minute — see the "Scheduled Tasks" section of this project's
    // README. No queue worker involved, per Taily's shared-hosting
    // constraint (ADR-012 Constraint 1 in the taily repo).
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('contracts:process-reminders')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'internal/invitations/*',
            'internal/inspect/*/submit',
        ]);
        $middleware->remove([
            ConvertEmptyStringsToNull::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
