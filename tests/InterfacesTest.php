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

/**
 * StandardOutput log wrapper (example).
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class StandardOutput extends AbstractLogger implements LoggerInterface
{
    public function write(LogEntry|string $log)
    {
        echo $log;
    }
}

/**
 * A JSON Formatter (example).
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class MyJsonFormatter extends LogFormatter
{
    public $separator = '~';

    public function format(LogEntry $log)
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
    protected $logger;

    protected function setUp() : void
    {
        $this->logger = new StandardOutput();
    }

    protected function tearDown() : void
    {
        $this->logger = null;
    }

    public function testGetLogFormatterReturnsDefaultLogFormatter()
    {
        static::assertInstanceOf(
            '\Apix\Log\LogFormatter',
            $this->logger->getLogFormatter()
        );
    }

    public function testSetLogFormatter()
    {
        $formatter = new MyJsonFormatter();
        $this->logger->setLogFormatter($formatter);
        static::assertSame($this->logger->getLogFormatter(), $formatter);
    }

    public function testLogFormatterInterfaceExample()
    {
        $formatter = new MyJsonFormatter();
        $this->logger->setLogFormatter($formatter);
        $this->logger->error('hello {who}', ['who' => 'world']);

        $this->expectOutputRegex(
            '@\{"timestamp":.*\,"name":"error"\,"level_code":3\,"message":"hello world","context":\{"who":"world"\}\,"formatter":\{"separator":"~"\}\}@'
        );
    }
}
