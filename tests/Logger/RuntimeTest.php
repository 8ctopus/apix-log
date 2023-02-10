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
 * @coversNothing
 */
class RuntimeTest extends \PHPUnit\Framework\TestCase
{
    protected $logger;

    protected function setUp() : void
    {
        $this->logger = new Logger\Runtime();
    }

    protected function tearDown() : void
    {
        unset($this->logger);
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

    public function testAbstractLogger()
    {
        $context = ['foo', 'bar'];
        $this->logger->debug('msg1', $context);
        $this->logger->error('msg2', $context);

        $this->assertSame(
            ['debug msg1', 'error msg2'],
            $this->getLogs()
        );
    }
}
