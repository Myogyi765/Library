<?php

namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\Librarian\Application\Service\LibrarianService;
use App\Librarian\Application\DTO\RegisterDTO;
use App\Shared\Base\BaseController;

class AdminLibrarianController extends BaseController
{
    private UserAuthenticator $userAuth;
    private LibrarianService $librarianService;

    public function __construct(UserAuthenticator $userAuth, LibrarianService $librarianService)
    {
        $this->userAuth = $userAuth;
        $this->librarianService = $librarianService;
        // Set view base path for librarian partials
        $this->setViewBasePath(BASE_PATH . '/view/admin/librarian/');
    }

    private function isAdmin(): bool
    {
        return $this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    /**
     * Show list of librarians
     */
    public function index(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // ✅ Fetch all librarians from the service
        $librarians = $this->librarianService->getAllLibrarians();
        $pageTitle = 'Manage Librarians';
        $content = BASE_PATH . '/view/admin/librarian/index.php';

        // AJAX request – return partial view only
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            include $content;
            return;
        }

        // Full page load – use single layout
        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function create(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $pageTitle = 'Add Librarian';
        $content = BASE_PATH . '/view/admin/librarian/create.php';

        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            include $content;
            return;
        }

        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function store(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/librarian');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $department = $_POST['department'] ?? 'General';

        try {
            $dto = new RegisterDTO();
            $dto->name = $name;
            $dto->email = $email;
            $dto->password = $password;
            $dto->department = $department;

            $this->librarianService->register($dto);

            $_SESSION['success_message'] = 'Librarian created successfully.';
            header('Location: ' . BASE_URL . '/admin/librarian');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/librarian/create');
            exit;
        }
    }

    public function edit(int $id): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $librarian = $this->librarianService->getLibrarian($id);
        if (!$librarian) {
            $_SESSION['error_message'] = 'Librarian not found.';
            header('Location: ' . BASE_URL . '/admin/librarian');
            exit;
        }

        $pageTitle = 'Edit Librarian';
        $content = BASE_PATH . '/view/admin/librarian/edit.php';

        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            include $content;
            return;
        }

        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function update(int $id): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/librarian');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $department = $_POST['department'] ?? '';

        try {
            $this->librarianService->updateLibrarian($id, $name, $department);

            $_SESSION['success_message'] = 'Librarian updated successfully.';
            header('Location: ' . BASE_URL . '/admin/librarian');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/librarian/edit/' . $id);
            exit;
        }
    }

    public function delete(int $id): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        try {
            $this->librarianService->deleteLibrarian($id);
            $_SESSION['success_message'] = 'Librarian deleted.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/librarian');
        exit;
    }
}