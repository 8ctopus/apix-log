<?php

declare(strict_types=1);

namespace Apix\Log\Format;

use Apix\Log\LogEntry;

class StandardColored extends Standard
{
    use Colored;

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

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Formats the given log entry.
     *
     * @param LogEntry $log the log entry to format
     *
     * @return string
     */
    public function format(LogEntry $log) : string
    {
        return sprintf(
            "[%s] \033[01;{$this->colors[$log->name]}m%s %s\033[0m",
            date('Y-m-d H:i:s', $log->timestamp),
            strtoupper($log->name),
            self::interpolate($log->message, $log->context)
        ) . $this->separator;
    }
}
