<?php

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Infrastructure\Security\UserAuthenticator;

class DashboardController extends BaseController
{
    private UserAuthenticator $authenticator;
    
    public function __construct($container = null)
    {
        parent::__construct($container);
        $this->authenticator = $this->container->get('user.authenticator');
    }
    
    public function userDashboard(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $_SESSION['login_errors'][] = 'Please login to access the dashboard';
            $this->redirect('/home');
            return;
        }
        
        $user = $this->authenticator->getCurrentUser();
        
        if ($user) {
            $method = $user->getLoginMethod();
            if ($method === 'email' && !$user->isEmailVerified()) {
                $verificationService = $this->container->get('verification.service');
                $token = $verificationService->generateVerificationToken($user);
                $code = $verificationService->generateVerificationCode();
                $user->setVerificationToken($token);
                $user->setVerificationCode($code);
                $this->container->get('user.repository')->save($user);
                $verificationService->sendVerificationEmail($user, $token, $code);
                
                $_SESSION['warning_message'] = 'Please verify your email address. A new verification email has been sent.';
                $this->redirect('/home');
                return;
            }
            
            if ($method === 'phone' && !$user->isPhoneVerified()) {
                // You need to implement a phone verification page
                $this->redirect('/verify-phone');
                return;
            }
        }
        
        require_once $this->basePath . '/view/user-dashboard.php';
    }
    
    public function adminDashboard(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $_SESSION['login_errors'][] = 'Please login to access the dashboard';
            $this->redirect('/home');
            return;
        }
        
        $user = $this->authenticator->getCurrentUser();
        
        if ($user->getRole() !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access the admin dashboard.';
            $this->redirect('/user-dashboard');
            return;
        }
        
        require_once $this->basePath . '/view/admin-dashboard.php';
    }
}