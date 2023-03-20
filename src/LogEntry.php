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

use Apix\Log\Format\FormatInterface;
use Apix\Log\Format\Standard;
use InvalidArgumentException;

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

    public readonly FormatInterface $format;

    /**
     * Constructor.
     *
     * @param mixed           $level   error level
     * @param string          $message message
     * @param mixed[]         $context context
     * @param FormatInterface $format  the formatter
     */
    public function __construct(mixed $level, string $message, array $context = [], ?FormatInterface $format = null)
    {
        $this->timestamp = time();

        if (\gettype($level) === 'string') {
            $this->name = $level;
            $this->levelCode = Logger::getLevelCode($level);
        } elseif (\gettype($level) === 'integer') {
            $this->name = Logger::getLevelName($level);
            $this->levelCode = $level;
        } else {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid level', \gettype($level)));
        }

        $this->message = $message;
        $this->context = $context;
        $this->format = $format ?? new Standard();
    }

    /**
     * Returns the formatted string for this log entry.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->format->format($this);
    }
}
