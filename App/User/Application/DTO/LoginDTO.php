<?php

namespace App\User\Application\DTO;

class LoginDTO
{
    private string $identifier;
    private string $password;
    private string $method;
    private bool $remember;

    private function __construct(
        string $identifier,
        string $password,
        string $method,
        bool $remember
    ) {
        $this->identifier = $identifier;
        $this->password = $password;
        $this->method = $method;
        $this->remember = $remember;
    }

    public static function fromArray(array $data): self
    {
        $method = $data['login_method'] ?? 'email';
        
        if ($method === 'email') {
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Valid email is required');
            }
            $identifier = $data['email'];
        } else {
            if (empty($data['phone'])) {
                throw new \InvalidArgumentException('Phone is required');
            }
            $identifier = $data['phone'];
        }

        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Password is required');
        }

        return new self(
            $identifier,
            $data['password'],
            $method,
            isset($data['remember']) && $data['remember'] === 'on'
        );
    }

    public function getIdentifier(): string { return $this->identifier; }
    public function getPassword(): string { return $this->password; }
    public function getMethod(): string { return $this->method; }
    public function isRemember(): bool { return $this->remember; }
}