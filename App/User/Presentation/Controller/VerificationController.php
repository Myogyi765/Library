<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\VerificationServiceInterface;
use App\User\Domain\ValueObject\UserStatus;
use App\User\Exception\UserNotFoundException;
use App\User\Infrastructure\Security\UserAuthenticator;
use DateTime;

class VerificationController extends BaseController
{
    private VerificationServiceInterface $verificationService;
    private UserAuthenticator $authenticator;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        VerificationServiceInterface $verificationService,
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
        if (!$this->authenticator->isAuthenticated()) {
            $_SESSION['login_errors'] = ['Please login to verify your phone.'];
            $this->redirect(BASE_URL . '/home');
            return;
        }

        $user = $this->authenticator->getCurrentUser();

        if (!$user) {
            $_SESSION['login_errors'] = ['User not found.'];
            $this->redirect(BASE_URL . '/home');
            return;
        }

        if ($user->isPhoneVerified()) {
            $_SESSION['success_message'] = 'Your phone is already verified.';
            $this->redirect(BASE_URL . '/user-dashboard');
            return;
        }

        if (!isset($_SESSION['verification_code_sent']) || $_SESSION['verification_code_sent'] < time() - 60) {
            $code = $this->verificationService->generateVerificationCode();
            $expiresAt = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');

            $user->setVerificationCode($code);
            $user->setVerificationExpiresAt($expiresAt);
            $this->userRepository->save($user);

            $this->verificationService->sendVerificationSMS($user, $code);

            $_SESSION['verification_code_sent'] = time();
            $_SESSION['verification_message'] = 'A verification code has been sent to your phone.';
        }

        $this->view('verify-phone');
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
            $user = $this->verificationService->verifyPhone($code);

            if (!$user) {
                throw new \RuntimeException('Invalid or expired verification code.');
            }

            $user->verifyPhone();
            $user->setStatus(UserStatus::active());
            $user->setVerificationCode(null);
            $user->setVerificationExpiresAt(null);
            $this->userRepository->save($user);

            $this->authenticator->login($user);

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
        if (!$this->authenticator->isAuthenticated()) {
            $_SESSION['info_message'] = 'Please login to resend verification.';
            $this->redirect(BASE_URL . '/home');
            return;
        }

        $user = $this->authenticator->getCurrentUser();

        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect(BASE_URL . '/home');
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
                $_SESSION['success_message'] = 'Verification code has been resent successfully.';
            }

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to resend verification: ' . $e->getMessage();
        }

        $this->redirect(BASE_URL . '/user-dashboard');
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

            $this->authenticator->login($user);

            $_SESSION['success_message'] = 'Your email has been verified successfully!';
            $this->redirect(BASE_URL . '/user-dashboard');

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Verification failed: ' . $e->getMessage();
            $this->redirect(BASE_URL . '/verify?token=' . urlencode($token));
        }
    }
}
