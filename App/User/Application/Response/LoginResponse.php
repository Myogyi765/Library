<?php

namespace App\User\Application\Response;

class LoginResponse
{
    public bool $success;
    public string $message;
    public ?int $userId;
    public ?array $errors;

    public function __construct(bool $success, string $message, ?int $userId = null, ?array $errors = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->userId = $userId;
        $this->errors = $errors;
    }
}