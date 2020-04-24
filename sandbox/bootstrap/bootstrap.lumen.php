<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerConstract;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Lumen\Application;

$app = new Application(dirname(__DIR__));

$app->withFacades();

$app->configure('app');
$app->configure('auth');
$app->configure('cache');
$app->configure('database');

$app->singleton(ConsoleKernelContract::class, ConsoleKernel::class);
$app->singleton(ExceptionHandlerConstract::class, ExceptionHandler::class);

// Patches
$app->instance('path.storage', dirname(__DIR__) . '/storage');

return $app;
