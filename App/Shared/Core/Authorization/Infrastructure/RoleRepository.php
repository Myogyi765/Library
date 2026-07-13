<?php

namespace App\Shared\Core\Authorization\Infrastructure;

use App\Shared\Core\Authorization\Entity\Role;
use App\Shared\Core\Authorization\Entity\Permission;
use App\Shared\Core\Authorization\Repository\RoleRepositoryInterface;
use PDO;

class RoleRepository implements RoleRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?Role
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) return null;
        
        return new Role($data['id'], $data['name'], $data['description']);
    }

    public function findByName(string $name): ?Role
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) return null;
        
        return new Role($data['id'], $data['name'], $data['description']);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles");
        $roles = [];
        
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = new Role($data['id'], $data['name'], $data['description']);
        }
        
        return $roles;
    }

    public function getRoleWithPermissions(string $roleName): ?Role
    {
        $role = $this->findByName($roleName);
        if (!$role) return null;
        
        $stmt = $this->db->prepare("
            SELECT rp.permission 
            FROM role_permissions rp
            JOIN roles r ON r.id = rp.role_id
            WHERE r.name = ?
        ");
        $stmt->execute([$roleName]);
        
        while ($permName = $stmt->fetchColumn()) {
            $permission = new Permission(0, $permName, '');
            $role->addPermission($permission);
        }
        
        return $role;
    }

    /**
     * Update permissions for a role (optional, for admin settings)
     */
    public function updatePermissions(string $roleName, array $permissions): void
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$roleName]);
        $roleId = $stmt->fetchColumn();
        if (!$roleId) {
            throw new \Exception("Role not found: {$roleName}");
        }

        $delStmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $delStmt->execute([$roleId]);

        if (!empty($permissions)) {
            $insertStmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)");
            foreach ($permissions as $perm) {
                $insertStmt->execute([$roleId, $perm]);
            }
        }
    }
}