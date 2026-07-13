<?php

namespace App\User\Application\DTO;

class LoginDTO
{
    public string $identifier;
    public string $password;
    public string $method;
    public bool $remember;

    public function __construct(
        string $identifier,
        string $password,
        string $method = 'email',
        bool $remember = false
    ) {
        $this->identifier = $identifier;
        $this->password = $password;
        $this->method = $method;
        $this->remember = $remember;
    }

    public function getIdentifier(): string { return $this->identifier; }
    public function getPassword(): string { return $this->password; }
    public function getMethod(): string { return $this->method; }
    public function isRemember(): bool { return $this->remember; }
}