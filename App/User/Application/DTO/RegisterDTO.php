<?php

namespace App\User\Application\DTO;

class RegisterDTO
{
    public string $name;
    public string $email;
    public string $phone;
    public string $password;
    public string $confirmPassword;
    public string $method;

    public function __construct(
        string $name,
        string $email,
        string $phone,
        string $password,
        string $confirmPassword,
        string $method = 'email'
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
        $this->confirmPassword = $confirmPassword;
        $this->method = $method;
    }

    // Getters (for consistency)
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getPassword(): string { return $this->password; }
    public function getConfirmPassword(): string { return $this->confirmPassword; }
    public function getMethod(): string { return $this->method; }
}