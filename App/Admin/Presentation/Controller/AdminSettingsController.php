<?php

namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Persistence\UserRepository;
use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;
use App\Admin\Application\Service\PermissionService;
use App\Admin\Application\Service\SettingsService;

class AdminSettingsController extends BaseController
{
    private UserAuthenticator $userAuth;
    private UserRepository $userRepository;
    private Authorization $authorization;
    private PermissionService $permissionService;
    private SettingsService $settingsService;

    public function __construct(
        UserAuthenticator $userAuth,
        UserRepository $userRepository,
        Authorization $authorization,
        PermissionService $permissionService,
        SettingsService $settingsService
    ) {
        parent::__construct();
        $this->userAuth = $userAuth;
        $this->userRepository = $userRepository;
        $this->authorization = $authorization;
        $this->permissionService = $permissionService;
        $this->settingsService = $settingsService;
    }

    private function isAdmin(): bool {
        return $this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public function index(): void {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $roles = $this->permissionService->getAllRoles();
        $rolePermissions = $this->permissionService->getRolePermissions($roles);
        $roleCounts = $this->permissionService->getRoleCounts($roles);
        $allPermissions = $this->permissionService->getAllAvailablePermissions();

        $settings = $this->settingsService->getSettings();

        $this->view('admin-dashboard', [
            'pageTitle' => 'Access Control',
            'content' => BASE_PATH . '/view/admin/settings/index.php',
            'roles' => $roles,
            'rolePermissions' => $rolePermissions,
            'roleCounts' => $roleCounts,
            'allPermissions' => $allPermissions,
            'defaultRole' => $settings['default_role'] ?? 'user',
            'settings' => $settings,
        ]);
    }

    public function update(): void {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Invalid request method.';
            $this->redirect('/admin/settings');
            return;
        }

        if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
            $this->permissionService->updateRolePermissions($_POST['permissions']);
            if (isset($_SESSION['user_id'])) {
                $this->authorization->loadUserPermissions($_SESSION['user_id']);
            }
        }

        if (isset($_POST['default_role'])) {
            $defaultRole = trim($_POST['default_role']);
            if (!empty($defaultRole)) {
                $this->settingsService->updateSetting('default_role', $defaultRole);
            }
        }

        $enableRefunds = isset($_POST['enable_refunds']) ? 1 : 0;
        $this->settingsService->updateSetting('enable_refunds', $enableRefunds);

        if (isset($_POST['system_status'])) {
            $this->settingsService->updateSetting('system_status', $_POST['system_status']);
        }

        $_SESSION['success_message'] = 'Settings updated successfully.';
        $this->redirect('/admin/settings');
    }
}