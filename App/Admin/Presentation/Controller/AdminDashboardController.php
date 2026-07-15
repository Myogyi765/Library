<?php
namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Admin\Application\Service\DashboardStatisticsService; 
class AdminDashboardController extends BaseController 
{
    private UserAuthenticator $userAuth;
    private DashboardStatisticsService $statsService;

    public function __construct(
        UserAuthenticator $userAuth,
        DashboardStatisticsService $statsService
    ) {
        $this->userAuth = $userAuth;
        $this->statsService = $statsService;
    }

    public function index(): void
    {
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            $this->redirect('/login');
        }

        
        $stats = $this->statsService->getStats();

        $this->view('admin-dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'content' => BASE_PATH . '/view/admin/dashboard-content.php',
            'stats' => $stats
        ]);
    }
}