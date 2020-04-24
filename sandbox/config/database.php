<?php

declare(strict_types=1);

return [
    'default'     => 'sqlite',
    'migrations'  => 'migrations',
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ],
    ],
];
