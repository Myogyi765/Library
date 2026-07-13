<?php

namespace App\Admin\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Admin\Infrastructure\Persistence\SettingRepository;
use App\User\Infrastructure\Security\UserAuthenticator;

class AdminFineController extends BaseController
{
    private SettingRepository $settingRepo;
    private UserAuthenticator $userAuth;

    public function __construct(
        SettingRepository $settingRepo,
        UserAuthenticator $userAuth
    ) {
        parent::__construct();
        $this->settingRepo = $settingRepo;
        $this->userAuth = $userAuth;
    }

    public function index(): void
    {
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        
        $settings = $this->settingRepo->findAll();
        
        $pageTitle = 'Fine & Fee Settings';
        $content = BASE_PATH . '/view/admin/fines/index.php';
        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function update(): void
    {
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = $_POST['settings'] ?? [];
            $this->settingRepo->update($settings);
            $_SESSION['success_message'] = 'Settings updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Invalid request.';
        }

        header('Location: ' . BASE_URL . '/admin/fines');
        exit;
    }
}