<?php
namespace App\Librarian\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Shared\Core\Authorization\Authorization;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Admin\Application\Service\DashboardStatisticsService; // ✅ Import

class DashboardController extends BaseController
{
    private UserAuthenticator $userAuth;
    private BookRepositoryInterface $bookRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private UserRepositoryInterface $userRepository;
    private LoanRepositoryInterface $loanRepository;
    private PaymentRepositoryInterface $paymentRepo;
    private Authorization $authorization;
    private DashboardStatisticsService $dashboardStats; // ✅ Add

    public function __construct(
        UserAuthenticator $userAuth,
        BookRepositoryInterface $bookRepository,
        CategoryRepositoryInterface $categoryRepository,
        UserRepositoryInterface $userRepository,
        LoanRepositoryInterface $loanRepository,
        PaymentRepositoryInterface $paymentRepo,
        Authorization $authorization,
        DashboardStatisticsService $dashboardStats // ✅ Inject
    ) {
        $this->userAuth = $userAuth;
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
        $this->userRepository = $userRepository;
        $this->loanRepository = $loanRepository;
        $this->paymentRepo = $paymentRepo;
        $this->authorization = $authorization;
        $this->dashboardStats = $dashboardStats;
    }

    public function index(): void
    {
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $page = $_GET['page'] ?? 'dashboard';
        $statusFilter = $_GET['status'] ?? 'all';

        // Permission mapping
        $permissionMap = [
            'books'        => 'view_books',
            'books_create' => 'view_books',
            'loans'        => 'view_loans',
            'users'        => 'view_users',
            'reports'      => 'view_reports',
            'payments'     => 'view_payments',
            'refunds'      => 'view_payments',
        ];

        if (isset($permissionMap[$page])) {
            $required = $permissionMap[$page];
            if (isset($_SESSION['user_id'])) {
                $this->authorization->loadUserPermissions($_SESSION['user_id']);
            }
            if (!$this->authorization->hasPermission($required)) {
                http_response_code(403);
                echo "403 Forbidden - You do not have permission to view {$page}.";
                exit;
            }
        }

        // ---- Data fetching with limits ----
        // For lists, limit to 200 to avoid memory exhaustion
        $allUsers = $this->userRepository->findAll(); // Keep as is for now (can be limited later)
        $allBooks = $this->bookRepository->findAll();
        $allLoans = $this->loanRepository->findAll();
        $allCategories = $this->categoryRepository->findAll();

        // Map users, books, categories
        $users = [];
        foreach ($allUsers as $user) {
            $users[$user->getId()] = $user;
        }
        $books = [];
        foreach ($allBooks as $book) {
            $books[$book->getId()] = $book;
        }
        $categoryMap = [];
        foreach ($allCategories as $category) {
            $categoryMap[$category->getId()] = $category->getName();
        }

        // ---- Stats (only for dashboard) ----
        $stats = [];
        if ($page === 'dashboard') {
            // Use the optimized statistics service
            $stats = $this->dashboardStats->getStats();

            // Recent activities – fetch only last 5 loans
            $recentLoans = $this->loanRepository->findRecent(5);
            $recentActivities = [];
            foreach ($recentLoans as $loan) {
                $book = $books[$loan->getBookId()] ?? null;
                $user = $users[$loan->getUserId()] ?? null;
                $recentActivities[] = [
                    'user'   => $user ? $user->getName() : 'Unknown',
                    'action' => $loan->getStatus()->getValue() === 'returned' ? 'Returned' : 'Borrowed',
                    'book'   => $book ? $book->getTitle() : 'Unknown',
                    'date'   => $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('Y-m-d') : '—',
                    'status' => $loan->getStatus()->getValue(),
                ];
            }
            $stats['recentActivities'] = $recentActivities;
        }

        // ---- Prepare base view data ----
        $viewData = [
            'page'        => $page,
            'stats'       => $stats,
            'loans'       => $allLoans,
            'users'       => $users,
            'books'       => $books,
            'allBooks'    => $allBooks,
            'categories'  => $allCategories,
            'categoryMap' => $categoryMap,
        ];

        // ---- Payments: fetch with details and filters (limit 100) ----
        if ($page === 'payments') {
            switch ($statusFilter) {
                case 'pending':
                    $payments = $this->paymentRepo->findPendingApprovalsWithDetails(0, 100);
                    break;
                case 'approved':
                    $payments = $this->paymentRepo->findByStatusWithDetails('completed', 0, 100);
                    break;
                case 'rejected':
                    $payments = $this->paymentRepo->findByStatusWithDetails('rejected', 0, 100);
                    break;
                default:
                    $payments = $this->paymentRepo->findAllWithDetails(0, 100);
                    break;
            }
            $viewData['payments'] = $payments;
            $viewData['currentFilter'] = $statusFilter;
        }

        // ---- Refunds: fetch refund data (limit 100) ----
        if ($page === 'refunds') {
            $allPayments = $this->paymentRepo->findAllWithDetails(0, 100);
            $refunds = array_filter($allPayments, function($payment) {
                return isset($payment['refund_status']) && $payment['refund_status'] !== 'none';
            });
            if ($statusFilter !== 'all') {
                $refunds = array_filter($refunds, function($payment) use ($statusFilter) {
                    return ($payment['refund_status'] ?? '') === $statusFilter;
                });
            }
            $viewData['refunds'] = array_values($refunds);
            $viewData['currentFilter'] = $statusFilter;
        }

        // ---- Render ----
        $pageTitle = 'Librarian Dashboard';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }
}