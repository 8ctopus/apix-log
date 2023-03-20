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

/**
 * Sapi (Server API) log wrapper.
 *
 * @author             Franck Cassedanne <franck at ouarz.net>
 *
 * @codeCoverageIgnore
 */
class Sapi extends ErrorLog
{
    /**
     * Constructor.
     *
     * @param string $destination
     */
    public function __construct(string $destination = \PHP_SAPI)
    {
        parent::__construct($destination, static::SAPI, null);
    }
}
