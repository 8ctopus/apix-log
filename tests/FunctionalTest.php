<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests;

use Apix\Log\Logger;
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
                $logger->getFormat()->separator,
                $lines[0]
            );
        }

        return $lines;
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

        // advanced usage
        $app_logger = (new Logger\Runtime())
            ->setMinLevel('warning')
            ->setCascading(false)
            ->setDeferred(true);

        $logger = new Logger([$urgent_logger, $app_logger]);

        $debug_logger = (new Logger\Runtime())
            ->setMinLevel('debug');

        $logger->add($debug_logger);

        // handled by both $urgent_logger & $app_logger
        $e = new Exception('Boo!');

        $logger->critical('OMG saw {bad-exception}', ['bad-exception' => $e]);

        // handled by $app_logger
        // push an object (or array) directly
        $logger->error($e);

        // handled by $debug_logger
        $logger->info('Something happened -> {abc}', ['abc' => ['xyz']]);

        $urgent_logs = $this->getLogs($urgent_logger);

        $date = date('[Y-m-d H:i:s]');

        static::assertSame($date . ' ALERT Running out of beers 5 left, recharge: true [type: resource]' . PHP_EOL, $urgent_logs[0]);

        $prefixException = 'Exception: Boo! in ';

        static::assertStringStartsWith($date . ' CRITICAL OMG saw ' . $prefixException, $urgent_logs[1]);

        $app_logger->flushDeferredLogs();

        $app_logs = $this->getLogs($app_logger, false);

        static::assertStringStartsWith($date . ' CRITICAL OMG saw ' . $prefixException, $app_logs[0]);

        static::assertStringContainsString($date . ' ERROR ' . $prefixException, $app_logs[0]);

        static::assertSame([$date . ' INFO Something happened -> ["xyz"]' . PHP_EOL], $this->getLogs($debug_logger));
    }
}
