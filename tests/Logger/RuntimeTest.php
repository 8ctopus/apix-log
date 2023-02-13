<?php
/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests\Logger;

use Apix\Log\Logger;

/**
 * @internal
 *
 * @covers \Apix\Log\Logger\Runtime
 */
final class RuntimeTest extends \PHPUnit\Framework\TestCase
{
    protected ?Logger\Runtime $logger;

    protected function setUp() : void
    {
        $this->logger = new Logger\Runtime();
    }

    protected function tearDown() : void
    {
        $this->logger = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogs()
    {
        return TestCase::normalizeLogs($this->logger->getItems());
    }

    /**
     * {@inheritDoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function testAbstractLogger() : void
    {
        $context = ['foo', 'bar'];
        $this->logger->debug('msg1', $context);
        $this->logger->error('msg2', $context);

        static::assertSame(
            ['debug msg1', 'error msg2'],
            $this->getLogs()
        );
    }
}
