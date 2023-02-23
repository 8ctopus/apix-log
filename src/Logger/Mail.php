<?php

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
 * Minimalist mail based PSR-3 logger relying on PHP's error_log().
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Mail extends ErrorLog
{
    /**
     * Constructor.
     *
     * @param string      $email   the email to append to
     * @param null|string $headers a string of additional (mail) headers
     *
     * @throws InvalidArgumentException if the email does not validate
     */
    public function __construct(string $email, ?string $headers = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is an invalid email address', $email)
            );
        }

        parent::__construct($email, static::MAIL, $headers);
    }
}
