<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

/**
 * Describes a log Entry.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class LogEntry
{
    /**
     * Holds this log timestamp.
     *
     * @var int
     */
    public int $timestamp;

    /**
     * Holds this log name.
     *
     * @var string
     */
    public string $name;

    /**
     * Holds this log level code.
     *
     * @var int
     */
    public int $level_code;

    /**
     * Holds this log message.
     *
     * @var string
     */
    public string $message;

    /**
     * Holds this log context.
     *
     * @var array
     */
    public array $context;

    /**
     * Holds this log formatter.
     *
     * @var LogFormatter
     */
    public LogFormatter $formatter;

    /**
     * Constructor.
     *
     * @param string $name    the level name
     * @param string $message the message for this log entry
     * @param array  $context the contexts for this log entry
     */
    public function __construct(string $name, string $message, array $context = [])
    {
        $this->timestamp = time();

        $this->name = $name;
        $this->level_code = Logger::getLevelCode($name);

        // Message is not a string let assume it is a context -- and permute.
        if (!\is_string($message)) {
            $context = ['ctx' => $message];
            $message = '{ctx}';
        }
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * Returns the formated string for this log entry.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->formatter->format($this);
    }

    public function setFormatter(LogFormatter $formatter) : void
    {
        $this->formatter = $formatter;
    }
}
