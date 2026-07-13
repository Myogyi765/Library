<?php

namespace App\Shared\Core\Authorization\Repository;

use App\Shared\Core\Authorization\Entity\Permission;

interface PermissionRepositoryInterface
{
    public function findById(int $id): ?Permission;
    public function findByName(string $name): ?Permission;
    public function findAll(): array;
    public function getPermissionsByRole(string $roleName): array;
}