<?php

namespace App\User\Exception;

// ✅ Must extend Exception or implement Throwable
class InvalidPhoneException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}