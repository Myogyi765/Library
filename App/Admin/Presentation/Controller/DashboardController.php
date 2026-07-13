<?php
namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface;

class DashboardController extends BaseController
{
    private UserAuthenticator $userAuth;
    private BookRepositoryInterface $bookRepository;
    private UserRepositoryInterface $userRepository;
    private LoanRepositoryInterface $loanRepository;

    public function __construct($container)
    {
        parent::__construct($container);
        
        $this->userAuth = $this->container->get('user.authenticator');
        $this->bookRepository = $this->container->get('book.repository');
        $this->userRepository = $this->container->get('user.repository');
        $this->loanRepository = $this->container->get('loan.repository');
    }

    public function index(): void
    {
        if (!$this->userAuth->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $allBooks = $this->bookRepository->findAll();
        $totalBooks = count($allBooks);
        $available = 0;
        $borrowed = 0;
        foreach ($allBooks as $book) {
            $available += $book->getAvailableQuantity();
            $borrowed += $book->getQuantity() - $book->getAvailableQuantity();
        }

        $allUsers = $this->userRepository->findAll();
        $totalUsers = count($allUsers);
        $librarians = 0;
        foreach ($allUsers as $user) {
            if ($user->getRole() === 'librarian') {
                $librarians++;
            }
        }

        $allLoans = $this->loanRepository->findAll();
        $activeLoans = 0;
        $overdue = 0;
        $now = new \DateTime();
        foreach ($allLoans as $loan) {
            // ✅ Fix: get status string from LoanStatus object
            $status = $loan->getStatus()->getValue();
            if ($status === 'active') {
                $activeLoans++;
                if ($loan->getDueDate() < $now) {
                    $overdue++;
                }
            }
        }

        $stats = [
            'users'       => $totalUsers,
            'librarian'   => $librarians,
            'books'       => $totalBooks,
            'available'   => $available,
            'borrowed'    => $borrowed,
            'activeLoans' => $activeLoans,
            'overdue'     => $overdue,
        ];
$pageTitle = 'Admin Dashboard';
$viewData = ['stats' => $stats];
$content = BASE_PATH . '/view/admin/dashboard-content.php';
include BASE_PATH . '/view/admin-dashboard.php';
    }
}