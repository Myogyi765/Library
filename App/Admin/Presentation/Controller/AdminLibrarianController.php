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
        $this->setViewBasePath(BASE_PATH . '/view/admin/librarian/');
    }

    private function isAdmin(): bool
    {
        return $this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public function index(): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }

        $librarians = $this->librarianService->getAllLibrarians();
        $pageTitle = 'Manage Librarians';
        $content = BASE_PATH . '/view/admin/librarian/index.php';

        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            include $content;
            return;
        }

        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function create(): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
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
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/librarian');
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
            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'librarian_created',
                'Librarian created',
                'A new librarian account was created successfully.',
                '/admin/librarian'
            );

            $_SESSION['success_message'] = 'Librarian created successfully.';
            $this->redirect('/admin/librarian');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            $this->redirect('/admin/librarian/create');
        }
    }

    public function show(int $id): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }

        $librarian = $this->librarianService->getLibrarian($id);
        if (!$librarian) {
            $_SESSION['error_message'] = 'Librarian not found.';
            $this->redirect('/admin/librarian');
            return;
        }

        $pageTitle = 'View Librarian';
        $content = BASE_PATH . '/view/admin/librarian/view.php';

        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            include $content;
            return;
        }

        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function edit(int $id): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }

        $librarian = $this->librarianService->getLibrarian($id);
        if (!$librarian) {
            $_SESSION['error_message'] = 'Librarian not found.';
            $this->redirect('/admin/librarian');
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
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/librarian');
        }

        $name = $_POST['name'] ?? '';
        $department = $_POST['department'] ?? '';

        try {
            $this->librarianService->updateLibrarian($id, $name, $department);
            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'librarian_updated',
                'Librarian updated',
                'A librarian account was updated successfully.',
                '/admin/librarian'
            );

            $_SESSION['success_message'] = 'Librarian updated successfully.';
            $this->redirect('/admin/librarian');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            $this->redirect('/admin/librarian/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }

        try {
            $this->librarianService->deleteLibrarian($id);
            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'librarian_deleted',
                'Librarian deleted',
                'A librarian account was deleted successfully.',
                '/admin/librarian'
            );
            $_SESSION['success_message'] = 'Librarian deleted.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/librarian');
    }

    public function toggleStatus(int $id): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            exit;
        }

        try {
            $librarian = $this->librarianService->getLibrarian($id);
            if (!$librarian) {
                throw new \Exception('Librarian not found.');
            }

            $currentStatus = $librarian->getStatus(); 
            $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

            $this->librarianService->updateLibrarianStatus($id, $newStatus);
            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'librarian_status_toggled',
                'Librarian status updated',
                'A librarian account status was changed successfully.',
                '/admin/librarian'
            );

            $_SESSION['success_message'] = 'Librarian status updated to ' . ucfirst($newStatus) . '.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to toggle status: ' . $e->getMessage();
        }

        $this->redirect('/admin/librarian');
    }
}