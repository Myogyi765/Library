<?php
namespace App\Admin\Application\Service;

use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;

class DashboardStatisticsService
{
    private BookRepositoryInterface $bookRepo;
    private UserRepositoryInterface $userRepo;
    private LoanRepositoryInterface $loanRepo;

    public function __construct(
        BookRepositoryInterface $bookRepo,
        UserRepositoryInterface $userRepo,
        LoanRepositoryInterface $loanRepo
    ) {
        $this->bookRepo = $bookRepo;
        $this->userRepo = $userRepo;
        $this->loanRepo = $loanRepo;
    }

    public function getStats(): array
    {
        $totalBooks = $this->bookRepo->count();
        $available = $this->bookRepo->getTotalAvailableQuantity();
        $borrowed = $this->bookRepo->getTotalBorrowedQuantity();

        $totalUsers = $this->userRepo->count();
        $librarians = $this->userRepo->countByRole('librarian');

        $activeLoans = $this->loanRepo->countByStatus('active');
        $overdue = $this->loanRepo->countOverdue();

        return [
            'users'       => $totalUsers,
            'librarian'   => $librarians,
            'books'       => $totalBooks,
            'available'   => $available,
            'borrowed'    => $borrowed,
            'activeLoans' => $activeLoans,
            'overdue'     => $overdue,
        ];
    }
}