<?php

declare(strict_types=1);

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests;

use Apix\Log\ApixLogException;
use Apix\Log\Logger;
use Apix\Log\Logger\Stream;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use stdClass;

/**
 * @internal
 *
 * @covers \Apix\Log\Logger
 * @covers \Apix\Log\Logger\AbstractLogger
 */
final class LoggerTest extends \PHPUnit\Framework\TestCase
{
    protected ?Logger $logger;

    protected function setUp() : void
    {
        $this->logger = new Logger();
    }

    protected function tearDown() : void
    {
        $this->logger = null;
    }

    public function testWriteException() : void
    {
        self::expectException(ApixLogException::class);
        $this->logger->write('cannot write');
    }

    /**
     * @see http://tools.ietf.org/html/rfc5424#section-6.2.1
     */
    public function testGetLevelCodeSameOrderAsRfc5424() : void
    {
        self::assertSame(3, Logger::getLevelCode(LogLevel::ERROR));
        self::assertSame(3, Logger::getLevelCode('error'));
    }

    public function testLevelName() : void
    {
        self::assertSame('emergency', Logger::getLevelName(0));
    }

    public function testInvalidLevel() : void
    {
        self::expectException(InvalidArgumentException::class);
        Logger::getLevelCode('not existing level');
    }

    public function testEmpty() : void
    {
        self::assertTrue($this->logger->isEmpty());
    }

    public function testMinimumLevelLogged() : void
    {
        $this->logger->add(new Stream('php://memory', 'a'));

        self::assertSame(7, $this->logger->getMinLevelLogged());

        $this->logger->alert('test');

        self::assertSame(1, $this->logger->getMinLevelLogged());
    }

    public function testConstructorThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"stdClass" must interface "Apix\Log\Logger\AbstractLogger"');
        new Logger([new stdClass()]);
    }

    public function testConstructor() : void
    {
        $err_logger = $this->_getMockLogger(['process']);
        $err_logger->setMinLevel(LogLevel::ERROR);

        $crit_logger = $this->_getMockLogger(['process']);
        $crit_logger->setMinLevel(LogLevel::CRITICAL);

        $this->logger = new Logger([$err_logger, $crit_logger]);

        $err_logger->expects(self::once())->method('process');
        $crit_logger->expects(self::once())->method('process');

        $this->logger->error('test err');
        $this->logger->critical('test crit');
    }

    public function testGetLevelCodeThrows() : void
    {
        $this->expectException(InvalidArgumentException::class);
        Logger::getLevelCode('non-existant');
    }

    public function testgGtPsrLevelName() : void
    {
        self::assertSame('error', Logger::getPsrLevelName(LogLevel::ERROR));
    }

    public function testGetPsrLevelNameWillThrows() : void
    {
        $this->expectException(InvalidArgumentException::class);
        Logger::getPsrLevelName('non-existant');
    }

    public function testWriteIsCalled() : void
    {
        $mock_logger = $this->_getMockLogger(['write']);
        $mock_logger->expects(self::once())
            ->method('write');

        $this->logger->add($mock_logger);

        $this->logger->info('test');
    }

    public function testLogWillProcess() : void
    {
        $mock_logger = $this->_getMockLogger(['process']);
        $mock_logger->expects(self::once()) // <-- process IS expected
            ->method('process');

        $this->logger->add($mock_logger);
        $mock_logger->setMinLevel(LogLevel::WARNING);

        $this->logger->warning('test');
    }

    public function testLogWillNotProcess() : void
    {
        $mock_logger = $this->_getMockLogger(['process']);
        $mock_logger->setMinLevel(LogLevel::ERROR);

        $mock_logger->expects(self::never()) // <-- process IS NOT expected
            ->method('process')
        ;
        $this->logger->add($mock_logger);

        $this->logger->warning('test');
    }

    /**
     * @see http://tools.ietf.org/html/rfc5424#section-6.2.1
     */
    public function testAddLoggersAreAlwaysSortedbyLevel() : void
    {
        $buckets = $this->_getFilledInLogBuckets();

        self::assertCount(3, $buckets);

        self::assertSame(2, $buckets[0]->getMinLevel(), 'Critical level');
        self::assertSame(5, $buckets[1]->getMinLevel(), 'Notice level');
        self::assertSame(7, $buckets[2]->getMinLevel(), 'Debug level');
    }

    public function testLogEntriesAreCascasdingDown() : void
    {
        $buckets = $this->_getFilledInLogBuckets();

        self::assertCount(
            3,
            $buckets[0]->getItems(),
            'Entries at Critical minimal level.'
        );

        self::assertCount(
            6,
            $buckets[1]->getItems(),
            'Entries at Notice minimal level.'
        );

        self::assertCount(
            8,
            $buckets[2]->getItems(),
            'Entries at Debug minimal level.'
        );
    }

    public function testLogEntriesAreNotCascasding() : void
    {
        $buckets = $this->_getFilledInLogBuckets(false);

        self::assertCount(
            3,
            $buckets[0]->getItems(),
            'Entries at Critical minimal level.'
        );

        self::assertCount(
            3,
            $buckets[1]->getItems(),
            'Entries at Notice minimal level.'
        );

        self::assertCount(
            2,
            $buckets[2]->getItems(),
            'Entries at Debug minimal level.'
        );
    }

    public function testSetCascading() : void
    {
        self::assertTrue(
            $this->logger->cascading(),
            "The 'cascading' property should be True by default"
        );

        $this->logger->setCascading(false);
        self::assertFalse($this->logger->cascading());
    }

    public function testCascading() : void
    {
        $dev = new Logger\Runtime();
        $dev->setMinLevel('debug');

        $app = new Logger\Runtime();
        $app->setMinLevel('alert');

        $this->logger->add($dev);
        $this->logger->add($app);

        $buckets = $this->logger->getBuckets();

        $this->logger->alert('cascading');
        self::assertCount(1, $buckets[0]->getItems());
        self::assertCount(1, $buckets[1]->getItems());

        $app->setCascading(false)->alert('not-cascading');

        self::assertCount(2, $buckets[0]->getItems(), 'app_log count = 2');
        self::assertCount(1, $buckets[1]->getItems(), 'dev_log count = 1');
    }

    public function testSetDeferred() : void
    {
        self::assertFalse(
            $this->logger->deferred(),
            "The 'deferred' property should be False by default"
        );

        $this->logger->setDeferred(true);

        $this->logger->setDeferredTrigger(100);

        self::assertTrue($this->logger->deferred());
    }

    public function testDeferring() : void
    {
        $logger = new Logger\Runtime();
        $logger->alert('not-deferred');

        self::assertCount(1, $logger->getItems());

        $logger->setDeferred(true)->alert('deferred');

        self::assertCount(1, $logger->getItems());
        self::assertCount(1, $logger->getDeferredLogs());
    }

    public function testDestructIsNotDeferring() : void
    {
        $logger = new Logger\Runtime();
        $logger->setDeferred(true)->alert('deferred');
        $logger->setDeferred(false)->alert('not-deferred');

        $logger->__destruct();

        self::assertCount(1, $logger->getDeferredLogs());
    }

    public function testInterceptAtAliasSetMinLevel() : void
    {
        self::assertSame(7, $this->logger->getMinLevel());

        $this->logger->setMinLevel('alert', true);
        self::assertSame(1, $this->logger->getMinLevel());
        self::assertTrue($this->logger->cascading());

        $this->logger->interceptAt('warning', true);
        self::assertSame(4, $this->logger->getMinLevel());
        self::assertFalse($this->logger->cascading());
    }

    protected function _getMockLogger(array $methods = [])
    {
        return $this->getMockBuilder('Apix\Log\Logger\Nil')
            ->onlyMethods($methods)
            ->getMock();
    }

    protected function _getFilledInLogBuckets(bool $cascading = true) : array
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
