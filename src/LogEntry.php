<?php

declare(strict_types=1);

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
    public readonly int $timestamp;
    public readonly string $name;
    public readonly int $levelCode;
    public string $message;

    /**
     * @var mixed[]
     */
    public readonly array $context;

    /**
     * Constructor.
     *
     * @param int|string $level   error level
     * @param string     $message message
     * @param mixed[]    $context context
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
