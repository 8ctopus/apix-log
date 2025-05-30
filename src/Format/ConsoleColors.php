<?php

declare(strict_types=1);

namespace Apix\Log\Format;

/**
 * @deprecated
 */
class ConsoleColors extends StandardColored
{
    public function __construct()
    {
        trigger_error(__CLASS__ . ' is deprecated', E_USER_DEPRECATED);

        parent::__construct();
    }
}
