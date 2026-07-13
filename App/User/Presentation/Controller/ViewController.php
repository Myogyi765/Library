<?php

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\Payment\Domain\Repository\PaymentRepositoryInterface; // ✅ ထည့်ပါ

class ViewController extends BaseController
{
    private Authorization $authorization;
    private UserRepositoryInterface $userRepo;
    private PaymentRepositoryInterface $paymentRepo; // ✅ ထည့်ပါ

    public function __construct(
        Authorization $authorization,
        UserRepositoryInterface $userRepo,
        PaymentRepositoryInterface $paymentRepo // ✅ ထည့်ပါ
    ) {
        parent::__construct();
        $this->authorization = $authorization;
        $this->userRepo = $userRepo;
        $this->paymentRepo = $paymentRepo; // ✅ ထည့်ပါ
    }

    public function home(): void
    {
        $this->render('home', [
            'title' => 'Home - Library Management System'
        ]);
    }

    public function profile(): void
    {
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_permissions']);
            unset($_SESSION['user_roles']);
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }
        
        if (!$this->authorization->hasPermission('view_profile')) {
            http_response_code(403);
            echo 'Access Denied';
            return;
        }
        require BASE_PATH . '/App/User/Presentation/View/profile.php';
    }

    public function editProfile(): void
    {
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_permissions']);
            unset($_SESSION['user_roles']);
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }

        if (!$this->authorization->hasPermission('edit_profile')) {
            http_response_code(403);
            echo '403 Forbidden - You do not have permission to edit profile.';
            exit;
        }

        require BASE_PATH . '/App/User/Presentation/View/profile.php';
    }

    /**
     * ✅ User Payment History (Refund Status အပါအဝင်)
     */
    public function payments(): void
    {
        // 1️⃣ Login check
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $userId = (int) $_SESSION['user_id'];

        // 2️⃣ Load user permissions (optional, if you want to check)
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_permissions']);
            unset($_SESSION['user_roles']);
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }

        // 3️⃣ Check permission (optional)
        if (!$this->authorization->hasPermission('view_payments')) {
            http_response_code(403);
            echo 'Access Denied';
            return;
        }

        // 4️⃣ Fetch payments for this user
        // Note: သင့် PaymentRepository မှာ findByUserId() method ရှိရပါမယ်။
        // မရှိသေးရင် PaymentRepository ထဲမှာ အောက်ပါ method ကိုထည့်ပါ။
        $payments = $this->paymentRepo->findByUserId($userId);

        // 5️⃣ Render view
        $this->render('payment/index', [
            'payments' => $payments,
            'title' => 'My Payments'
        ]);
    }

    /**
     * Update user profile with image upload
     */
    public function updateProfile(): void
    {
        // 1️⃣ Check login
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        try {
            $userId = (int) $_SESSION['user_id'];
            $user = $this->userRepo->findById($userId);
            if (!$user) {
                throw new \Exception('User not found.');
            }

            // 2️⃣ Get form data
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            // 3️⃣ Validation
            if (empty($name) || empty($email)) {
                throw new \Exception('Name and Email are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Please enter a valid email address.');
            }

            // 4️⃣ Update user data
            $user->setName($name);
            $user->setEmail(new Email($email));
            if (!empty($phone)) {
                $user->setPhone(new Phone($phone));
            }

            // 5️⃣ Handle profile image upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = BASE_PATH . '/public/uploads/profiles/';
                
                // Create directory if not exists
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

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                    // Delete old profile image if exists
                    if ($user->getProfileImage()) {
                        $oldPath = BASE_PATH . '/public/' . $user->getProfileImage();
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $user->setProfileImage('uploads/profiles/' . $newFileName);
                } else {
                    throw new \Exception('Failed to upload profile image.');
                }
            }

            // 6️⃣ Save to database
            $this->userRepo->save($user);

            // 7️⃣ Update session data
            $_SESSION['user_name'] = $user->getName();
            $_SESSION['user_email'] = $user->getEmail()->getValue();
            $_SESSION['user_phone'] = $user->getPhone()?->getValue();
            $_SESSION['user_profile_image'] = $user->getProfileImage();

            $_SESSION['success_message'] = 'Profile updated successfully!';
            $this->redirect(BASE_URL . '/profile');

        } catch (\Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect(BASE_URL . '/profile');
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = BASE_PATH . '/view/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        }
    }
}