<?php
namespace App\Admin\Presentation\Controller;

use App\Book\Application\UseCase\GetBooks;
use App\Book\Application\UseCase\GetBook;
use App\Shared\Base\BaseController;

class AdminBookController extends BaseController
{
    private GetBooks $getBooks;
    private GetBook $getBook;

    public function __construct(GetBooks $getBooks, GetBook $getBook)
    {
        $this->getBooks = $getBooks;
        $this->getBook = $getBook;
    }

    public function index(): void
    {
        $books = $this->getBooks->execute();
        
      
        $this->view('admin-dashboard', [
            'pageTitle' => 'Manage Books',
            'content' => BASE_PATH . '/view/admin/books/index.php',
            'books' => $books
        ]);
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $book = $this->getBook->execute($id);

        $this->view('admin-dashboard', [
            'pageTitle' => 'Book Details',
            'content' => BASE_PATH . '/view/admin/books/view.php',
            'book' => $book
        ]);
    }
}