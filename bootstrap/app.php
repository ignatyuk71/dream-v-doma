<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Вибір env-файла за доменом:
 * - dream-v-doma.com.ua  -> .env.comua
 * - все інше (у т.ч. .site) -> .env.site
 * Можна примусово задати APP_ENV_FILE в середовищі/CLI.
 */
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$envFile = $_SERVER['APP_ENV_FILE']
    ?? (str_contains($host, 'dream-v-doma.com.ua/uk/') ? '.env.comua' : '.env.site');

return Application::configure(basePath: dirname(__DIR__))
    ->loadEnvironmentFrom($envFile) // <— важливо
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
