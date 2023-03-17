<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Logger;

use Apix\Log\LogEntry;

/**
 * @internal
 *
 * @covers \Apix\Log\LogEntry
 */
final class LogEntryTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor() : void
    {
        $entry = new LogEntry('emergency', 'test', ['a' => 1, 'b' => false]);
        static::assertSame(date('[Y-m-d H:i:s]') . ' EMERGENCY test' . PHP_EOL, (string) $entry);

        $entry = new LogEntry(0, 'test', ['a' => 1, 'b' => false]);
        static::assertSame(date('[Y-m-d H:i:s]') . ' EMERGENCY test' . PHP_EOL, (string) $entry);
    }
}
