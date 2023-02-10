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
abstract class AbstractLogger extends PsrAbstractLogger
{
    /**
     * The PSR-3 logging levels.
     *
     * @var array
     */
    protected static $levels = [
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
    protected $min_level = 7;

    /**
     * Whether this logger will cascade downstream.
     *
     * @var bool
     */
    protected $cascading = true;

    /**
     * Whether this logger will be deferred (push the logs at destruct time).
     *
     * @var bool
     */
    protected $deferred = false;

    /**
     * Holds the deferred logs.
     *
     * @var array
     */
    protected $deferred_logs = [];

    /**
     * Flush deferred logs when deferred array reaches count.
     *
     * @var null|int
     */
    protected $deferred_trigger;

    /**
     * Holds the log formatter.
     *
     * @var null|LogFormatter
     */
    protected $log_formatter;

    /**
     * Holds the logger options (useful to set default options).
     *
     * @var array
     */
    protected $options = [];

    /**
     * Minimum level logged.
     *
     * @var int
     */
    protected $min_level_logged = 7;

    /**
     * Whether or not log is empty.
     *
     * @var bool
     */
    protected $empty = true;

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
    public static function getLevelCode(string $level_name)
    {
        $level_code = array_search($level_name, static::$levels, true);
        if (false === $level_code) {
            throw new InvalidArgumentException(
                sprintf('Invalid log level "%s"', $level_name)
            );
        }

        return $level_code;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, Stringable|string $message, array $context = []) : void
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
    public function process(LogEntry $log)
    {
        if ($this->min_level_logged > $log->level_code) {
            $this->min_level_logged = $log->level_code;
        }

        if ($this->empty) {
            $this->empty = false;
        }

        if ($this->deferred) {
            $this->deferred_logs[] = $log;

            if ($this->deferred_trigger && \count($this->deferred_logs) >= $this->deferred_trigger) {
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
    public function isHandling(int $level_code)
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
    public function setMinLevel(string $name, bool $cascading = true)
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
    public function interceptAt(string $name, bool $blocking = false)
    {
        return $this->setMinLevel($name, !$blocking);
    }

    /**
     * Returns the minimal level at which this logger will be triggered.
     *
     * @return int
     */
    public function getMinLevel()
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
    public function setCascading(bool $bool)
    {
        $this->cascading = (bool) $bool;

        return $this;
    }

    /**
     * Get cascading property.
     *
     * @return bool
     */
    public function cascading()
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
    public function setDeferred(bool $bool)
    {
        $this->deferred = (bool) $bool;

        return $this;
    }

    /**
     * Get deferred property.
     *
     * @return bool
     */
    public function deferred()
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
    public function setDeferredTrigger(?int $value)
    {
        $this->deferred_trigger = $value;

        return $this;
    }

    /**
     * Returns all the deferred logs.
     *
     * @return array
     */
    public function getDeferredLogs()
    {
        return $this->deferred_logs;
    }

    /**
     * Process any accumulated deferred log if there are any.
     */
    public function flushDeferredLogs()
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
    public function close()
    {
        // empty
    }

    /**
     * Sets a log formatter.
     */
    public function setLogFormatter(LogFormatter $formatter)
    {
        $this->log_formatter = $formatter;
    }

    /**
     * Returns the current log formatter.
     *
     * @return LogFormatter
     */
    public function getLogFormatter()
    {
        if (!$this->log_formatter) {
            $this->setLogFormatter(new LogFormatter());
        }

        return $this->log_formatter;
    }

    /**
     * Sets and merges the options for this logger, overriding any default.
     */
    public function setOptions(array $options = null)
    {
        if (null !== $options) {
            $this->options = $options + $this->options;
        }
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
