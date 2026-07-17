<?php
namespace App\Librarian\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Shared\Core\Authorization\Authorization;

class RefundController extends BaseController
{
    private UserAuthenticator $userAuth;
    private PaymentRepositoryInterface $paymentRepo;
    private Authorization $authorization;

    public function __construct(
        UserAuthenticator $userAuth,
        PaymentRepositoryInterface $paymentRepo,
        Authorization $authorization
    ) {
        $this->userAuth = $userAuth;
        $this->paymentRepo = $paymentRepo;
        $this->authorization = $authorization;
    }

    /**
     * Display refund list
     */
    public function index(): void
    {
        // Authentication & role check
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Permission check
        $this->authorization->loadUserPermissions($_SESSION['user_id'] ?? 0);
        if (!$this->authorization->hasPermission('view_payments')) {
            http_response_code(403);
            echo '403 Forbidden - You do not have permission to view refunds.';
            exit;
        }

        // Get filter status from query string
        $statusFilter = $_GET['status'] ?? 'all';

        // Fetch refund data (limit 100)
        $allPayments = $this->paymentRepo->findAllWithDetails(0, 100);
        $refunds = array_filter($allPayments, function($p) {
            return isset($p['refund_status']) && $p['refund_status'] !== 'none';
        });

        if ($statusFilter !== 'all') {
            $refunds = array_filter($refunds, function($p) use ($statusFilter) {
                return ($p['refund_status'] ?? '') === $statusFilter;
            });
        }

        $refunds = array_values($refunds);

        // Prepare view data
        $viewData = [
            'refunds'       => $refunds,
            'currentFilter' => $statusFilter,
            'page'          => 'refunds', // For sidebar highlighting
        ];

        // Render using the existing librarian dashboard layout
        $pageTitle = 'Refund Management';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    /**
     * Approve a refund
     */
    public function approve(int $id): void
    {
        // Your approve logic here
        // Example: update payment refund_status to 'completed'
        // Redirect back with success message
        $this->createNotification(
            (int) ($_SESSION['user_id'] ?? 0),
            'librarian',
            'refund_approved',
            'Refund approved',
            'The refund request was approved successfully.',
            '/librarian/dashboard?page=refunds'
        );
        $_SESSION['flash_success'] = 'Refund approved successfully.';
        header('Location: ' . BASE_URL . '/librarian/refunds');
        exit;
    }

    /**
     * Reject a refund
     */
    public function reject(int $id): void
    {
        // Your reject logic here
        // Example: update payment refund_status to 'rejected'
        // Redirect back with error message
        $this->createNotification(
            (int) ($_SESSION['user_id'] ?? 0),
            'librarian',
            'refund_rejected',
            'Refund rejected',
            'The refund request was rejected.',
            '/librarian/dashboard?page=refunds'
        );
        $_SESSION['flash_error'] = 'Refund rejected.';
        header('Location: ' . BASE_URL . '/librarian/refunds');
        exit;
    }
}