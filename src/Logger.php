<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

use Apix\Log\Logger\AbstractLogger;
use Psr\Log\InvalidArgumentException;

/**
 * Minimalist logger implementing PSR-3 relying on PHP's error_log().
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Logger extends AbstractLogger
{
    /**
     * Holds all the registered loggers as buckets.
     *
     * @var Logger\AbstractLogger[]
     */
    protected array $buckets = [];

    /**
     * Constructor.
     *
     * @param Logger\AbstractLogger[] $loggers
     */
    public function __construct(array $loggers = [])
    {
        foreach ($loggers as $logger) {
            if ($logger instanceof Logger\AbstractLogger) {
                $this->buckets[] = $logger;
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        '"%s" must interface "%s".',
                        \get_class($logger),
                        __NAMESPACE__ . '\Logger\AbstractLogger'
                    )
                );
            }
        }

        $this->sortBuckets();
    }

    /**
     * Processes log (overwrite abstract).
     *
     * @param LogEntry $log the log record to handle
     *
     * @return bool false when not processed
     */
    public function process(LogEntry $log) : bool
    {
        $i = $this->getIndexFirstBucket($log->level_code);

        if (false !== $i) {
            while (
                isset($this->buckets[$i])
                && $this->buckets[$i]->process($log)
            ) {
                ++$i;
            }

            return true;
        }

        return false;
    }

    /**
     * Flush deferred logs.
     */
    public function flushDeferredLogs() : void
    {
        foreach ($this->buckets as $bucket) {
            $bucket->flushDeferredLogs();
        }
    }

    /**
     * Gets the name of the PSR-3 logging level.
     *
     * @param string $level_name
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function getPsrLevelName(string $level_name) : string
    {
        $logLevel = '\Psr\Log\LogLevel::' . \strtoupper($level_name);

        if (!\defined($logLevel)) {
            throw new InvalidArgumentException(
                sprintf('Invalid PSR-3 log level "%s"', $level_name)
            );
        }

        return $level_name;
    }

    /**
     * Adds a logger.
     *
     * @param Logger\AbstractLogger $logger
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function add(Logger\AbstractLogger $logger) : bool
    {
        $this->buckets[] = $logger;

        return $this->sortBuckets();
    }

    /**
     * Returns all the registered log buckets.
     *
     * @return Logger\AbstractLogger[]
     */
    public function getBuckets() : array
    {
        return $this->buckets;
    }

    public function write(LogEntry|string $log) : bool
    {
        throw new ApixLogException('Write must be called on children not on parent');
    }

    /**
     * Checks if any log bucket can handle the given code.
     *
     * @param int $level_code
     *
     * @return false|int
     */
    protected function getIndexFirstBucket(int $level_code)
    {
        foreach ($this->buckets as $key => $logger) {
            if ($logger->isHandling($level_code)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Sorts the log buckets, prioritizes top-down by minimal level.
     * Beware: Exisiting level will be in FIFO order.
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    protected function sortBuckets() : bool
    {
        return \usort(
            $this->buckets,
            function ($a, $b) {
                return $a->getMinLevel() - $b->getMinLevel();
            }
        );
    }
}
