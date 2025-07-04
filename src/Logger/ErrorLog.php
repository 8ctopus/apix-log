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
 * Minimalist logger implementing PSR-3 relying on PHP's error_log().
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class ErrorLog extends AbstractLogger implements LoggerInterface
{
    public const PHP = 0;
    public const MAIL = 1;
    public const FILE = 3;
    public const SAPI = 4;

    /**
     * Holds the destination string (filename path or email address).
     *
     * @var null|string
     */
    protected ?string $destination;

    /**
     * Holds the message/delivery type:
     *      0: message is sent to PHP's system logger.
     *      1: message is sent by email to the address in the destination.
     *      3: message is appended to the file destination.
     *      4: message is sent directly to the SAPI.
     *
     * @var int
     */
    protected int $type;

    /**
     * Holds a string of additional (mail) headers.
     *
     * @var null|string
     *
     * @see http://php.net/manual/en/function.mail.php
     */
    protected ?string $headers;

    /**
     * Constructor.
     *
     * @param null|string $file    the filename to log messages to
     * @param int         $type    the message/delivery type
     * @param string      $headers
     */
    public function __construct(?string $file = null, int $type = self::PHP, ?string $headers = null)
    {
        $this->destination = $file;
        $this->type = $type;
        $this->headers = $headers;
    }

    /**
     * {@inheritDoc}
     */
    public function write(LogEntry|string $log) : bool
    {
        if ($log instanceof LogEntry) {
            $log = $this->getFormat()->format($log);
        }

        /* REM
        if (!$this->deferred && self::FILE === $this->type) {
            if ($log instanceof LogEntry) {
                $log = $this->getFormat()->format($log);
            }
        }
        */

        return error_log(
            $log,
            $this->type,
            $this->destination,
            $this->headers
        );
    }

    /**
     * Get log destination.
     *
     * @return null|string
     */
    public function getDestination() : ?string
    {
        return $this->destination;
    }
}
