<?php

declare(strict_types=1);

namespace Apix\Log\Format;


trait Colored
{
    /**
     * @var array<string, int>
     *
     * @note https://misc.flogisoft.com/bash/tip_colors_and_formatting
     */
    private array $colors = [
        'emergency' => 91,
        'alert' => 91,
        'critical' => 91,
        'error' => 31,
        'warning' => 93,
        'notice' => 32,
        'info' => 39,
        'debug' => 34,
    ];
}
