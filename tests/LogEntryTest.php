<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

declare(strict_types=1);

namespace Tests;

use Apix\Log\LogEntry;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Apix\Log\LogEntry
 */
final class LogEntryTest extends TestCase
{
    public function testConstructor() : void
    {
        $entry = new LogEntry('emergency', 'test', [
            'a' => 1,
            'b' => false,
        ]);

        self::assertSame(date('[Y-m-d H:i:s]') . ' EMERGENCY test' . PHP_EOL, (string) $entry);

        $entry = new LogEntry(0, 'test', [
            'a' => 1,
            'b' => false,
        ]);

        self::assertSame(date('[Y-m-d H:i:s]') . ' EMERGENCY test' . PHP_EOL, (string) $entry);
    }
}
