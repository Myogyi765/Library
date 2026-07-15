<?php

namespace App\User\Application\DTO;

class RegisterDTO
{
    private string $name;
    private string $email;
    private ?string $phone;
    private string $password;
    private string $method;

    private function __construct(
        string $name,
        string $email,
        ?string $phone,
        string $password,
        string $method
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
        $this->method = $method;
    }

    public static function fromArray(array $data): self
    {
     
        if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            throw new \InvalidArgumentException('Passwords do not match');
        }

        $method = $data['register_method'] ?? 'email';
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if ($method === 'email') {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Valid email is required');
            }
        } else {
            if (empty($phone) || !preg_match('/^\+95[0-9]{7,10}$/', $phone)) {
                throw new \InvalidArgumentException('Valid Myanmar phone number is required (+95XXXXXXXXX)');
            }
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }

        return new self(
            $data['name'],
            $email,
            $phone,
            $data['password'],
            $method
        );
    }

   
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getPassword(): string { return $this->password; }
    public function getMethod(): string { return $this->method; }
}