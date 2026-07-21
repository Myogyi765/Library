<?php

namespace App\Librarian\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\UserStatus;
use App\Shared\Base\BaseController;
use DateTime;

class UserController extends BaseController
{
    private UserAuthenticator $authenticator;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserAuthenticator $authenticator,
        UserRepositoryInterface $userRepository
    ) {
        $this->authenticator = $authenticator;
        $this->userRepository = $userRepository;
    }

    private function isLibrarian(): bool
    {
        return $this->authenticator->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'librarian';
    }

    
    public function index(): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        $page = (int) ($_GET['page_num'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;
        $search = trim($_GET['search'] ?? '');

        if (!empty($search)) {
            $totalUsers = $this->userRepository->countByRoleWithSearch('user', $search);
        } else {
            $totalUsers = $this->userRepository->countByRole('user');
        }
        $totalPages = (int) ceil($totalUsers / $perPage);
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        if (!empty($search)) {
            $users = $this->userRepository->findByRoleWithSearchPaginated('user', $search, $offset, $perPage);
        } else {
            $users = $this->userRepository->findByRolePaginated('user', $offset, $perPage);
        }

        $viewData = [
            'users'        => $users,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'totalUsers'   => $totalUsers,
            'perPage'      => $perPage,
            'search'       => $search,
        ];

        $this->view('librarian-dashboard', [
            'pageTitle' => 'Manage Users',
            'content'   => BASE_PATH . '/view/librarian/users/index.php',
            ...$viewData
        ]);
    }

    public function create(): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        $this->view('librarian-dashboard', [
            'pageTitle' => 'Add User',
            'content'   => BASE_PATH . '/view/librarian/users/create.php'
        ]);
    }

    public function store(): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        try {
            $data = $_POST;
            $user = $this->createUserFromData($data);
            $this->userRepository->save($user);
            $_SESSION['success_message'] = 'User created successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create user: ' . $e->getMessage();
            $this->redirect('/librarian/users/create');
            return;
        }

        $this->redirect('/librarian/users');
    }

    public function show(int $id): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/librarian/users');
            return;
        }

        $this->view('librarian-dashboard', [
            'pageTitle' => 'User Profile',
            'content'   => BASE_PATH . '/view/librarian/users/view.php',
            'user'      => $user
        ]);
    }

    public function edit(int $id): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/librarian/users');
            return;
        }

        $this->view('librarian-dashboard', [
            'pageTitle' => 'Edit User',
            'content'   => BASE_PATH . '/view/librarian/users/edit.php',
            'user'      => $user
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                throw new \Exception('User not found.');
            }

            $this->updateUserFromData($user, $_POST);
            $this->userRepository->save($user);
            $_SESSION['success_message'] = 'User updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to update user: ' . $e->getMessage();
            $this->redirect('/librarian/users/edit/' . $id);
            return;
        }

        $this->redirect('/librarian/users');
    }

    public function delete(int $id): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        try {
            $this->userRepository->delete($id);
            $_SESSION['success_message'] = 'User deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete user: ' . $e->getMessage();
        }

        $this->redirect('/librarian/users');
    }

    public function toggleStatus(int $id): void
    {
        if (!$this->isLibrarian()) {
            $this->redirect('/login');
            return;
        }

        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                throw new \Exception('User not found.');
            }

            $currentStatus = $user->getStatus()->getValue();
            if ($currentStatus === 'active') {
                $newStatusString = 'inactive';
            } elseif ($currentStatus === 'inactive') {
                $newStatusString = 'active';
            } elseif ($currentStatus === 'pending') {
                $newStatusString = 'active';
            } else {
                $newStatusString = 'active';
            }

            $newStatus = UserStatus::fromString($newStatusString);
            $user->setStatus($newStatus);
            $this->userRepository->save($user);

            $_SESSION['success_message'] = 'User status updated to ' . ucfirst($newStatusString) . '.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to toggle status: ' . $e->getMessage();
        }

        $this->redirect('/librarian/users');
    }

    private function createUserFromData(array $data): User
    {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $status = $data['status'] ?? 'active';
        $loginMethod = $data['login_method'] ?? 'email';

        if (empty($name) || empty($password)) {
            throw new \InvalidArgumentException('Name and password are required.');
        }

        if (empty($email) && empty($phone)) {
            throw new \InvalidArgumentException('Either email OR phone is required.');
        }

        $emailVO = null;
        $phoneVO = null;

        if (!empty($email)) {
            $emailVO = new Email($email);
            if ($this->userRepository->findByEmail($emailVO)) {
                throw new \RuntimeException('Email already registered.');
            }
        }

        if (!empty($phone)) {
            $phoneVO = new Phone($phone);
            if ($this->userRepository->findByPhone($phoneVO)) {
                throw new \RuntimeException('Phone number already registered.');
            }
        }

        $finalLoginMethod = (!empty($email)) ? 'email' : 'phone';

        if (empty($email) && !empty($phone)) {
            $uniqueId = time() . '_' . bin2hex(random_bytes(3));
            $generatedEmail = 'u_' . $uniqueId . '@p.local';
            $emailVO = new Email($generatedEmail);
            $finalLoginMethod = 'phone';
        }

        $passwordVO = new Password($password);
        $statusVO = UserStatus::fromString($status);

        $roleId = $this->userRepository->getRoleIdByName('user');
        if (!$roleId) {
            throw new \RuntimeException('Default role "user" not found in database.');
        }

        $emailVerified = ($status === 'active' && !empty($email));
        $phoneVerified = ($status === 'active' && !empty($phone));

        return new User(
            null,
            $name,
            $emailVO,
            $passwordVO,
            $statusVO,
            $phoneVO,
            $roleId,
            'user',
            $emailVerified,
            $phoneVerified,
            $finalLoginMethod,
            null,
            new DateTime(),
            new DateTime(),
            null,
            null,
            null,
            null,
            null,
            null
        );
    }

    private function updateUserFromData(User $user, array $data): void
    {
        $name = trim($data['name'] ?? $user->getName());
        $email = trim($data['email'] ?? $user->getEmail()->getValue());
        $phone = trim($data['phone'] ?? ($user->getPhone() ? $user->getPhone()->getValue() : ''));
        $status = $data['status'] ?? $user->getStatus()->getValue();
        $password = $data['password'] ?? null;

        if ($email !== $user->getEmail()->getValue()) {
            $emailVO = new Email($email);
            if ($this->userRepository->findByEmail($emailVO)) {
                throw new \RuntimeException('Email already taken.');
            }
            $user->setEmail($emailVO);
        }

        $currentPhone = $user->getPhone() ? $user->getPhone()->getValue() : '';
        if ($phone !== $currentPhone) {
            $phoneVO = !empty($phone) ? new Phone($phone) : null;
            if ($phoneVO && $this->userRepository->findByPhone($phoneVO)) {
                throw new \RuntimeException('Phone already taken.');
            }
            $user->setPhone($phoneVO);
        }

        $user->setName($name);
        $user->setStatus(UserStatus::fromString($status));

        if (!empty($password)) {
            $user->setPassword(new Password($password));
        }
    }
}
