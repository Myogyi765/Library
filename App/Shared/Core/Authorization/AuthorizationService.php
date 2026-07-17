<?php


namespace App\Shared\Core\Authorization;

use App\Shared\Core\Authorization\Entity\Role;
use App\Shared\Core\Authorization\Repository\RoleRepositoryInterface;
use App\Shared\Core\Authorization\Repository\PermissionRepositoryInterface;
use RuntimeException;

class AuthorizationService
{
    private RoleRepositoryInterface $roleRepo;
    private PermissionRepositoryInterface $permissionRepo;
    private array $roleCache = [];

    public function __construct(
        RoleRepositoryInterface $roleRepo,
        PermissionRepositoryInterface $permissionRepo
    ) {
        $this->roleRepo = $roleRepo;
        $this->permissionRepo = $permissionRepo;
    }

    
    public function hasPermission(string $roleName, string $permissionName): bool
    {
        $role = $this->getRoleWithPermissions($roleName);
        if (!$role) {
            return false;
        }
        return $role->hasPermission($permissionName);
    }

    
    public function getPermissionsForRole(string $roleName): array
    {
        $role = $this->getRoleWithPermissions($roleName);
        if (!$role) {
            return [];
        }
        return $role->getPermissions();
    }

    
    public function getAllRoles(): array
    {
        return $this->roleRepo->findAll();
    }

    
    public function getAllPermissions(): array
    {
        return $this->permissionRepo->findAll();
    }

    
    public function roleExists(string $roleName): bool
    {
        $role = $this->roleRepo->findByName($roleName);
        return $role !== null;
    }

    
    public function clearCache(): void
    {
        $this->roleCache = [];
    }

    
    private function getRoleWithPermissions(string $roleName): ?Role
    {
        if (!isset($this->roleCache[$roleName])) {
            try {
                $role = $this->roleRepo->getRoleWithPermissions($roleName);
                $this->roleCache[$roleName] = $role;
            } catch (RuntimeException $e) {
                // Log error if needed
                error_log("❌ [AuthorizationService] Error loading role '{$roleName}': " . $e->getMessage());
                $this->roleCache[$roleName] = null;
            }
        }
        return $this->roleCache[$roleName];
    }
}
