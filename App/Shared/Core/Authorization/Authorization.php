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
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate user ID
        if ($userId <= 0) {
            error_log("❌ [Authorization] Invalid user ID: {$userId}");
            $this->clearPermissions();
            return;
        }

        error_log("🔍 [Authorization] loadUserPermissions() called for user: {$userId}");

        try {
            // Fetch user role
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

            // Fetch permissions for the role
            $sql = "SELECT rp.permission 
                    FROM role_permissions rp 
                    JOIN roles r ON r.id = rp.role_id 
                    WHERE r.name = :role_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':role_name' => $roleName]);
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Store in local properties
            $this->roles = [$roleName];
            $this->permissions = $permissions ?: [];

            // Store in session
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

    /**
     * Get permissions from local cache or session.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        if (empty($this->permissions) && isset($_SESSION['user_permissions'])) {
            $this->permissions = $_SESSION['user_permissions'];
        }
        return $this->permissions;
    }

    /**
     * Check if the current user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    /**
     * Check if the current user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions(), true);
    }

    /**
     * Require a specific permission; if not met, send 403 and exit.
     *
     * @param string $permission
     * @return void
     */
    public function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            $this->sendForbidden("You do not have permission to access this page.");
        }
    }

    /**
     * Require a specific role; if not met, send 403 and exit.
     *
     * @param string $role
     * @return void
     */
    public function requireRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            $this->sendForbidden("You do not have the required role.");
        }
    }

    /**
     * Clear permissions and session data.
     *
     * @return void
     */
    private function clearPermissions(): void
    {
        $this->roles = [];
        $this->permissions = [];
        if (isset($_SESSION)) {
            $_SESSION['user_roles'] = [];
            $_SESSION['user_permissions'] = [];
        }
    }

    /**
     * Send a 403 Forbidden response and exit.
     *
     * @param string $message
     * @return void
     */
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