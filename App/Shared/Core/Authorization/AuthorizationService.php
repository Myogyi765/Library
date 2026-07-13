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

    /**
     * Check if a role has a specific permission.
     *
     * @param string $roleName
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $roleName, string $permissionName): bool
    {
        $role = $this->getRoleWithPermissions($roleName);
        if (!$role) {
            return false;
        }
        return $role->hasPermission($permissionName);
    }

    /**
     * Get all permissions for a specific role.
     *
     * @param string $roleName
     * @return array
     */
    public function getPermissionsForRole(string $roleName): array
    {
        $role = $this->getRoleWithPermissions($roleName);
        if (!$role) {
            return [];
        }
        return $role->getPermissions();
    }

    /**
     * Get all roles.
     *
     * @return array
     */
    public function getAllRoles(): array
    {
        return $this->roleRepo->findAll();
    }

    /**
     * Get all permissions.
     *
     * @return array
     */
    public function getAllPermissions(): array
    {
        return $this->permissionRepo->findAll();
    }

    /**
     * Check if a role exists.
     *
     * @param string $roleName
     * @return bool
     */
    public function roleExists(string $roleName): bool
    {
        $role = $this->roleRepo->findByName($roleName);
        return $role !== null;
    }

    /**
     * Clear the role cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->roleCache = [];
    }

    /**
     * Get role with permissions from cache or repository.
     *
     * @param string $roleName
     * @return Role|null
     */
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