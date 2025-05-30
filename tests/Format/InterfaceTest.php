<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

declare(strict_types=1);

namespace Apix\Log\tests\Format;

use Apix\Log\Format\Standard;
use Apix\Log\LogEntry;
use Apix\Log\Logger\Runtime;

/**
 * @internal
 *
 * @coversNothing
 */
final class InterfaceTest extends \PHPUnit\Framework\TestCase
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
        $formatter = new MyJsonFormatter();
        $this->logger->setFormat($formatter);
        self::assertSame($this->logger->getFormat(), $formatter);
    }

    public function testFormatInterfaceExample() : void
    {
        $formatter = new MyJsonFormatter();
        $this->logger->setFormat($formatter);
        $this->logger->error('hello {who}', ['who' => 'world']);

        self::assertMatchesRegularExpression(
            '@\{"timestamp":.*\,"name":"error"\,"levelCode":3\,"message":"hello world","context":\{"who":"world"\}\}@',
            $this->logger->getItems()[0]
        );
    }
}

/**
 * A JSON Formatter (example).
 */
class MyJsonFormatter extends Standard
{
    public function __construct()
    {
        parent::__construct();
    }

    public function format(LogEntry $log) : string
    {
        // Interpolate the context values into the message placeholders.
        $log->message = self::interpolate($log->message, $log->context);

        return json_encode($log);
    }
}
