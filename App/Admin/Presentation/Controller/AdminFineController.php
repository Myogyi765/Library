<?php
namespace App\Admin\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Admin\Infrastructure\Persistence\SettingRepository;
use App\User\Infrastructure\Security\UserAuthenticator;

class AdminFineController extends BaseController
{
    private SettingRepository $settingRepo;
    private UserAuthenticator $userAuth;

    public function __construct(SettingRepository $settingRepo, UserAuthenticator $userAuth)
    {
        parent::__construct();
        $this->settingRepo = $settingRepo;
        $this->userAuth = $userAuth;
    }

    private function isAdmin(): bool
    {
        return $this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public function index(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $settings = $this->settingRepo->findAll();

        $this->view('admin-dashboard', [
            'pageTitle' => 'Fine & Fee Settings',
            'content'   => BASE_PATH . '/view/admin/Fines/index.php',
            'settings'  => $settings, 
        ]);
    }

    public function update(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $allowedKeys = [
                'fine_per_day',
                'borrowing_fee',
                'max_borrow_days',
                'max_borrow_limit',
                'grace_period_days',
                'membership_fee',
            ];
            
            $settings = [];

            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $settings[$key] = trim($_POST[$key]);
                }
            }

            if (!empty($settings)) {
                $this->settingRepo->update($settings);
                $_SESSION['success_message'] = 'Fine & fee settings updated successfully!';
            } else {
                $_SESSION['error_message'] = 'No valid settings were submitted.';
            }
        }

        $this->redirect('/admin/fines');
    }
}