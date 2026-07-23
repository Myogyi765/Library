<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\VerificationServiceInterface;
use App\User\Domain\ValueObject\UserStatus;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Exception\UserNotFoundException;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Service\VerificationService;
use DateTime;

class VerificationController extends BaseController
{
    private VerificationService $verificationService;
    private UserAuthenticator $authenticator;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        VerificationService $verificationService,
        UserAuthenticator $authenticator,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct();
        $this->verificationService = $verificationService;
        $this->authenticator = $authenticator;
        $this->userRepository = $userRepository;
    }

    
    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token) && isset($_SESSION['verification_token'])) {
            $token = $_SESSION['verification_token'];
        }

        if (isset($_SESSION['just_registered']) && $_SESSION['just_registered'] === true) {
            unset($_SESSION['just_registered']);
            $message = $_SESSION['verification_message'] ?? 'Account created successfully! Please check your email to verify your account.';
            $this->view('verify', [
                'message' => $message,
                'success' => true,
                'token'   => $token,
            ]);
            return;
        }

        if (empty($token)) {
            $this->view('verify', [
                'message' => 'Verification token is missing. Please check your email or request a new one.',
                'success' => false,
                'token'   => '',
            ]);
            return;
        }

        $this->view('verify', [
            'token'   => $token,
            'message' => 'Enter the 6-digit code sent to your email.',
            'success' => true,
        ]);
    }

    
    public function showVerifyPhone(): void
    {
        $user = null;

        // 1️⃣ If logged in, get user from authenticator
        if ($this->authenticator->isAuthenticated()) {
            $user = $this->authenticator->getCurrentUser();
        } 
        // 2️⃣ If just registered (not logged in), get from session
        elseif (isset($_SESSION['just_registered']) && isset($_SESSION['register_user_id'])) {
            $user = $this->userRepository->findById((int)$_SESSION['register_user_id']);
            // Keep just_registered so that verifyPhone can still use it
        }

        if (!$user) {
            $_SESSION['login_errors'] = ['Please login to verify your phone.'];
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if ($user->isPhoneVerified()) {
            $_SESSION['success_message'] = 'Your phone is already verified.';
            if (isset($_SESSION['just_registered'])) {
                $this->authenticator->login($user);
                unset($_SESSION['just_registered']);
            }
            $this->redirect(BASE_URL . '/user-dashboard');
            return;
        }

        $phone = $user->getPhone()?->getValue() ?? '';
        if (empty($phone)) {
            $_SESSION['error_message'] = 'No phone number registered. Please update your profile.';
            $this->redirect(BASE_URL . '/profile');
            return;
        }

        // Generate code if not recently sent (60s cooldown)
        if (!isset($_SESSION['verification_code_sent']) || $_SESSION['verification_code_sent'] < time() - 60) {
            $code = $this->verificationService->generateVerificationCode();
            $expiresAt = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');

            $user->setVerificationCode($code);
            $user->setVerificationExpiresAt($expiresAt);
            $this->userRepository->save($user);

            // Send SMS
            $this->verificationService->sendVerificationSMS($user, $code);

            // ✅ Always store code in session for debugging (no env check)
            $_SESSION['verification_code'] = $code;
            $_SESSION['verification_phone'] = $phone;

            $_SESSION['verification_code_sent'] = time();
            $_SESSION['verification_message'] = 'A verification code has been sent to your phone.';
        }

        $debugCode = $_SESSION['verification_code'] ?? null;

        $this->view('verify-phone', [
            'phone'     => $phone,
            'debugCode' => $debugCode,
        ]);
    }

    
    public function verifyPhone(): void
    {
        $code = $_POST['code'] ?? '';

        if (empty($code)) {
            $_SESSION['error_message'] = 'Please enter the verification code.';
            $this->redirect(BASE_URL . '/verify-phone');
            return;
        }

        try {
            // 1️⃣ Get user via service (checks DB)
            $user = $this->verificationService->verifyPhone($code);

            if (!$user) {
                throw new \RuntimeException('Invalid or expired verification code.');
            }

            // 2️⃣ Update user status
            $user->verifyPhone();
            $user->setStatus(UserStatus::active());
            $user->setVerificationCode(null);
            $user->setVerificationExpiresAt(null);
            $this->userRepository->save($user);

            // 3️⃣ Log in the user (if not already)
            if (!$this->authenticator->isAuthenticated()) {
                $this->authenticator->login($user);
            }

            // 4️⃣ Clear session flags
            unset($_SESSION['verification_code'], $_SESSION['verification_phone']);
            unset($_SESSION['just_registered'], $_SESSION['register_user_id']);

            $_SESSION['success_message'] = 'Your phone has been verified successfully! Welcome to the Library Management System.';
            $this->redirect(BASE_URL . '/user-dashboard');

        } catch (UserNotFoundException $e) {
            $_SESSION['error_message'] = 'Invalid verification code. Please try again.';
            $this->redirect(BASE_URL . '/verify-phone');

        } catch (\RuntimeException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect(BASE_URL . '/verify-phone');

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'An error occurred during verification. Please try again.';
            $this->redirect(BASE_URL . '/verify-phone');
        }
    }

    
    public function resendVerification(): void
    {
        $user = null;

        // 1️⃣ If logged in, get user from authenticator
        if ($this->authenticator->isAuthenticated()) {
            $user = $this->authenticator->getCurrentUser();
        } 
        // 2️⃣ If just registered (not logged in), get from session
        elseif (isset($_SESSION['just_registered']) && isset($_SESSION['register_user_id'])) {
            $user = $this->userRepository->findById((int)$_SESSION['register_user_id']);
            // Keep just_registered so that showVerifyPhone can still use it
        }

        if (!$user) {
            $_SESSION['error_message'] = 'User not found. Please login to resend verification.';
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // Prevent excessive resend (60s cooldown)
        if (isset($_SESSION['last_resend_time']) && $_SESSION['last_resend_time'] > time() - 60) {
            $_SESSION['error_message'] = 'Please wait 60 seconds before requesting a new code.';
            $this->redirect(BASE_URL . ($user->getLoginMethod() === 'phone' ? '/verify-phone' : '/verify'));
            return;
        }

        try {
            $token = $this->verificationService->generateVerificationToken($user);
            $code = $this->verificationService->generateVerificationCode();
            $expiresAt = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

            $user->setVerificationToken($token);
            $user->setVerificationCode($code);
            $user->setVerificationExpiresAt($expiresAt);
            $this->userRepository->save($user);

            if ($user->getLoginMethod() === 'email') {
                $this->verificationService->sendVerificationEmail($user, $token, $code);
                $_SESSION['success_message'] = 'Verification email has been resent successfully.';
            } else {
                $this->verificationService->sendVerificationSMS($user, $code);
                // ✅ Always store in session for debug (no env check)
                $_SESSION['verification_code'] = $code;
                $_SESSION['verification_phone'] = $user->getPhone()?->getValue();
                $_SESSION['success_message'] = 'Verification code has been resent successfully.';
            }

            $_SESSION['last_resend_time'] = time();
            $_SESSION['verification_code_sent'] = time();

            // Redirect to appropriate verification page
            if ($user->getLoginMethod() === 'phone') {
                $this->redirect(BASE_URL . '/verify-phone');
            } else {
                $this->redirect(BASE_URL . '/verify');
            }

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to resend verification: ' . $e->getMessage();
            $this->redirect(BASE_URL . '/user-dashboard');
        }
    }

    
    public function verifyEmailWithCode(): void
    {
        $code = $_POST['code'] ?? '';
        $token = $_POST['token'] ?? $_SESSION['verification_token'] ?? '';

        if (empty($code)) {
            $_SESSION['error_message'] = 'Please enter the verification code.';
            $this->redirect(BASE_URL . '/verify');
            return;
        }

        try {
            $user = $this->verificationService->verifyEmailByCode($code);

            if (!$user) {
                throw new \RuntimeException('Invalid or expired verification code.');
            }

            $user->verifyEmail();
            $user->setStatus(UserStatus::active());
            $user->setVerificationToken(null);
            $user->setVerificationCode(null);
            $user->setVerificationExpiresAt(null);
            $this->userRepository->save($user);

            if (!$this->authenticator->isAuthenticated()) {
                $this->authenticator->login($user);
            }

            $_SESSION['success_message'] = 'Your email has been verified successfully!';
            $this->redirect(BASE_URL . '/user-dashboard');

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Verification failed: ' . $e->getMessage();
            $this->redirect(BASE_URL . '/verify?token=' . urlencode($token));
        }
    }

    // ─── Password Reset Methods ───────────────────────────────────────

    public function showForgotForm(): void
    {
        $this->view('auth/forgot-password', [
            'pageTitle' => 'Forgot Password'
        ]);
    }

    public function sendResetLink(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['error_message'] = 'Please enter your email address.';
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        try {
            $emailVO = new Email($email);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = 'Invalid email address.';
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $user = $this->userRepository->findByEmail($emailVO);

        if (!$user) {
            $_SESSION['success_message'] = 'If your email is registered, you will receive a reset link.';
            $this->redirect(BASE_URL . '/login');
            return;
        }

        try {
            $token = $this->verificationService->generateVerificationToken($user);
            $resetLink = BASE_URL . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email);

            $user->setVerificationToken($token);
            $this->userRepository->save($user);

            $this->verificationService->sendPasswordResetEmail($user, $resetLink);

            $_SESSION['success_message'] = 'Password reset link has been sent to your email.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to send reset link: ' . $e->getMessage();
        }

        $this->redirect(BASE_URL . '/login');
    }

    public function showResetForm(): void
    {
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';

        if (empty($token) || empty($email)) {
            $_SESSION['error_message'] = 'Invalid reset link.';
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        try {
            $emailVO = new Email($email);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = 'Invalid email address.';
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $user = $this->userRepository->findByEmail($emailVO);

        if (!$user || $user->getVerificationToken() !== $token) {
            $_SESSION['error_message'] = 'Invalid or expired reset link.';
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $this->view('auth/reset-password', [
            'pageTitle' => 'Reset Password',
            'token'     => $token,
            'email'     => $email
        ]);
    }

    public function updatePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $token = trim($_POST['token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordConfirm = trim($_POST['password_confirm'] ?? '');

        if (empty($token) || empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'All fields are required.';
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['error_message'] = 'Password must be at least 6 characters.';
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            return;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error_message'] = 'Passwords do not match.';
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            return;
        }

        try {
            $emailVO = new Email($email);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = 'Invalid email address.';
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $user = $this->userRepository->findByEmail($emailVO);

        if (!$user || $user->getVerificationToken() !== $token) {
            $_SESSION['error_message'] = 'Invalid or expired reset link.';
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        try {
            $passwordVO = new Password($password);
            $user->setPassword($passwordVO);
            $user->setVerificationToken(null);
            $this->userRepository->save($user);

            $_SESSION['success_message'] = 'Password updated successfully. Please login with your new password.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to update password: ' . $e->getMessage();
        }

        $this->redirect(BASE_URL . '/login');
    }
}
