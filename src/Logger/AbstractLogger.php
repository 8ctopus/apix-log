<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Logger;

use Apix\Log\LogEntry;
use Apix\Log\LogFormatter;
use Psr\Log\AbstractLogger as PsrAbstractLogger;
use Psr\Log\InvalidArgumentException;
use Stringable;

/**
 * Abstract class.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
abstract class AbstractLogger extends PsrAbstractLogger implements LoggerInterface
{
    /**
     * The PSR-3 logging levels.
     *
     * @var string[]
     */
    protected static array $levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    /**
     * Holds the minimal level index supported by this logger.
     *
     * @var int
     */
    protected int $min_level = 7;

    /**
     * Whether this logger will cascade downstream.
     *
     * @var bool
     */
    protected bool $cascading = true;

    /**
     * Whether this logger will be deferred (push the logs at destruct time).
     *
     * @var bool
     */
    protected bool $deferred = false;

    /**
     * Holds the deferred logs.
     *
     * @var LogEntry[]
     */
    protected array $deferred_logs = [];

    /**
     * Flush deferred logs when deferred array reaches count.
     *
     * @var null|int
     */
    protected ?int $deferred_trigger;

    /**
     * Holds the log formatter.
     *
     * @var LogFormatter
     */
    protected LogFormatter $log_formatter;

    /**
     * Minimum level logged.
     *
     * @var int
     */
    protected int $min_level_logged = 7;

    /**
     * Whether or not log is empty.
     *
     * @var bool
     */
    protected bool $empty = true;

    /**
     * Process any accumulated deferred log if there are any.
     */
    final public function __destruct()
    {
        $this->flushDeferredLogs();
        $this->close();
    }

    /**
     * Gets the named level code.
     *
     * @param string $level_name the name of a PSR-3 level
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public static function getLevelCode(string $level_name) : int
    {
        $level_code = array_search($level_name, static::$levels, true);
        if (false === $level_code) {
            throw new InvalidArgumentException(
                sprintf('Invalid log level "%s"', $level_name)
            );
        }

        return (int) $level_code;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        $entry = new LogEntry($level, $message, $context);
        $entry->setFormatter($this->getLogFormatter());
        $this->process($entry);
    }

    /**
     * Processes the given log.
     *
     * @param LogEntry $log the log entry to process
     *
     * @return bool whether this logger cascades downstream
     */
    public function process(LogEntry $log) : bool
    {
        if ($this->min_level_logged > $log->level_code) {
            $this->min_level_logged = $log->level_code;
        }

        if ($this->empty) {
            $this->empty = false;
        }

        if ($this->deferred) {
            $this->deferred_logs[] = $log;

            if (isset($this->deferred_trigger) && \count($this->deferred_logs) >= $this->deferred_trigger) {
                $this->flushDeferredLogs();
            }
        } else {
            $this->write($log);
        }

        return $this->cascading;
    }

    /**
     * Checks whether the given level code is handled by this logger.
     *
     * @param int $level_code
     *
     * @return bool
     */
    public function isHandling(int $level_code) : bool
    {
        return $this->min_level >= $level_code;
    }

    /**
     * Sets the minimal level at which this logger will be triggered.
     *
     * @param string    $name
     * @param bool|true $cascading should the logs continue pass that level
     *
     * @return self
     */
    public function setMinLevel(string $name, bool $cascading = true) : self
    {
        $this->min_level = self::getLevelCode(strtolower($name));
        $this->cascading = (bool) $cascading;

        return $this;
    }

    /**
     * Alias to self::setMinLevel().
     *
     * @param string     $name
     * @param bool|false $blocking should the logs continue pass that level
     *
     * @return self
     */
    public function interceptAt(string $name, bool $blocking = false) : self
    {
        return $this->setMinLevel($name, !$blocking);
    }

    /**
     * Returns the minimal level at which this logger will be triggered.
     *
     * @return int
     */
    public function getMinLevel() : int
    {
        return $this->min_level;
    }

    /**
     * Sets whether to enable/disable cascading.
     *
     * @param bool $bool
     *
     * @return self
     */
    public function setCascading(bool $bool) : self
    {
        $this->cascading = (bool) $bool;

        return $this;
    }

    /**
     * Get cascading property.
     *
     * @return bool
     */
    public function cascading() : bool
    {
        return $this->cascading;
    }

    /**
     * Sets whether to enable/disable log deferring.
     *
     * @param bool $bool
     *
     * @return self
     */
    public function setDeferred(bool $bool) : self
    {
        $this->deferred = (bool) $bool;

        return $this;
    }

    /**
     * Get deferred property.
     *
     * @return bool
     */
    public function deferred() : bool
    {
        return $this->deferred;
    }

    /**
     * Sets deferred trigger.
     *
     * @param null|int $value
     *
     * @return self
     */
    public function setDeferredTrigger(?int $value) : self
    {
        $this->deferred_trigger = $value;

        return $this;
    }

    /**
     * Returns all the deferred logs.
     *
     * @return LogEntry[]
     */
    public function getDeferredLogs() : array
    {
        return $this->deferred_logs;
    }

    /**
     * Process any accumulated deferred log if there are any.
     */
    public function flushDeferredLogs() : void
    {
        if ($this->deferred && !empty($this->deferred_logs)) {
            $messages = array_map(
                function ($log) {
                    return (string) $log;
                },
                $this->deferred_logs
            );

            $formatter = $this->getLogFormatter();

            $messages = implode($formatter->separator, $messages) . $formatter->separator;

            $this->write($messages);

            // cleanup array
            $this->deferred_logs = [];
            // return $this->formatter->format($this);
        }
    }

    /**
     * Closes the logger ~ acts as the last resort garbage collector.
     *
     * This method is called last at __destruct() time.
     */
    public function close() : void
    {
        // empty
    }

    /**
     * Sets a log formatter.
     */
    public function setLogFormatter(LogFormatter $formatter) : void
    {
        $this->log_formatter = $formatter;
    }

    /**
     * Returns the current log formatter.
     *
     * @return LogFormatter
     */
    public function getLogFormatter() : LogFormatter
    {
        if (!isset($this->log_formatter)) {
            $this->setLogFormatter(new LogFormatter());
        }

        return $this->log_formatter;
    }

    /**
     * Gets min level logged.
     */
    public function getMinLevelLogged() : int
    {
        return $this->min_level_logged;
    }

    /**
     * Check if log is empty.
     */
    public function isEmpty() : bool
    {
        return $this->empty;
    }
}
