<?php

declare(strict_types=1);

namespace Apix\Log\Format;

use Apix\Log\LogEntry;
use Stringable;

class Minimal implements FormatInterface
{
    public readonly string $separator;

    public function __construct(string $separator = PHP_EOL)
    {
        $this->separator = $separator;
    }

    /**
     * Formats log entry
     *
     * @param LogEntry $log
     *
     * @return string
     */
    public function format(LogEntry $log) : string
    {
        return sprintf('%s', self::interpolate($log->message, $log->context)) . $this->separator;
    }

    /**
     * Interpolates context values into the message placeholders
     *
     * Builds a replacement array with braces around the context keys.
     * It replaces {foo} with the value from $context['foo'].
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return string
     */
    protected function interpolate(string $message, array $context = []) : string
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
}
