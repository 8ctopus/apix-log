<?php

declare(strict_types=1);

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Logger;

use Apix\Log\Format\FormatInterface;
use Apix\Log\Format\Standard;
use Apix\Log\LogEntry;
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
    protected int $minLevel = 7;

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
    protected array $deferredLogs = [];

    /**
     * Flush deferred logs when deferred array reaches count.
     *
     * @var null|int
     */
    protected ?int $deferredTrigger;

    /**
     * Holds the log formatter.
     *
     * @var FormatInterface
     */
    protected FormatInterface $format;

    /**
     * Minimum level logged.
     *
     * @var int
     */
    protected int $minLevelLogged = 7;

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
     * @param string $levelName the name of a PSR-3 level
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public static function getLevelCode(string $levelName) : int
    {
        $levelCode = array_search($levelName, static::$levels, true);
        if (false === $levelCode) {
            throw new InvalidArgumentException(
                sprintf('Invalid log level "%s"', $levelName)
            );
        }

        return (int) $levelCode;
    }

    /**
     * Gets the level name.
     *
     * @param int $levelCode PSR-3 level
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function getLevelName(int $levelCode) : string
    {
        return static::$levels[$levelCode];
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed             $level
     * @param string|Stringable $message
     * @param mixed[]           $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        $this->process(new LogEntry($level, (string) $message, $context));
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
        if ($this->minLevelLogged > $log->levelCode) {
            $this->minLevelLogged = $log->levelCode;
        }

        if ($this->empty) {
            $this->empty = false;
        }

        if ($this->deferred) {
            $this->deferredLogs[] = $log;

            if (isset($this->deferredTrigger) && \count($this->deferredLogs) >= $this->deferredTrigger) {
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
     * @param int $levelCode
     *
     * @return bool
     */
    public function isHandling(int $levelCode) : bool
    {
        return $this->minLevel >= $levelCode;
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
        $this->minLevel = self::getLevelCode(strtolower($name));
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
        return $this->minLevel;
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
        $this->cascading = $bool;

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
        $this->deferred = $bool;

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
        $this->deferredTrigger = $value;

        return $this;
    }

    /**
     * Returns all the deferred logs.
     *
     * @return LogEntry[]
     */
    public function getDeferredLogs() : array
    {
        return $this->deferredLogs;
    }

    /**
     * Process accumulated deferred logs.
     */
    public function flushDeferredLogs() : void
    {
        if ($this->deferred && !empty($this->deferredLogs)) {
            $format = $this->getFormat();
            $messages = '';

            foreach ($this->deferredLogs as $log) {
                $messages .= $format->format($log);
            }

            $this->write($messages);
            $this->deferredLogs = [];
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
     *
     * @param FormatInterface $format
     *
     * @return self
     */
    public function setFormat(FormatInterface $format) : self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Returns the current log formatter.
     *
     * @return FormatInterface
     */
    public function getFormat() : FormatInterface
    {
        if (!isset($this->format)) {
            $this->setFormat(new Standard());
        }

        return $this->format;
    }

    /**
     * Gets min level logged.
     *
     * @return int
     */
    public function getMinLevelLogged() : int
    {
        return $this->minLevelLogged;
    }

    /**
     * Check if log is empty.
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->empty;
    }
}
