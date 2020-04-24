<?php

declare(strict_types=1);

return [
    'default' => 'memory',
    'stores'  => [
        'memory' => [
            'driver'    => 'array',
            'serialize' => false,
        ],
    ],
];
