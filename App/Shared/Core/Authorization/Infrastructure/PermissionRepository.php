<?php

namespace App\Shared\Core\Authorization\Infrastructure;

use App\Shared\Core\Authorization\Entity\Permission;
use App\Shared\Core\Authorization\Repository\PermissionRepositoryInterface;
use PDO;

class PermissionRepository implements PermissionRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?Permission
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) return null;
        
        return new Permission($data['id'], $data['name'], $data['description']);
    }

    public function findByName(string $name): ?Permission
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE name = ?");
        $stmt->execute([$name]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) return null;
        
        return new Permission($data['id'], $data['name'], $data['description']);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM permissions");
        $permissions = [];
        
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[] = new Permission($data['id'], $data['name'], $data['description']);
        }
        
        return $permissions;
    }

    public function getPermissionsByRole(string $roleName): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            JOIN roles r ON r.id = rp.role_id
            WHERE r.name = ?
        ");
        $stmt->execute([$roleName]);
        
        $permissions = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[] = new Permission($data['id'], $data['name'], $data['description']);
        }
        
        return $permissions;
    }
}