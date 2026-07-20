<?php
namespace App\Admin\Presentation\Controller;

use App\Book\Application\UseCase\GetBooks;
use App\Book\Application\UseCase\GetBook;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Base\BaseController;

class AdminBookController extends BaseController
{
    private GetBooks $getBooks;
    private GetBook $getBook;
    private BookRepositoryInterface $bookRepo;
    private CategoryRepositoryInterface $categoryRepo;

    public function __construct(
        GetBooks $getBooks,
        GetBook $getBook,
        BookRepositoryInterface $bookRepo,
        CategoryRepositoryInterface $categoryRepo
    ) {
        $this->getBooks = $getBooks;
        $this->getBook = $getBook;
        $this->bookRepo = $bookRepo;
        $this->categoryRepo = $categoryRepo;
    }


    public function index(): void
    {
        $page = (int) ($_GET['page_num'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;

        $search = trim($_GET['search'] ?? '');
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;

        $totalBooks = $this->bookRepo->countFiltered($search, $categoryId);
        $totalPages = ceil($totalBooks / $perPage);
        
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $books = $this->bookRepo->findFilteredPaginated($search, $categoryId, $offset, $perPage);
        $categories = $this->categoryRepo->findAll();

        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category->getId()] = $category->getName();
        }

        $this->view('admin-dashboard', [
            'pageTitle'   => 'Manage Books',
            'content'     => BASE_PATH . '/view/admin/books/index.php',
            'books'       => $books,
            'categories'  => $categories,
            'categoryMap' => $categoryMap,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalBooks'  => $totalBooks,
            'perPage'     => $perPage,
            'search'      => $search,
            'categoryId'  => $categoryId,
        ]);
    }


    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $book = $this->getBook->execute($id);

        $this->view('admin-dashboard', [
            'pageTitle' => 'Book Details',
            'content'   => BASE_PATH . '/view/admin/books/view.php',
            'book'      => $book
        ]);
    }


    public function create(): void
    {
        $categories = $this->categoryRepo->findAll();

        $this->view('admin-dashboard', [
            'pageTitle'   => 'Add New Book',
            'content'     => BASE_PATH . '/view/admin/books/create.php',
            'categories'  => $categories
        ]);
    }


    public function store(): void
    {
        $_SESSION['success_message'] = 'Book created successfully.';
        header('Location: ' . BASE_URL . '/admin/books');
        exit;
    }


    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $book = $this->getBook->execute($id);
        $categories = $this->categoryRepo->findAll();

        $this->view('admin-dashboard', [
            'pageTitle'   => 'Edit Book',
            'content'     => BASE_PATH . '/view/admin/books/edit.php',
            'book'        => $book,
            'categories'  => $categories
        ]);
    }


    public function update(): void
    {
        $_SESSION['success_message'] = 'Book updated successfully.';
        header('Location: ' . BASE_URL . '/admin/books');
        exit;
    }


    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $_SESSION['success_message'] = 'Book deleted successfully.';
        header('Location: ' . BASE_URL . '/admin/books');
        exit;
    }
}