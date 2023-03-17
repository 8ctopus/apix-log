<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

use Apix\Log\tests\Logger\TestCase;
use Exception;

/**
 * @internal
 *
 * @coversNothing
 */
final class FunctionalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * This must return the log messages in order with a simple formatting: "<LOG LEVEL> <MESSAGE>".
     *
     * Example ->error('Foo') would yield "error Foo"
     *
     * @param Logger\Runtime $logger
     * @param bool           $deferred
     *
     * @return LogEntry[]
     */
    public function getLogs(Logger\Runtime $logger, bool $deferred = false) : array
    {
        $lines = $logger->getItems();

        if ($deferred) {
            $lines = explode(
                $logger->getLogFormatter()->separator,
                $lines[0]
            );
        }

        return TestCase::normalizeLogs($lines);
    }

    public function testUsages() : void
    {
        // basic usage
        $urgent_logger = new Logger\Runtime();

        // catch logs >= to `critical`
        $urgent_logger->setMinLevel('critical');

        $urgent_logger->alert(
            'Running out of {items} {left} left, recharge: {recharge} {resource}',
            [
                'items' => 'beers',
                'left' => 5,
                'recharge' => true,
                'resource' => tmpfile(),
            ]
        );

        // Advanced usage
        $app_logger = new Logger\Runtime();

        $app_logger->setMinLevel('warning')
            ->setCascading(false)
            ->setDeferred(true);

        // The main logger object (injecting the previous loggers/buckets)
        $logger = new Logger([$urgent_logger, $app_logger]);

        $debug_logger = new Logger\Runtime();
        $debug_logger->setMinLevel('debug');

        $logger->add($debug_logger);

        // handled by both $urgent_logger & $app_logger
        $e = new Exception('Boo!');
        $logger->critical(
            'OMG saw {bad-exception}',
            ['bad-exception' => $e]
        );

        // handled by $app_logger
        $logger->error($e); // push an object (or array) directly

        // handled by $debug_logger
        $logger->info('Something happened -> {abc}', ['abc' => ['xyz']]);

        // -- All the assertions --

        $urgent_logs = $this->getLogs($urgent_logger);

        static::assertSame('alert Running out of beers 5 left, recharge: true [type: resource]' . PHP_EOL, $urgent_logs[0]);

        $prefixException = version_compare(PHP_VERSION, '7.0.0-dev', '>=')
                ? 'Exception: Boo! in '
                : "exception 'Exception' with message 'Boo!' in ";

        static::assertStringStartsWith(
            'critical OMG saw ' . $prefixException,
            $urgent_logs[1]
        );

        //$app_logger->getLogFormatter()->separator = PHP_EOL . '~' . PHP_EOL;
        // just to ensure deferred logs are written
        $app_logger->__destruct();

        $app_logs = $this->getLogs($app_logger, true);

        static::assertStringStartsWith(
            'critical OMG saw ' . $prefixException,
            $app_logs[0]
        );

        static::assertStringStartsWith(
            'error ' . $prefixException,
            $app_logs[2]
        );

        static::assertSame(
            ['info Something happened -> ["xyz"]' . PHP_EOL],
            $this->getLogs($debug_logger)
        );
    }
}
