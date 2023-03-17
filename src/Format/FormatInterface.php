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

/**
 * Log Formatter Interface.
 *
 * To contribute a formatter, essentially it needs to:
 *    1.) Extends the `Format`
 *    2.) Implements this interface `FormatInterface`
 *
 * @example
 *   class MyJsonFormatter extends Format
 *   {
 *     public function format(LogEntry $log)
 *     {
 *       return json_encode($log);
 *     }
 *   }
 *
 * @see tests/InterfacesTest.php     For a more detailed example.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
interface FormatInterface
{
    /**
     * Formats the given log entry.
     *
     * @param LogEntry $log the log entry to format
     *
     * @return string
     */
    public function format(LogEntry $log) : string;
}
