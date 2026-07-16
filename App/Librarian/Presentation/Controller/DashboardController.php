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

class DashboardController extends BaseController
{
    private UserAuthenticator $userAuth;
    private BookRepositoryInterface $bookRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private UserRepositoryInterface $userRepository;
    private LoanRepositoryInterface $loanRepository;
    private PaymentRepositoryInterface $paymentRepo;
    private Authorization $authorization;

    public function __construct(
        UserAuthenticator $userAuth,
        BookRepositoryInterface $bookRepository,
        CategoryRepositoryInterface $categoryRepository,
        UserRepositoryInterface $userRepository,
        LoanRepositoryInterface $loanRepository,
        PaymentRepositoryInterface $paymentRepo,
        Authorization $authorization
    ) {
        $this->userAuth = $userAuth;
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
        $this->userRepository = $userRepository;
        $this->loanRepository = $loanRepository;
        $this->paymentRepo = $paymentRepo;
        $this->authorization = $authorization;
    }

    public function index(): void
    {
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $page = $_GET['page'] ?? 'dashboard';
        $statusFilter = $_GET['status'] ?? 'all';  // for payment/refund filter

        // Permission mapping
        $permissionMap = [
            'books'        => 'view_books',
            'books_create' => 'view_books',
            'loans'        => 'view_loans',
            'users'        => 'view_users',
            'reports'      => 'view_reports',
            'payments'     => 'view_payments',
            'refunds'      => 'view_payments', // refunds require payment view permission
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

        // ---- Data fetching ----
        $allUsers = $this->userRepository->findAll();
        $allBooks = $this->bookRepository->findAll();
        $allLoans = $this->loanRepository->findAll();
        $allCategories = $this->categoryRepository->findAll();

        // Map users by ID for fast lookup
        $users = [];
        foreach ($allUsers as $user) {
            $users[$user->getId()] = $user;
        }

        // Map books by ID
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
            $totalBooks = count($allBooks);
            $available = 0;
            $borrowed = 0;
            foreach ($allBooks as $book) {
                $available += $book->getAvailableQuantity();
                $borrowed += $book->getQuantity() - $book->getAvailableQuantity();
            }
            $totalUsers = count($allUsers);
            $activeLoans = 0;
            $overdue = 0;
            $now = new \DateTime();
            foreach ($allLoans as $loan) {
                $status = $loan->getStatus()->getValue();
                if ($status === 'active') {
                    $activeLoans++;
                    if ($loan->getDueDate() && $loan->getDueDate() < $now) {
                        $overdue++;
                    }
                }
            }
            $recentLoans = array_slice($allLoans, 0, 5);
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
            $stats = [
                'totalBooks'   => $totalBooks,
                'available'    => $available,
                'borrowed'     => $borrowed,
                'overdue'      => $overdue,
                'totalUsers'   => $totalUsers,
                'activeLoans'  => $activeLoans,
                'recentActivities' => $recentActivities,
            ];
        }

        // ---- Prepare base view data ----
        $viewData = [
            'page'        => $page,
            'stats'       => $stats,
            'loans'       => $allLoans,
            'users'       => $users,        // indexed by user ID
            'books'       => $books,        // lookup array
            'allBooks'    => $allBooks,
            'categories'  => $allCategories,
            'categoryMap' => $categoryMap,
        ];

        // ---- Payments: fetch with details and filters ----
        if ($page === 'payments') {
            switch ($statusFilter) {
                case 'pending':
                    $payments = $this->paymentRepo->findPendingApprovalsWithDetails();
                    break;
                case 'approved':
                    $payments = $this->paymentRepo->findByStatusWithDetails('completed');
                    break;
                case 'rejected':
                    $payments = $this->paymentRepo->findByStatusWithDetails('rejected');
                    break;
                default:
                    $payments = $this->paymentRepo->findAllWithDetails();
                    break;
            }
            $viewData['payments'] = $payments;
            $viewData['currentFilter'] = $statusFilter;
        }

        // ---- Refunds: fetch refund data ----
        if ($page === 'refunds') {
            // Get all payments with details
            $allPayments = $this->paymentRepo->findAllWithDetails();

            // Filter those with refund_status not 'none'
            $refunds = array_filter($allPayments, function($payment) {
                return isset($payment['refund_status']) && $payment['refund_status'] !== 'none';
            });

            // If status filter is applied (pending/completed)
            if ($statusFilter !== 'all') {
                $refunds = array_filter($refunds, function($payment) use ($statusFilter) {
                    return ($payment['refund_status'] ?? '') === $statusFilter;
                });
            }

            // Re-index array
            $refunds = array_values($refunds);

            $viewData['refunds'] = $refunds;
            $viewData['currentFilter'] = $statusFilter;
        }

        // ---- Render ----
        $pageTitle = 'Librarian Dashboard';
        $content = BASE_PATH . '/view/librarian/dashboard-content.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }
}