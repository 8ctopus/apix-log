<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Logger;

use Apix\Log\LogEntry;
use Apix\Log\LogFormatter;

/**
 * Runtime (Array/ArrayObject) log wrapper.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Runtime extends AbstractLogger implements LoggerInterface
{
    /**
     * Holds the logged items.
     *
     * @var string[]
     */
    protected array $items = [];

    public function __construct()
    {
        $this->logFormatter = new LogFormatter();
    }

    /**
     * {@inheritDoc}
     */
    public function write(LogEntry|string $log) : bool
    {
        if ($log instanceof LogEntry) {
            $log = $this->getLogFormatter()->format($log);
        }

        $this->items[] = $log;
        return true;
    }

    /**
     * Returns the logged items.
     *
     * @return string[]
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
