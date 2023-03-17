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
use Apix\Log\Logger\LoggerInterface;
use Apix\Log\Format\Standard;

/**
 * Standard output log wrapper (example).
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class StandardOutput extends AbstractLogger implements LoggerInterface
{
    public function write(LogEntry|string $log) : bool
    {
        if ($log instanceof LogEntry) {
            $log = $this->getFormat()->format($log);
        }

        echo $log;
        return true;
    }
}

/**
 * A JSON Formatter (example).
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class MyJsonFormatter extends Standard
{
    public function __construct()
    {
        parent::__construct('~');
    }

    public function format(LogEntry $log) : string
    {
        // Interpolate the context values into the message placeholders.
        $log->message = self::interpolate($log->message, $log->context);

        return json_encode($log);
    }
}

/**
 * @internal
 *
 * @coversNothing
 */
final class InterfacesTest extends \PHPUnit\Framework\TestCase
{
    protected ?StandardOutput $logger;

    protected function setUp() : void
    {
        $this->logger = new StandardOutput();
    }

    protected function tearDown() : void
    {
        $this->logger = null;
    }

    public function testGetFormatReturnsStandardFormat() : void
    {
        static::assertInstanceOf(
            '\Apix\Log\Format\Standard',
            $this->logger->getFormat()
        );
    }

    public function testSetFormat() : void
    {
        $formatter = new MyJsonFormatter();
        $this->logger->setFormat($formatter);
        static::assertSame($this->logger->getFormat(), $formatter);
    }

    public function testFormatInterfaceExample() : void
    {
        $formatter = new MyJsonFormatter();
        $this->logger->setFormat($formatter);
        $this->logger->error('hello {who}', ['who' => 'world']);

        $this->expectOutputRegex(
            '@\{"timestamp":.*\,"name":"error"\,"levelCode":3\,"message":"hello world","context":\{"who":"world"\}\}@'
        );
    }
}
