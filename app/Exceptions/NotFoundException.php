<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected static int $statusCode = 404;


    public static function getInstance(string $message): NotFoundException
    {
        return new static($message, static::$statusCode);
    }
}
