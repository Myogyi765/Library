<?php
namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Persistence\UserRepository;
use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;
use PDO;

class AdminSettingsController extends BaseController
{
    private UserAuthenticator $userAuth;
    private UserRepository $userRepository;
    private Authorization $authorization;
    private PDO $db;

    public function __construct(
        UserAuthenticator $userAuth, 
        UserRepository $userRepository,
        Authorization $authorization,
        PDO $db
    ) {
        parent::__construct();
        $this->userAuth = $userAuth;
        $this->userRepository = $userRepository;
        $this->authorization = $authorization;
        $this->db = $db;
    }

    private function isAdmin(): bool
    {
        return $this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public function index(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $rolesStmt = $this->db->query("SELECT name FROM roles ORDER BY id");
        $roles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);

        $rolePermissions = [];
        foreach ($roles as $role) {
            $stmt = $this->db->prepare("
                SELECT rp.permission 
                FROM role_permissions rp
                JOIN roles r ON r.id = rp.role_id
                WHERE r.name = ?
            ");
            $stmt->execute([$role]);
            $rolePermissions[$role] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $roleCounts = [];
        foreach ($roles as $role) {
            $stmt = $this->db->prepare("
                SELECT COUNT(u.id) 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE r.name = ?
            ");
            $stmt->execute([$role]);
            $roleCounts[$role] = (int)$stmt->fetchColumn();
        }

        $allPermissions = [
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_books', 'create_books', 'edit_books', 'delete_books',
            'view_loans', 'create_loans', 'edit_loans', 'delete_loans',
            'borrow_books',
            'view_own_loans',     
            'view_profile',
            'edit_profile',
            'view_reports', 'export_reports', 'manage_settings',
            'view_notifications',
            'create_notifications',
            'edit_notifications',
            'view_payments', 'create_payments', 'edit_payments', 'delete_payments',
        ];

        $defaultRole = 'user';
        $permissions = $allPermissions;
        $pageTitle = 'Access Control';
        $content = BASE_PATH . '/view/admin/settings/index.php';
        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function update(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['permissions'])) {
            $newPermissions = $_POST['permissions'];

            foreach ($newPermissions as $roleName => $permissionsArray) {
                // Get role ID
                $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
                $stmt->execute([$roleName]);
                $roleId = $stmt->fetchColumn();
                if (!$roleId) continue;

                // Delete all existing permissions for this role
                $delStmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
                $delStmt->execute([$roleId]);

                // Insert only checked permissions (value == 1)
                if (!empty($permissionsArray)) {
                    $insertStmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)");
                    foreach ($permissionsArray as $permName => $value) {
                        // ✅ Only insert if value is 1 (checked)
                        if ($value == '1') {
                            $insertStmt->execute([$roleId, $permName]);
                        }
                    }
                }
            }

            // Refresh current admin session permissions
            if (isset($_SESSION['user_id'])) {
                $this->authorization->loadUserPermissions($_SESSION['user_id']);
            }

            $_SESSION['success_message'] = 'Permissions updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Invalid request.';
        }

        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
}