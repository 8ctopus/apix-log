<?php

declare(strict_types=1);

namespace Apix\Log\tests\Format;

use Apix\Log\Format\StandardColored;
use Apix\Log\Logger\Runtime;

/**
 * @internal
 *
 * @covers \Apix\Log\Format\StandardColored
 */
final class StandardColoredTest extends \PHPUnit\Framework\TestCase
{
    protected ?Runtime $logger;

    protected function setUp() : void
    {
        $this->logger = new Runtime();
    }

    protected function tearDown() : void
    {
        $this->logger = null;
    }

    public function testGetFormatReturnsStandardFormat() : void
    {
        self::assertInstanceOf('\Apix\Log\Format\Standard', $this->logger->getFormat());
    }

    public function testSetFormat() : void
    {
        $format = new StandardColored();
        $this->logger->setFormat($format);

        self::assertSame($this->logger->getFormat(), $format);
    }

    public function testFormatInterfaceExample() : void
    {
        $format = new StandardColored();
        $this->logger->setFormat($format);

        $this->logger->error('hello {who}', ['who' => 'world']);
        self::assertSame(date('[Y-m-d H:i:s]') . ' [01;31mERROR hello world[0m' . PHP_EOL, $this->logger->getItems()[0]);
    }
}
