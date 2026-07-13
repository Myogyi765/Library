<?php

namespace App\Shared\Core\Authorization\Repository;

use App\Shared\Core\Authorization\Entity\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;
    public function findByName(string $name): ?Role;
    public function findAll(): array;
    public function getRoleWithPermissions(string $roleName): ?Role;
}