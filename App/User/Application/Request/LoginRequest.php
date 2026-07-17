<?php

namespace App\User\Application\Request;

use App\User\Application\DTO\LoginDTO;

class LoginRequest
{
    private array $data;
    
    private function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public static function fromArray(array $data): self
    {
        $method = $data['login_method'] ?? 'email';
        
        if ($method === 'email') {
            if (empty($data['email'])) {
                throw new \InvalidArgumentException('Email is required');
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format');
            }
            $data['identifier'] = $data['email'];
        } else {
            if (empty($data['phone'])) {
                throw new \InvalidArgumentException('Phone is required');
            }
            $data['identifier'] = $data['phone'];
        }
        
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Password is required');
        }
        
        $data['remember'] = isset($data['remember']) && $data['remember'] === 'on';
        
        $data['method'] = $method;
        
        return new self($data);
    }
    
    public function getIdentifier(): string
    {
        return $this->data['identifier'];
    }
    
    public function getPassword(): string
    {
        return $this->data['password'];
    }
    
    public function getMethod(): string
    {
        return $this->data['method'] ?? 'email';
    }
    
    public function isRemember(): bool
    {
        return $this->data['remember'] ?? false;
    }
    
   public function toDTO(): LoginDTO
{
    return new LoginDTO(
        $this->getIdentifier(), 
        $this->getPassword(),  
        $this->getMethod(),     
        $this->isRemember()     
    );
}
}