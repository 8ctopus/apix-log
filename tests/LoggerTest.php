<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use stdClass;

/**
 * @internal
 *
 * @coversNothing
 */
final class LoggerTest extends \PHPUnit\Framework\TestCase
{
    protected $logger;

    protected function setUp() : void
    {
        $this->logger = new Logger();
    }

    protected function tearDown() : void
    {
        $this->logger = null;
    }

    /**
     * @see http://tools.ietf.org/html/rfc5424#section-6.2.1
     */
    public function testGetLevelCodeSameOrderAsRfc5424()
    {
        static::assertSame(3, Logger::getLevelCode(LogLevel::ERROR));
        static::assertSame(3, Logger::getLevelCode('error'));
    }

    public function testConstructorThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"stdClass" must interface "Apix\Log\Logger\LoggerInterface"');
        new Logger([new stdClass()]);
    }

    public function testConstructor()
    {
        $err_logger = $this->_getMocklogger(['process']);
        $err_logger->setMinLevel(LogLevel::ERROR);

        $crit_logger = $this->_getMocklogger(['process']);
        $crit_logger->setMinLevel(LogLevel::CRITICAL);

        $this->logger = new Logger([$err_logger, $crit_logger]);

        $err_logger->expects(static::once())->method('process');
        $crit_logger->expects(static::once())->method('process');

        $this->logger->error('test err');
        $this->logger->critical('test crit');
    }

    public function testGetLevelCodeThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        Logger::getLevelCode('non-existant');
    }

    public function testgGtPsrLevelName()
    {
        static::assertSame('error', Logger::getPsrLevelName(LogLevel::ERROR));
    }

    public function testGetPsrLevelNameWillThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        Logger::getPsrLevelName('non-existant');
    }

    public function testWriteIsCalled()
    {
        $mock_logger = $this->_getMocklogger(['write']);
        $mock_logger->expects(static::once())
            ->method('write')
        ;

        $this->logger->add($mock_logger);

        $this->logger->info('test');
    }

    public function testLogWillProcess()
    {
        $mock_logger = $this->_getMocklogger(['process']);
        $mock_logger->expects(static::once()) // <-- process IS expected
            ->method('process')
        ;

        $this->logger->add($mock_logger);
        $mock_logger->setMinLevel(LogLevel::WARNING);

        $this->logger->warning('test');
    }

    public function testLogWillNotProcess()
    {
        $mock_logger = $this->_getMocklogger(['process']);
        $mock_logger->setMinLevel(LogLevel::ERROR);

        $mock_logger->expects(static::never()) // <-- process IS NOT expected
            ->method('process')
        ;
        $this->logger->add($mock_logger);

        $this->logger->warning('test');
    }

    /**
     * @see http://tools.ietf.org/html/rfc5424#section-6.2.1
     */
    public function testAddLoggersAreAlwaysSortedbyLevel()
    {
        $buckets = $this->_getFilledInLogBuckets();

        static::assertCount(3, $buckets);

        static::assertSame(2, $buckets[0]->getMinLevel(), 'Critical level');
        static::assertSame(5, $buckets[1]->getMinLevel(), 'Notice level');
        static::assertSame(7, $buckets[2]->getMinLevel(), 'Debug level');
    }

    public function testLogEntriesAreCascasdingDown()
    {
        $buckets = $this->_getFilledInLogBuckets();

        static::assertCount(
            3,
            $buckets[0]->getItems(),
            'Entries at Critical minimal level.'
        );
        static::assertCount(
            6,
            $buckets[1]->getItems(),
            'Entries at Notice minimal level.'
        );
        static::assertCount(
            8,
            $buckets[2]->getItems(),
            'Entries at Debug minimal level.'
        );
    }

    public function testLogEntriesAreNotCascasding()
    {
        $buckets = $this->_getFilledInLogBuckets(false);

        static::assertCount(
            3,
            $buckets[0]->getItems(),
            'Entries at Critical minimal level.'
        );
        static::assertCount(
            3,
            $buckets[1]->getItems(),
            'Entries at Notice minimal level.'
        );
        static::assertCount(
            2,
            $buckets[2]->getItems(),
            'Entries at Debug minimal level.'
        );
    }

    public function testSetCascading()
    {
        static::assertTrue(
            $this->logger->cascading(),
            "The 'cascading' property should be True by default"
        );
        $this->logger->setCascading(false);
        static::assertFalse($this->logger->cascading());
    }

    public function testCascading()
    {
        $dev_log = new Logger\Runtime();
        $dev_log->setMinLevel('debug');

        $app_log = new Logger\Runtime();
        $app_log->setMinLevel('alert');

        $this->logger->add($dev_log);
        $this->logger->add($app_log);

        $buckets = $this->logger->getBuckets();

        $this->logger->alert('cascading');
        static::assertCount(1, $buckets[0]->getItems());
        static::assertCount(1, $buckets[1]->getItems());

        $app_log->setCascading(false)->alert('not-cascading');

        static::assertCount(2, $buckets[0]->getItems(), 'app_log count = 2');
        static::assertCount(1, $buckets[1]->getItems(), 'dev_log count = 1');
    }

    public function testSetDeferred()
    {
        static::assertFalse(
            $this->logger->deferred(),
            "The 'deferred' property should be False by default"
        );
        $this->logger->setDeferred(true);
        static::assertTrue($this->logger->deferred());
    }

    public function testDeferring()
    {
        $logger = new Logger\Runtime();
        $logger->alert('not-deferred');

        static::assertCount(1, $logger->getItems());

        $logger->setDeferred(true)->alert('deferred');

        static::assertCount(1, $logger->getItems());
        static::assertCount(1, $logger->getDeferredLogs());
    }

    public function testDestructIsNotDeferring()
    {
        $logger = new Logger\Runtime();
        $logger->setDeferred(true)->alert('deferred');
        $logger->setDeferred(false)->alert('not-deferred');

        $logger->__destruct();

        static::assertCount(1, $logger->getDeferredLogs());
    }

    public function testSeparatorOfLogFormatter()
    {
        $test = $this->logger->getLogFormatter();
        $test->separator = '~';

        static::assertSame('~', $this->logger->getLogFormatter()->separator);
    }

    public function testInterceptAtAliasSetMinLevel()
    {
        static::assertSame(7, $this->logger->getMinLevel());

        $this->logger->setMinLevel('alert', true);
        static::assertSame(1, $this->logger->getMinLevel());
        static::assertTrue($this->logger->cascading());

        $this->logger->interceptAt('warning', true);
        static::assertSame(4, $this->logger->getMinLevel());
        static::assertFalse($this->logger->cascading());
    }

    protected function _getMocklogger($r = [])
    {
        return !method_exists($this, 'createMock')
                    ? $this->getMock('Apix\Log\Logger\Nil', $r)
                    : $this->getMockBuilder('Apix\Log\Logger\Nil')
                        ->setMethods($r)
                        ->getMock()
        ;
    }

    protected function _getFilledInLogBuckets($cascading = true)
    {
        // The log bucket for everything (starts at 0 Debug level).
        $dev_log = new Logger\Runtime();
        $dev_log->setMinLevel('debug', $cascading);

        // The log bucket for Critical, Alert and Emergency.
        $urgent_log = new Logger\Runtime();
        $urgent_log->setMinLevel('critical', $cascading);

        // The log bucket that starts at Notice level
        $notices_log = new Logger\Runtime();
        $notices_log->setMinLevel('notice', $cascading);

        $this->logger->add($notices_log);
        $this->logger->add($urgent_log);
        $this->logger->add($dev_log);

        // Log some stuff...
        $this->logger->emergency('foo');
        $this->logger->alert('foo');
        $this->logger->critical('foo');

        $this->logger->error('foo');
        $this->logger->warning('foo');
        $this->logger->notice('foo');

        $this->logger->info('foo');
        $this->logger->debug('foo');

        return $this->logger->getBuckets();
    }
}
