<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Format;

use Apix\Log\Logger\Runtime;

/**
 * @internal
 *
 * @covers \Apix\Log\Format\Standard
 */
final class StandardTest extends \PHPUnit\Framework\TestCase
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
        static::assertInstanceOf('\Apix\Log\Format\Standard', $this->logger->getFormat());
    }

    public function testFormatInterfaceExample() : void
    {
        $this->logger->error('hello {who} {age} {bool} {object}', [
            'who' => 'world',
            'age' => 18,
            'bool' => true,
            'object' => [
                'key1' => '2',
                'key2' => true,
            ],
            'file' => tmpfile(),
        ]);

        static::assertSame(date('[Y-m-d H:i:s]') . ' ERROR hello world 18 true {"key1":"2","key2":true}' . PHP_EOL, $this->logger->getItems()[0]);
    }
}
