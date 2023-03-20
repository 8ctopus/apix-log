<?php

declare(strict_types=1);

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Logger;

use Apix\Log\Logger\Stream;
use InvalidArgumentException;
use LogicException;
use ValueError;

/**
 * @internal
 *
 * @covers \Apix\Log\Logger\Stream
 */
final class StreamTest extends \PHPUnit\Framework\TestCase
{
    protected string $dest = 'php://memory';
    protected $stream;
    protected ?Stream $logger;

    protected function setUp() : void
    {
        $this->stream = fopen($this->dest, 'a');
        $this->logger = new Stream($this->stream);
    }

    protected function tearDown() : void
    {
        $this->logger = null;
        $this->stream = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogs() : array
    {
        fseek($this->stream, 0);
        $lines = fread($this->stream, 1000);
        return explode(
            $this->logger->getFormat()->separator,
            $lines,
            -1
        );
    }

    // public function testStreamFromString()
    // {
    //     $logger = new Stream(self::dest, 'a');
    //     self::assertEquals(self::logger, $logger);
    // }

    public function testWrite() : void
    {
        $this->logger->info('test');
        static::assertSame(date('[Y-m-d H:i:s]') . ' INFO test', (string) $this->getLogs()[0]);
    }

    public function testInvalidResource() : void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('The stream "" cannot be created or opened');

        new Stream('.', 'x');
    }

    public function testThrowsInvalidArgumentExceptionWhenFileCannotBeCreated() : void
    {
        self::expectException(ValueError::class);
        self::expectExceptionMessage('Path cannot be empty');
        new Stream('');
    }

    public function testThrowsLogicException() : void
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('The stream resource has been destructed too early');

        $logger = new Stream();
        $logger->__destruct();
        $logger->debug('foo');
    }
}
