<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Format;

use Apix\Log\LogEntry;
use Stringable;

/**
 * Standard log formatter.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Standard implements FormatInterface
{
    /**
     * Holds this log separator.
     *
     * @var string
     */
    public readonly string $separator;

    public function __construct(string $separator = PHP_EOL)
    {
        $this->separator = $separator;
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * Builds a replacement array with braces around the context keys.
     * It replaces {foo} with the value from $context['foo'].
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return string
     */
    public function interpolate(string $message, array $context = []) : string
    {
        $replaces = [];

        foreach ($context as $key => $val) {
            if (\is_bool($val)) {
                $val = $val ? 'true' : 'false';
            } elseif (null === $val || \is_scalar($val) || ($val instanceof Stringable)) {
                $val = (string) $val;
            } elseif (\is_array($val) || \is_object($val)) {
                $val = json_encode($val);

                if ($val === false) {
                    $val = 'json encode error';
                }
            } else {
                $val = '[type: ' . \gettype($val) . ']';
            }

            $replaces['{' . $key . '}'] = $val;
        }

        return strtr($message, $replaces);
    }

    /**
     * Formats the given log entry.
     *
     * @param LogEntry $log the log entry to format
     *
     * @return string
     */
    public function format(LogEntry $log) : string
    {
        return sprintf(
            '[%s] %s %s',
            date('Y-m-d H:i:s', $log->timestamp),
            strtoupper($log->name),
            self::interpolate($log->message, $log->context)
        ) . $this->separator;
    }
}
