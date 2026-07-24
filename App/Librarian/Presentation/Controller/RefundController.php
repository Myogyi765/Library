<?php
namespace App\Librarian\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Shared\Core\Authorization\Authorization;
use PDO;

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

    public function index(): void
    {
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->authorization->loadUserPermissions($_SESSION['user_id'] ?? 0);
        if (!$this->authorization->hasPermission('view_payments')) {
            http_response_code(403);
            echo '403 Forbidden - You do not have permission to view refunds.';
            exit;
        }

        $statusFilter = $_GET['status'] ?? 'all';

        $container = $GLOBALS['container'] ?? null;
        if (!$container) {
            throw new \RuntimeException('Container not found.');
        }
        $db = $container->get('db');

        $sql = "
            SELECT 
                p.*,
                u.name AS user_name,
                u.email AS user_email
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.refund_status IS NOT NULL 
              AND p.refund_status != 'none'
            ORDER BY p.created_at DESC
        ";

        if ($statusFilter !== 'all') {
            $sql .= " AND p.refund_status = :status";
        }

        $stmt = $db->prepare($sql);
        if ($statusFilter !== 'all') {
            $stmt->execute(['status' => $statusFilter]);
        } else {
            $stmt->execute();
        }
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $refunds = [];
        foreach ($results as $row) {
            $refunds[] = [
                'id'              => $row['id'] ?? null,
                'user_id'         => $row['user_id'] ?? null,
                'user_name'       => $row['user_name'] ?? 'Unknown',
                'user_email'      => $row['user_email'] ?? '',
                'loan_id'         => $row['loan_id'] ?? null,
                'amount'          => $row['amount'] ?? 0,
                'payment_method'  => $row['payment_method'] ?? '',
                'refund_status'   => $row['refund_status'] ?? 'none',
                'refund_reason'   => $row['refund_reason'] ?? '—',
                'refunded_at'     => $row['refunded_at'] ?? null,
            ];
        }

        $page = 'refunds';
        $pageTitle = 'Refund Management';
        $content = BASE_PATH . '/view/librarian/refunds/index.php';
        
        $viewData = [
            'refunds'       => $refunds,
            'currentFilter' => $statusFilter,
        ];

        extract($viewData);
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    public function approve(int $id): void
    {
        $payment = $this->paymentRepo->findById($id);
        if ($payment) {
            $payment->setRefundStatus('completed');
            $payment->setRefundedAt(new \DateTimeImmutable());
            $this->paymentRepo->save($payment);

            $userId = $payment->getUserId();
            $this->createNotification(
                $userId,
                'user',
                'refund_approved',
                'Refund approved',
                'Your refund request has been approved.',
                '/user-dashboard'
            );
        }

        $_SESSION['flash_success'] = 'Refund approved successfully.';
        header('Location: ' . BASE_URL . '/librarian/refunds');
        exit;
    }

    public function reject(int $id): void
    {
        $payment = $this->paymentRepo->findById($id);
        if ($payment) {
            $payment->setRefundStatus('none');
            $this->paymentRepo->save($payment);

            $userId = $payment->getUserId();
            $this->createNotification(
                $userId,
                'user',
                'refund_rejected',
                'Refund rejected',
                'Your refund request has been rejected.',
                '/user-dashboard'
            );
        }

        $_SESSION['flash_error'] = 'Refund rejected.';
        header('Location: ' . BASE_URL . '/librarian/refunds');
        exit;
    }
}
