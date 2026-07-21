<?php
namespace App\Admin\Presentation\Controller;

use App\Book\Application\UseCase\GetBooks;
use App\Book\Application\UseCase\GetBook;
use App\Book\Application\UseCase\CreateBook;
use App\Book\Application\UseCase\UpdateBook;
use App\Book\Application\UseCase\DeleteBook;
use App\Book\Application\DTO\BookDTO;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Base\BaseController;
use App\User\Infrastructure\Security\UserAuthenticator;

class AdminBookController extends BaseController
{
    private GetBooks $getBooks;
    private GetBook $getBook;
    private CreateBook $createBook;
    private UpdateBook $updateBook;
    private DeleteBook $deleteBook;
    private BookRepositoryInterface $bookRepo;
    private CategoryRepositoryInterface $categoryRepo;
    private UserAuthenticator $authenticator;

    public function __construct(
        GetBooks $getBooks,
        GetBook $getBook,
        CreateBook $createBook,
        UpdateBook $updateBook,
        DeleteBook $deleteBook,
        BookRepositoryInterface $bookRepo,
        CategoryRepositoryInterface $categoryRepo,
        UserAuthenticator $authenticator
    ) {
        $this->getBooks = $getBooks;
        $this->getBook = $getBook;
        $this->createBook = $createBook;
        $this->updateBook = $updateBook;
        $this->deleteBook = $deleteBook;
        $this->bookRepo = $bookRepo;
        $this->categoryRepo = $categoryRepo;
        $this->authenticator = $authenticator;
    }

    private function isAdmin(): bool
    {
        return $this->authenticator->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    private function ensureAdmin(): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }
    }

    public function index(): void
    {
        $this->ensureAdmin();

        $page = (int) ($_GET['page_num'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;

        $search = trim($_GET['search'] ?? '');
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;

        $totalBooks = $this->bookRepo->countFiltered($search, $categoryId);
        $totalPages = ceil($totalBooks / $perPage);
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
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

    public function create(): void
    {
        $this->ensureAdmin();
        $categories = $this->categoryRepo->findAll();

        $this->view('admin-dashboard', [
            'pageTitle'   => 'Add New Book',
            'content'     => BASE_PATH . '/view/admin/books/create.php',
            'categories'  => $categories
        ]);
    }

    public function store(): void
    {
        $this->ensureAdmin();

        $dto = new BookDTO();
        $dto->title = trim($_POST['title'] ?? '');
        $dto->author = trim($_POST['author'] ?? '');
        $dto->isbn = trim($_POST['isbn'] ?? null);
        $dto->categoryId = (int) ($_POST['category_id'] ?? 0);
        $dto->description = trim($_POST['description'] ?? null);
        $dto->quantity = (int) ($_POST['quantity'] ?? 0);
        $dto->availableQuantity = $dto->quantity;

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/books/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $filename);
            $dto->coverImage = '/uploads/books/' . $filename;
        }

        try {
            $this->createBook->execute($dto);
            $_SESSION['success_message'] = 'Book created successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create book: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/books');
        exit;
    }

    public function show(int $id): void
    {
        $this->ensureAdmin();
        $book = $this->getBook->execute($id);
        if (!$book) {
            $_SESSION['error_message'] = 'Book not found.';
            header('Location: ' . BASE_URL . '/admin/books');
            exit;
        }

        $category = $this->categoryRepo->findById($book->getCategoryId());
        $categoryName = $category ? $category->getName() : 'Uncategorized';

        $this->view('admin-dashboard', [
            'pageTitle'    => 'Book Details',
            'content'      => BASE_PATH . '/view/admin/books/view.php',
            'book'         => $book,
            'categoryName' => $categoryName, 
        ]);
    }

    public function edit(int $id): void
    {
        $this->ensureAdmin();
        $book = $this->getBook->execute($id);
        if (!$book) {
            $_SESSION['error_message'] = 'Book not found.';
            header('Location: ' . BASE_URL . '/admin/books');
            exit;
        }
        $categories = $this->categoryRepo->findAll();

        $this->view('admin-dashboard', [
            'pageTitle'   => 'Edit Book',
            'content'     => BASE_PATH . '/view/admin/books/edit.php',
            'book'        => $book,
            'categories'  => $categories
        ]);
    }

    public function update(int $id): void
    {
        $this->ensureAdmin();

        $dto = new BookDTO();
        $dto->id = $id;
        $dto->title = trim($_POST['title'] ?? '');
        $dto->author = trim($_POST['author'] ?? '');
        $dto->isbn = trim($_POST['isbn'] ?? null);
        $dto->categoryId = (int) ($_POST['category_id'] ?? 0);
        $dto->description = trim($_POST['description'] ?? null);
        $dto->quantity = (int) ($_POST['quantity'] ?? 0);
        $dto->availableQuantity = $dto->quantity;

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/books/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $filename);
            $dto->coverImage = '/uploads/books/' . $filename;
        }

        try {
            $this->updateBook->execute($dto);
            $_SESSION['success_message'] = 'Book updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to update book: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/books');
        exit;
    }

    public function delete(int $id): void
    {
        $this->ensureAdmin();
        try {
            $this->deleteBook->execute($id);
            $_SESSION['success_message'] = 'Book deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete book: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/books');
        exit;
    }
}