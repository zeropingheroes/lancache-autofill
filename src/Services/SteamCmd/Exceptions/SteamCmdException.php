<?php

namespace Zeropingheroes\LancacheAutofill\Services\SteamCmd\Exceptions;

use Throwable;
use Exception;

class SteamCmdException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}