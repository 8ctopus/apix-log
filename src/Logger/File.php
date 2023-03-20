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

use Psr\Log\InvalidArgumentException;

/**
 * Minimalist file based PSR-3 logger relying on PHP's error_log().
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class File extends ErrorLog
{
    /**
     * Constructor.
     *
     * @param string $file the file to append to
     *
     * @throws InvalidArgumentException if the file cannot be created or written
     */
    public function __construct(string $file)
    {
        if (!file_exists($file) && !touch($file)) {
            throw new InvalidArgumentException(sprintf('Log file "%s" cannot be created', $file), 1);
        }

        if (!is_writable($file)) {
            throw new InvalidArgumentException(sprintf('Log file "%s" is not writable', $file), 2);
        }

        parent::__construct($file, static::FILE, null);
    }
}
