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
            $this->redirect('/login');
        }

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
            $this->redirect('/login');
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
            $this->redirect('/login');
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

    public function edit(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
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
            $this->redirect('/login');
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
            $this->redirect('/login');
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

    /**
     * Toggle librarian status (Enable / Disable)
     */
    public function toggleStatus(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        try {
            // Fetch the librarian
            $librarian = $this->librarianService->getLibrarian($id);
            if (!$librarian) {
                throw new \Exception('Librarian not found.');
            }

            // Determine the new status
            // Assuming the librarian entity has getStatus() and setStatus() methods.
            // Status can be 'active' or 'inactive' (adjust according to your database).
            $currentStatus = $librarian->getStatus(); // e.g., 'active'
            $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

            // Update status
            // Option A: If LibrarianService has a dedicated method:
            // $this->librarianService->updateLibrarianStatus($id, $newStatus);

            // Option B: If you have direct repository access via service, you can do:
            // $librarian->setStatus($newStatus);
            // $this->librarianService->saveLibrarian($librarian); // you would need to implement this

            // Since we only have the service, we can add a method to the service.
            // For now, we'll assume a method exists or we'll add it:
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