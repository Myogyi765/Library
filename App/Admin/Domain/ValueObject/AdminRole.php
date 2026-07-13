<?php
namespace App\Admin\Domain\ValueObject;

class AdminRole
{
    private string $role;

    public function __construct(string $role)
    {
        $allowed = ['admin'];
        if (!in_array($role, $allowed)) {
            throw new \InvalidArgumentException('Invalid admin role');
        }
        $this->role = $role;
    }

    public function getValue(): string { return $this->role; }
}