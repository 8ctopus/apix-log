<?php

declare(strict_types=1);

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Logger;

use Apix\Log\LogEntry;

/**
 * Nil (Null) log wrapper.
 *
 * @author             Franck Cassedanne <franck at ouarz.net>
 *
 * @codeCoverageIgnore
 */
class Nil extends AbstractLogger implements LoggerInterface
{
    /**
     * {@inheritDoc}
     */
    public function write(LogEntry|string $log) : bool
    {
        return false;
    }
}
