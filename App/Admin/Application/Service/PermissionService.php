<?php
namespace App\Admin\Application\Service;

use PDO;

class PermissionService
{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAllRoles(): array {
        $stmt = $this->db->query("SELECT name FROM roles ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getRolePermissions(array $roles): array {
        $result = [];
        foreach ($roles as $role) {
            $stmt = $this->db->prepare("SELECT rp.permission FROM role_permissions rp JOIN roles r ON r.id = rp.role_id WHERE r.name = ?");
            $stmt->execute([$role]);
            $result[$role] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $result;
    }

    public function getRoleCounts(array $roles): array {
        $result = [];
        foreach ($roles as $role) {
            $stmt = $this->db->prepare("SELECT COUNT(u.id) FROM users u INNER JOIN roles r ON u.role_id = r.id WHERE r.name = ?");
            $stmt->execute([$role]);
            $result[$role] = (int)$stmt->fetchColumn();
        }
        return $result;
    }

    public function getAllAvailablePermissions(): array {
        return [
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_books', 'create_books', 'edit_books', 'delete_books',
            'view_loans', 'create_loans', 'edit_loans', 'delete_loans',
            'borrow_books', 'view_own_loans', 'view_profile', 'edit_profile',
            'view_reports', 'export_reports', 'manage_settings',
            'view_notifications', 'create_notifications', 'edit_notifications',
            'view_payments', 'create_payments', 'edit_payments', 'delete_payments',
        ];
    }

    public function updateRolePermissions(array $newPermissions): void {
        foreach ($newPermissions as $roleName => $permissionsArray) {
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$roleName]);
            $roleId = $stmt->fetchColumn();
            if (!$roleId) continue;

            $delStmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $delStmt->execute([$roleId]);

            if (!empty($permissionsArray)) {
                $insertStmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)");
                foreach ($permissionsArray as $permName => $value) {
                    if ($value == '1') {
                        $insertStmt->execute([$roleId, $permName]);
                    }
                }
            }
        }
    }
}