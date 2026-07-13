<?php

namespace App\User\Application\Response;

use App\User\Application\DTO\UserDTO;

class UserResponse
{
    public bool $success;
    public string $message;
    public ?UserDTO $user;
    public ?array $errors;

    public function __construct(bool $success, string $message, ?UserDTO $user = null, ?array $errors = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->user = $user;
        $this->errors = $errors;
    }
}