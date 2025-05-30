<?php

declare(strict_types=1);

namespace Apix\Log\Format;

use Apix\Log\LogEntry;

class MinimalColored extends Standard
{
    use Colored;

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
        return sprintf("\033[01;{$this->colors[$log->name]}m%s\033[0m", self::interpolate($log->message, $log->context)) . $this->separator;
    }
}
