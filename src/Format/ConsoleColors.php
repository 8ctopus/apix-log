<?php

namespace Apix\Log\Format;

use Apix\Log\LogEntry;

class ConsoleColors extends Standard
{
    // https://misc.flogisoft.com/bash/tip_colors_and_formatting
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
        $message = "\033[01;{$this->colors[$log->name]}m{$log->message}\033[0m";

        return sprintf(
            '[%s] %s %s',
            date('Y-m-d H:i:s', $log->timestamp),
            strtoupper($log->name),
            self::interpolate($message, $log->context)
        ) . $this->separator;
    }
}
