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

use Apix\Log\Logger\Mail;
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
        new Mail('');
    }

    public function testThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"foo" is an invalid email address');
        new Mail('foo');
    }

    public function testConstructor() : void
    {
        new Mail('foo@bar.com', 'CC: some@somewhere.com');
        self::assertTrue(true);
    }
}
