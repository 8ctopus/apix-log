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

use Apix\Log\Logger\File;
use Psr\Log\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Apix\Log\Logger\File
 */
final class FileTest extends \PHPUnit\Framework\TestCase
{
    protected string $dest = 'test';

    protected function tearDown() : void
    {
        if (file_exists($this->dest)) {
            chmod($this->dest, 0777);
            unlink($this->dest);
        }
    }

    public function testConstructor() : void
    {
        $logger = new File($this->dest);

        self::assertInstanceOf(File::class, $logger);
    }

    public function testThrowsInvalidArgumentExceptionWhenFileCannotBeCreated() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Log file "" cannot be created');
        $this->expectExceptionCode(1);
        new File('');
    }

    public function testThrowsInvalidArgumentExceptionWhenNotWritable() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Log file \"{$this->dest}\" is not writable");
        $this->expectExceptionCode(2);

        touch($this->dest);
        chmod($this->dest, 0000);

        new File($this->dest);
    }
}
