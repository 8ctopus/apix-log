<?php
/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Logger;

use Apix\Log\Logger\Runtime;

/**
 * @internal
 *
 * @covers \Apix\Log\Logger\Runtime
 */
final class RuntimeTest extends \PHPUnit\Framework\TestCase
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

    public function testAbstractLogger() : void
    {
        $context = ['foo', 'bar'];
        $this->logger->debug('msg1', $context);
        $this->logger->error('msg2', $context);

        static::assertSame(
            date('[Y-m-d H:i:s]') . ' DEBUG msg1' . PHP_EOL,
            (string) $this->logger->getItems()[0]
        );

        static::assertSame(
            date('[Y-m-d H:i:s]') . ' ERROR msg2' . PHP_EOL,
            (string) $this->logger->getItems()[1]
        );
    }
}
