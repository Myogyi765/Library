<?php
namespace App\Admin\Application\Service;

use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;

class ReportService
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

    public function getDashboardReport(): array
    {
        $books = $this->bookRepo->findAll();
        $users = $this->userRepo->findAll();
        $loans = $this->loanRepo->findAll();

        $totalBooks = count($books);
        $availableBooks = array_sum(array_map(fn($book) => $book->getAvailableQuantity(), $books));
        $borrowedBooks = array_sum(array_map(fn($book) => max(0, $book->getQuantity() - $book->getAvailableQuantity()), $books));
        $totalUsers = count($users);

        $activeLoans = 0;
        $overdueLoans = 0;
        $monthlyLoans = [];
        $now = new \DateTimeImmutable();

        foreach ($loans as $loan) {
            $status = $loan->getStatus()->getValue();
            if ($status === 'active') {
                $activeLoans++;
                $dueDate = $loan->getDueDate();
                if ($dueDate instanceof \DateTimeImmutable && $dueDate < $now) {
                    $overdueLoans++;
                }
            }

            $borrowedAt = $loan->getBorrowedAt();
            if ($borrowedAt instanceof \DateTimeImmutable) {
                $month = $borrowedAt->format('M');
                $monthlyLoans[$month] = ($monthlyLoans[$month] ?? 0) + 1;
            }
        }

        return [
            'totalBooks' => $totalBooks,
            'availableBooks' => $availableBooks,
            'borrowedBooks' => $borrowedBooks,
            'totalUsers' => $totalUsers,
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'monthlyLoans' => $this->buildMonthlyLoanSummary($monthlyLoans),
            'popularBooks' => $this->getPopularBooksFromLoans($loans),
            'recentActivities' => $this->getRecentActivitiesFromLoans($loans),
        ];
    }

    public function getPopularBooks(): array
    {
        return $this->getPopularBooksFromLoans($this->loanRepo->findAll());
    }

    public function getRecentActivities(): array
    {
        return $this->getRecentActivitiesFromLoans($this->loanRepo->findAll());
    }

    private function buildMonthlyLoanSummary(array $monthlyLoans): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $summary = [];
        foreach ($months as $month) {
            $summary[$month] = $monthlyLoans[$month] ?? 0;
        }
        return $summary;
    }

    private function getPopularBooksFromLoans(array $loans): array
    {
        $counts = [];
        foreach ($loans as $loan) {
            $bookId = $loan->getBookId();
            $counts[$bookId] = ($counts[$bookId] ?? 0) + 1;
        }

        arsort($counts);
        $popular = array_slice($counts, 0, 5, true);

        $result = [];
        foreach ($popular as $bookId => $quantity) {
            $book = $this->bookRepo->findById($bookId);
            $result[] = [
                'title' => $book ? $book->getTitle() : 'Book #' . $bookId,
                'borrows' => $quantity,
            ];
        }

        return $result;
    }

    private function getRecentActivitiesFromLoans(array $loans): array
    {
        usort($loans, function ($a, $b) {
            $aDate = $a->getBorrowedAt();
            $bDate = $b->getBorrowedAt();
            if ($aDate === null || $bDate === null) {
                return $aDate === null ? 1 : -1;
            }
            return $bDate->getTimestamp() <=> $aDate->getTimestamp();
        });

        $activities = [];
        foreach ($loans as $loan) {
            if (count($activities) >= 6) {
                break;
            }

            $user = $this->userRepo->findById($loan->getUserId());
            $book = $this->bookRepo->findById($loan->getBookId());
            $status = $loan->getStatus()->getValue();

            $userName = $user ? $user->getName() : 'Unknown User';
            $bookTitle = $book ? $book->getTitle() : 'Unknown Book';

            $action = match ($status) {
                'active' => 'Borrowed',
                'returned' => 'Returned',
                'pending' => 'Requested',
                'overdue' => 'Overdue',
                default => 'Updated',
            };

            $activities[] = [
                'user' => $userName,
                'action' => sprintf('%s "%s"', $action, $bookTitle),
                'date' => $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('Y-m-d H:i') : date('Y-m-d H:i'),
            ];
        }

        return $activities;
    }
}
