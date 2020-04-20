<?php

declare(strict_types=1);

use Laravel\Lumen\Application;

$app = new Application(dirname(__DIR__));
$app->instance('path.storage', dirname(__DIR__) . '/storage');

return $app;
