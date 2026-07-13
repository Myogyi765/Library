<?php

namespace App\Shared\Core\Authorization\Entity;

class Role
{
    private int $id;
    private string $name;
    private string $description;
    private array $permissions = [];

    public function __construct(int $id, string $name, string $description = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPermissions(): array { return $this->permissions; }
    
    public function addPermission(Permission $permission): void
    {
        $this->permissions[$permission->getId()] = $permission;
    }

    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getName() === $permissionName) {
                return true;
            }
        }
        return false;
    }
}