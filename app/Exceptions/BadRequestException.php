<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    protected static int $statusCode = 400;

    public static function getInstance(string $message): BadRequestException
    {
        return new static($message, static::$statusCode);
    }
}
