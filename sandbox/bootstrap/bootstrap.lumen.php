<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Mathrix\Lumen\JWT\Providers\JWTServiceProvider;

$app = new Application(dirname(__DIR__));
$app->instance('path.storage', dirname(__DIR__) . '/storage');
$app->register(JWTServiceProvider::class);

return $app;
