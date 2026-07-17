<?php

namespace App\Shared\Core\Authorization;

use PDO;
use PDOException;

class Authorization
{
    private PDO $db;
    private array $permissions = [];
    private array $roles = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

   
    public function loadUserPermissions(int $userId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($userId <= 0) {
            error_log("❌ [Authorization] Invalid user ID: {$userId}");
            $this->clearPermissions();
            return;
        }

        error_log("🔍 [Authorization] loadUserPermissions() called for user: {$userId}");

        try {
            $sql = "SELECT role FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $roleName = $stmt->fetchColumn();

            error_log("🔍 [Authorization] Role from DB: " . ($roleName ?: 'NULL'));

            if (!$roleName) {
                $this->clearPermissions();
                error_log("🔍 [Authorization] No role found → permissions set to empty");
                return;
            }

            $sql = "SELECT rp.permission 
                    FROM role_permissions rp 
                    JOIN roles r ON r.id = rp.role_id 
                    WHERE r.name = :role_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':role_name' => $roleName]);
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $this->roles = [$roleName];
            $this->permissions = $permissions ?: [];

            $_SESSION['user_roles'] = $this->roles;
            $_SESSION['user_permissions'] = $this->permissions;

            error_log("🔍 [Authorization] Permissions loaded: " . print_r($this->permissions, true));
            error_log("🔍 [Authorization] Session updated successfully");

        } catch (PDOException $e) {
            error_log("❌ [Authorization] Database error: " . $e->getMessage());
            $this->clearPermissions();
        }
    }

   
    public function getRoles(): array
    {
        if (empty($this->roles) && isset($_SESSION['user_roles'])) {
            $this->roles = $_SESSION['user_roles'];
        }
        return $this->roles;
    }

    
    public function getPermissions(): array
    {
        if (empty($this->permissions) && isset($_SESSION['user_permissions'])) {
            $this->permissions = $_SESSION['user_permissions'];
        }
        return $this->permissions;
    }

    
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions(), true);
    }

    
    public function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            $this->sendForbidden("You do not have permission to access this page.");
        }
    }

    
    public function requireRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            $this->sendForbidden("You do not have the required role.");
        }
    }

    
    private function clearPermissions(): void
    {
        $this->roles = [];
        $this->permissions = [];
        if (isset($_SESSION)) {
            $_SESSION['user_roles'] = [];
            $_SESSION['user_permissions'] = [];
        }
    }

    
    private function sendForbidden(string $message): void
    {
        http_response_code(403);
        echo '<!DOCTYPE html>
        <html>
        <head><title>403 Forbidden</title></head>
        <body>
            <h1>403 Forbidden</h1>
            <p>' . htmlspecialchars($message) . '</p>
        </body>
        </html>';
        exit;
    }
}
