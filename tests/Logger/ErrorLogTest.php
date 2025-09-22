<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

declare(strict_types=1);

namespace Tests\Logger;

use Apix\Log\Logger\ErrorLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Stringable;

#[CoversClass(ErrorLog::class)]
final class ErrorLogTest extends TestCase
{
    protected string $file;

    protected function setUp() : void
    {
        $this->file = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'apix-test.log';

        // HHVM support
        // @see: https://github.com/facebook/hhvm/issues/3558
        if (\defined('HHVM_VERSION')) {
            ini_set('log_errors', 'On');
        }

        ini_set('error_log', $this->file);
    }

    protected function tearDown() : void
    {
        file_exists($this->file) && unlink($this->file);
    }

    public function testWriteString() : void
    {
        // phpunit 12 changed error_log behavior
        if (method_exists(self::class, 'expectErrorLog')) {
            self::expectErrorLog();
        }

        $logger = new ErrorLog();

        $message = 'test log';
        $logger->debug($message);

        if (!method_exists(self::class, 'expectErrorLog')) {
            self::assertStringContainsString($message, file_get_contents($this->file) ?? '');
        }
    }

    public function testWriteObject() : void
    {
        $logger = (new ErrorLog($this->file, ErrorLog::FILE))
            ->setDeferred(false);

        $test = new TestClass();
        $logger->debug($test);

        $logger->flushDeferredLogs();

        self::assertStringContainsString((string) $test, file_get_contents($this->file) ?? '');
        self::assertSame($this->file, $logger->getDestination());
    }
}

class TestClass implements Stringable
{
    public function __toString()
    {
        return 'test class';
    }
}
