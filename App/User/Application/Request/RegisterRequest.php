<?php

namespace App\User\Application\Request;

class RegisterRequest
{
    private string $name;
    private string $email;
    private string $phone;
    private string $password;
    private string $method;
    private string $confirmPassword;
    
    public function __construct(
        string $name,
        string $email,
        string $phone,
        string $password,
        string $method = 'email',
        string $confirmPassword = ''
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
        $this->method = $method;
        $this->confirmPassword = $confirmPassword;
    }
    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['password'] ?? '',
            $data['register_method'] ?? 'email',
            $data['confirm_password'] ?? ''
        );
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getPhone(): string
    {
        return $this->phone;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }
    
    public function toDTO(): array
    {
        return [
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $this->password,
            'method' => $this->method
        ];
    }
}