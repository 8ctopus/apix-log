<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Logger;

use Apix\Log\Logger\ErrorLog;
use Stringable;

/**
 * @internal
 *
 * @covers \Apix\Log\Logger\ErrorLog
 */
final class ErrorLogTest extends \PHPUnit\Framework\TestCase
{
    protected string $dest = 'test';

    protected function setUp() : void
    {
        // HHVM support
        // @see: https://github.com/facebook/hhvm/issues/3558
        if (\defined('HHVM_VERSION')) {
            ini_set('log_errors', 'On');
            ini_set('error_log', $this->dest);
        }

        ini_set('error_log', $this->dest);
    }

    protected function tearDown() : void
    {
        if (file_exists($this->dest)) {
            unlink($this->dest);
        }
    }

    public function testWriteString() : void
    {
        $logger = new ErrorLog();

        $message = 'test log';
        $logger->debug($message);

        $content = file_get_contents($this->dest);

        static::assertStringContainsString($message, $content);
    }

    public function testWriteObject() : void
    {
        $destination = 'test';

        $logger = (new ErrorLog($destination, ErrorLog::FILE))
            ->setDeferred(false);

        $test = new TestClass();
        $logger->debug($test);

        $logger->flushDeferredLogs();

        $content = file_get_contents($this->dest);

        static::assertStringContainsString((string) $test, $content);
        static::assertSame($destination, $logger->getDestination());
    }
}

class TestClass implements Stringable
{
    public function __toString()
    {
        return 'test class';
    }
}
