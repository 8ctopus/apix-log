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
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function write(LogEntry|array $log) : bool
    {
        if (!is_array($log)) {
            $log = [$log];
        }

        foreach ($log as $item) {
            $this->items[] = $item;
        }

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
