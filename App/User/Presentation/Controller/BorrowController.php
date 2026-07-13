<?php

namespace App\User\Presentation\Controller;

use App\Loan\Application\Command\BorrowBookCommand;
use App\Loan\Application\Handler\BorrowBookHandler;
use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;

class BorrowController extends BaseController
{
    private BorrowBookHandler $handler;
    private Authorization $authorization;

    public function __construct(BorrowBookHandler $handler, Authorization $authorization)
    {
        parent::__construct();
        $this->handler = $handler;
        $this->authorization = $authorization;
    }

    public function borrow($id): void
    {
        $bookId = (int) $id;

        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        if (isset($_SESSION['user_id'])) {
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }

        if (!$this->authorization->hasPermission('borrow_books')) {
            $_SESSION['error_message'] = 'You do not have permission to borrow books.';
            $this->redirect('/books/' . $bookId);
            return;
        }

        try {
            $cmd = new BorrowBookCommand($userId, $bookId);
            $this->handler->handle($cmd);

            $_SESSION['success_message'] = 'Borrow request submitted successfully. Waiting for librarian approval.';
        } catch (\DomainException $e) {
            $_SESSION['error_message'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Something went wrong. Please try again.';
        }

        $this->redirect('/books/' . $bookId);
    }
}