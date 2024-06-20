<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class KavenegarException extends Exception
{
    public function __construct(string $message, int $code = 0, null|Throwable $previous = null)
    {
        parent::__construct('Kavenegar exception message:: ' . $message, $code, $previous);
    }
}
