<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

use Psr\Log\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Apix\Log\Logger\Mail
 */
final class MailTest extends \PHPUnit\Framework\TestCase
{
    public function testThrowsInvalidArgumentExceptionWhenNull() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"" is an invalid email address');
        new Logger\Mail('');
    }

    public function testThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"foo" is an invalid email address');
        new Logger\Mail('foo');
    }

    public function testConstructor() : void
    {
        new Logger\Mail('foo@bar.com', 'CC: some@somewhere.com');
        static::assertTrue(true);
    }
}
