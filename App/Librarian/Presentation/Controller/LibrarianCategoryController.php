<?php
namespace App\Librarian\Presentation\Controller;

use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Book\Domain\Entity\Category;

class LibrarianCategoryController extends BaseController
{
    private CategoryRepositoryInterface $categoryRepository;
    private UserAuthenticator $authenticator;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->categoryRepository = $container->get('category.repository');
        $this->authenticator = $container->get('user.authenticator');
    }

    private function isLibrarian(): bool
    {
        return $this->authenticator->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'librarian';
    }

    public function index(): void
    {
        if (!$this->isLibrarian()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $categories = $this->categoryRepository->findAll();
        $pageTitle = 'Manage Categories';
        $viewData = ['categories' => $categories]; 
        $content = BASE_PATH . '/view/librarian/categories/index.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    public function create(): void
    {
        if (!$this->isLibrarian()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $pageTitle = 'Add Category';
        $content = BASE_PATH . '/view/librarian/categories/create.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    public function store(): void
    {
        if (!$this->isLibrarian()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $_SESSION['error_message'] = 'Category name is required.';
            header('Location: ' . BASE_URL . '/librarian/categories/create');
            exit;
        }

        try {
            $existing = $this->categoryRepository->findByName($name);
            if ($existing) {
                $_SESSION['error_message'] = 'Category "' . htmlspecialchars($name) . '" already exists.';
                header('Location: ' . BASE_URL . '/librarian/categories/create');
                exit;
            }

            $category = new Category(null, $name, $description ?: null);
            $this->categoryRepository->save($category);

            $_SESSION['success_message'] = 'Category "' . htmlspecialchars($name) . '" created successfully.';
            header('Location: ' . BASE_URL . '/librarian/categories');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create category: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/librarian/categories/create');
        }
        exit;
    }

    public function delete(int $id): void
    {
        if (!$this->isLibrarian()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        try {
            $this->categoryRepository->delete($id);
            $_SESSION['success_message'] = 'Category deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete category: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/librarian/categories');
        exit;
    }
}