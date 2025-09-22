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

use Apix\Log\Logger\Runtime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Runtime::class)]
final class RuntimeTest extends TestCase
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

        self::assertSame(
            [
                date('[Y-m-d H:i:s]') . ' DEBUG msg1' . PHP_EOL,
                date('[Y-m-d H:i:s]') . ' ERROR msg2' . PHP_EOL,
            ],
            $this->logger->getItems()
        );
    }
}
