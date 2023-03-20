<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

use Apix\Log\Format\Standard;

/**
 * Describes a log Entry.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class LogEntry
{
    public int $timestamp;
    public string $name;
    public int $levelCode;
    public string $message;
    public array $context;

    /**
     * Constructor.
     *
     * @param int|string $level   the level
     * @param string     $message the message for this log entry
     * @param mixed[]    $context the contexts for this log entry
     */
    public function __construct(string|int $level, string $message, array $context = [])
    {
        $this->timestamp = time();

        if (\gettype($level) === 'string') {
            $this->name = $level;
            $this->levelCode = Logger::getLevelCode($level);
        } else {
            $this->name = Logger::getLevelName($level);
            $this->levelCode = $level;
        }

        $this->message = $message;
        $this->context = $context;
    }

    /**
     * Returns the formatted string for this log entry.
     *
     * @return string
     */
    public function __toString() : string
    {
        $format = new Standard();
        return $format->format($this);
    }
}
