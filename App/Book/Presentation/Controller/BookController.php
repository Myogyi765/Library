<?php
namespace App\Book\Presentation\Controller;

use App\Book\Application\UseCase\CreateBook;
use App\Book\Application\UseCase\GetBooks;
use App\Book\Application\UseCase\GetBook;
use App\Book\Application\UseCase\UpdateBook;
use App\Book\Application\UseCase\DeleteBook;
use App\Book\Application\DTO\BookDTO;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;

class BookController extends BaseController
{
    private CreateBook $createBook;
    private GetBooks $getBooks;
    private GetBook $getBook;
    private UpdateBook $updateBook;
    private DeleteBook $deleteBook;
    private UserAuthenticator $authenticator;
    private CategoryRepositoryInterface $categoryRepository;
    private Authorization $authorization;
    private BookRepositoryInterface $bookRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->bookRepo = $this->container->get('book.repository');
        $this->createBook = new CreateBook($this->bookRepo);
        $this->getBooks = new GetBooks($this->bookRepo);
        $this->getBook = new GetBook($this->bookRepo);
        $this->updateBook = new UpdateBook($this->bookRepo);
        $this->deleteBook = new DeleteBook($this->bookRepo);
        $this->authenticator = $this->container->get('user.authenticator');
        $this->categoryRepository = $this->container->get('category.repository');
        $this->authorization = $this->container->get('Authorization');
    }

    private function ensurePermissions(string $permission): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }

        if (!$this->authorization->hasPermission($permission)) {
            error_log("❌ Permission denied: {$permission} for user_id: " . ($_SESSION['user_id'] ?? 'unknown'));
            error_log("Session permissions: " . print_r($_SESSION['user_permissions'] ?? [], true));
            
            http_response_code(403);
            echo '403 Forbidden - You do not have permission to access this page.';
            exit;
        }
    }


    public function librarianIndex(): void
    {
        $this->ensurePermissions('view_books');

        $page = 'books';

        $pageNum = (int) ($_GET['page_num'] ?? 1);
        if ($pageNum < 1) $pageNum = 1;
        $perPage = 10;

        $search = trim($_GET['search'] ?? '');
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;

        $totalBooks = $this->bookRepo->countFiltered($search, $categoryId);
        $totalPages = ceil($totalBooks / $perPage);
        
        if ($pageNum > $totalPages && $totalPages > 0) {
            $pageNum = $totalPages;
        }

        $offset = ($pageNum - 1) * $perPage;
        $books = $this->bookRepo->findFilteredPaginated($search, $categoryId, $offset, $perPage);
        $categories = $this->categoryRepository->findAll();

        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category->getId()] = $category->getName();
        }

        $pageTitle = 'Books Management';
        $viewData = [
            'books' => $books,
            'categories' => $categories,
            'categoryMap' => $categoryMap,
            'currentPage' => $pageNum,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks,
            'perPage' => $perPage,
            'search' => $search,
            'categoryId' => $categoryId,
        ];

        $content = BASE_PATH . '/view/librarian/books/index.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }


    public function create(): void
    {
        $this->ensurePermissions('create_books');

        $page = 'books';
        $categories = $this->categoryRepository->findAll();
        
        $pageTitle = 'Add New Book';
        $viewData = ['categories' => $categories];
        $content = BASE_PATH . '/view/librarian/books/create.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }


    public function store(): void
    {
        $this->ensurePermissions('create_books');

        $dto = new BookDTO();
        $dto->title = trim($_POST['title'] ?? '');
        $dto->author = trim($_POST['author'] ?? '');
        $dto->isbn = trim($_POST['isbn'] ?? null);
        $dto->categoryId = (int)$_POST['category_id'];
        $dto->description = trim($_POST['description'] ?? null);
        $dto->quantity = (int)$_POST['quantity'];
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

            $this->createNotification(
                null,
                'admin',
                'book_created',
                'New book added',
                'A new book has been added to the catalog by librarian.',
                '/admin/books'
            );

            $_SESSION['success_message'] = 'Book created successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create book: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/librarian/books');
        exit;
    }


    public function edit(int $id): void
    {
        $this->ensurePermissions('edit_books');

        $book = $this->getBook->execute($id);
        if (!$book) {
            $_SESSION['error_message'] = 'Book not found.';
            header('Location: ' . BASE_URL . '/librarian/books');
            exit;
        }
        $categories = $this->categoryRepository->findAll();

        $page = 'books';
        $pageTitle = 'Edit Book';
        
        $viewData = [
            'book' => $book,
            'categories' => $categories,
        ];
        $content = BASE_PATH . '/view/librarian/books/edit.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }


    public function update(int $id): void
    {
        $this->ensurePermissions('edit_books');

        $dto = new BookDTO();
        $dto->id = $id;
        $dto->title = trim($_POST['title'] ?? '');
        $dto->author = trim($_POST['author'] ?? '');
        $dto->isbn = trim($_POST['isbn'] ?? null);
        $dto->categoryId = (int)$_POST['category_id'];
        $dto->description = trim($_POST['description'] ?? null);
        $dto->quantity = (int)$_POST['quantity'];
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
        header('Location: ' . BASE_URL . '/librarian/books');
        exit;
    }


    public function delete(int $id): void
    {
        $this->ensurePermissions('delete_books');

        try {
            $this->deleteBook->execute($id);
            $_SESSION['success_message'] = 'Book deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete book: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/librarian/books');
        exit;
    }


    public function publicIndex(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }

        if (!$this->authorization->hasPermission('view_books')) {
            http_response_code(403);
            echo 'Access Denied';
            return;
        }

        $page = (int) ($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 12;

        $search = trim($_GET['search'] ?? '');
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;

        $totalBooks = $this->bookRepo->countFiltered($search, $categoryId);
        $totalPages = ceil($totalBooks / $perPage);
        
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $books = $this->bookRepo->findFilteredPaginated($search, $categoryId, $offset, $perPage);
        $categories = $this->categoryRepository->findAll();

        $this->view('public/books/index', [
            'books' => $books,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks,
            'perPage' => $perPage,
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }


    public function show(int $id): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }

        if (!$this->authorization->hasPermission('view_books')) {
            http_response_code(403);
            echo 'Access Denied';
            return;
        }

        $book = $this->getBook->execute($id);
        if (!$book) {
            http_response_code(404);
            echo 'Book not found.';
            return;
        }
        $categories = $this->categoryRepository->findAll();
        $this->view('public/books/view', ['book' => $book, 'categories' => $categories]);
    }
}