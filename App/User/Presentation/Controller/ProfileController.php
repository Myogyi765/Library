<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;

class ProfileController extends BaseController
{
    private Authorization $authorization;
    private UserRepositoryInterface $userRepo;
    private PaymentRepositoryInterface $paymentRepo;

    public function __construct(
        Authorization $authorization,
        UserRepositoryInterface $userRepo,
        PaymentRepositoryInterface $paymentRepo
    ) {
        parent::__construct();
        $this->authorization = $authorization;
        $this->userRepo = $userRepo;
        $this->paymentRepo = $paymentRepo;
    }


    public function profile(): void
    {
        if (!$this->checkPermission('view_profile')) {
            $this->sendForbidden('Access Denied');
            return;
        }

        $this->view('user/profile', [
            'user' => $this->getCurrentUser()
        ]);
    }

    public function editProfile(): void
    {
        if (!$this->checkPermission('edit_profile')) {
            $this->sendForbidden('You do not have permission to edit profile.');
            return;
        }

        $this->view('user/profile_edit', [
            'user' => $this->getCurrentUser()
        ]);
    }

    public function payments(): void
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if (!$this->checkPermission('view_payments')) {
            $this->sendForbidden('Access Denied');
            return;
        }

        $payments = $this->paymentRepo->findByUserId($userId);

        $this->view('payment/index', [
            'payments' => $payments,
            'title' => 'My Payments'
        ]);
    }

    public function updateProfile(): void
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        try {
            $user = $this->userRepo->findById($userId);
            if (!$user) {
                throw new \Exception('User not found.');
            }

            $this->validateAndUpdateUserInfo($user);
            $this->handleProfileImageUpload($user, $userId);
            $this->userRepo->save($user);
            $this->updateSessionUserData($user);

            $_SESSION['success_message'] = 'Profile updated successfully!';
            $this->redirect(BASE_URL . '/profile');

        } catch (\Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect(BASE_URL . '/profile');
        }
    }

    private function checkPermission(string $permission): bool
    {
        $userId = $this->getCurrentUserId();
        if ($userId) {
            $this->authorization->loadUserPermissions($userId);
        }
        return $this->authorization->hasPermission($permission);
    }

    private function getCurrentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    private function getCurrentUser()
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return null;
        }
        return $this->userRepo->findById($userId);
    }

    private function sendForbidden(string $message = 'Access Denied'): void
    {
        http_response_code(403);
        echo $message;
        exit;
    }

    private function validateAndUpdateUserInfo($user): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($email)) {
            throw new \Exception('Name and Email are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Please enter a valid email address.');
        }

        $user->setName($name);
        $user->setEmail(new Email($email));

        if (!empty($phone)) {
            $user->setPhone(new Phone($phone));
        }
    }

    private function handleProfileImageUpload($user, int $userId): void
    {
        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $uploadDir = BASE_PATH . '/public/uploads/profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($_FILES['profile_image']['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('Invalid image format. Allowed: JPG, PNG, GIF, WEBP.');
        }

        $newFileName = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
            throw new \Exception('Failed to upload profile image.');
        }

        if ($user->getProfileImage()) {
            $oldPath = BASE_PATH . '/public/' . $user->getProfileImage();
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $user->setProfileImage('uploads/profiles/' . $newFileName);
    }

    private function updateSessionUserData($user): void
    {
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_email'] = $user->getEmail()->getValue();
        $_SESSION['user_phone'] = $user->getPhone()?->getValue();
        $_SESSION['user_profile_image'] = $user->getProfileImage();
    }
}