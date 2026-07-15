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
        $allBooks = $this->bookRepo->findAll();
        $totalBooks = count($allBooks);
        $available = 0;
        $borrowed = 0;
        foreach ($allBooks as $book) {
            $available += $book->getAvailableQuantity();
            $borrowed += $book->getQuantity() - $book->getAvailableQuantity();
        }

        $allUsers = $this->userRepo->findAll();
        $totalUsers = count($allUsers);
        $librarians = 0;
        foreach ($allUsers as $user) {
            if ($user->getRole() === 'librarian') {
                $librarians++;
            }
        }

      
        $allLoans = $this->loanRepo->findAll();
        $activeLoans = 0;
        $overdue = 0;
        $now = new \DateTime();
        foreach ($allLoans as $loan) {
            $status = $loan->getStatus()->getValue();
            if ($status === 'active') {
                $activeLoans++;
                if ($loan->getDueDate() < $now) {
                    $overdue++;
                }
            }
        }

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