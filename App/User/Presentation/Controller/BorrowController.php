<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Circulation\Application\Command\BorrowBookCommand;
use App\Circulation\Application\Handler\BorrowBookHandler;
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

    public function borrow(int $id): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        if ($userId === 0) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if (!$this->authorization->hasPermission('borrow_books')) {
            $_SESSION['error_message'] = 'You do not have permission to borrow books.';
            $this->redirect(BASE_URL . '/books/' . $id);
            return;
        }

        try {
            $command = new BorrowBookCommand($userId, $id);
            $this->handler->handle($command);

            $_SESSION['success_message'] = 'Borrow request submitted successfully. Waiting for librarian approval.';

           
            $this->createNotification(
                null,                           
                'librarian',                
                'borrow_request',
                'Borrow request submitted',
                'A user has requested to borrow a book.',
                '/librarian/dashboard?page=loans'
            );

            $this->redirect(BASE_URL . '/books/' . $id);

        } catch (\DomainException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect(BASE_URL . '/books/' . $id);

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Something went wrong. Please try again.';
            $this->redirect(BASE_URL . '/books/' . $id);
        }
    }
}