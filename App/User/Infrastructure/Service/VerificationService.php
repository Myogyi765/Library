<?php

namespace App\User\Infrastructure\Service;

use App\User\Domain\Entity\User;
use App\User\Domain\Service\VerificationServiceInterface;
use App\User\Infrastructure\Persistence\UserRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class VerificationService implements VerificationServiceInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function generateVerificationToken(User $user): string
    {
        return bin2hex(random_bytes(32));
    }

    public function generateVerificationCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function sendVerificationEmail(User $user, string $token, string $code): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
            $mail->SMTPDebug  = 0;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? $mail->Username,
                $_ENV['MAIL_FROM_NAME'] ?? 'Library System'
            );
            $mail->addAddress($user->getEmail()->getValue(), $user->getName());

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email';

            $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
            $verifyLink = $baseUrl . '/verify-email?token=' . urlencode($token);

            $viewPath = __DIR__ . '/../../../../view/emails/vertification.php';
            
            if (file_exists($viewPath)) {
                ob_start();
                extract([
                    'name'       => $user->getName(),
                    'verifyLink' => $verifyLink,
                    'code'       => $code,
                ]);
                include $viewPath;
                $mail->Body = ob_get_clean();
            } else {
                $mail->Body = "
                    <div style='font-family:Arial,sans-serif;padding:20px'>
                        <h2>Library Management System</h2>
                        <p>Hello <strong>{$user->getName()}</strong>,</p>
                        <p>Thank you for registering.</p>
                        <p>Click the button below to verify your email:</p>
                        <div style='text-align:center;margin:30px 0;'>
                            <a href='{$verifyLink}' style='display:inline-block;padding:12px 30px;background:#0077ff;color:white;text-decoration:none;border-radius:5px;'>Verify Email Address</a>
                        </div>
                        <p>Or use this verification code:</p>
                        <h1 style='color:#2563eb;font-size:40px;letter-spacing:8px'>{$code}</h1>
                        <p>This link and code expire in 15 minutes.</p>
                        <hr>
                        <p style='color:#999;font-size:12px;'>If you did not register, please ignore this email.</p>
                    </div>
                ";
            }

            $mail->AltBody = "Your verification code is: {$code}\n\nOr click this link to verify: {$verifyLink}";

            $mail->send();

        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            throw new \RuntimeException("Unable to send verification email. " . $mail->ErrorInfo);
        }
    }

    
    public function sendVerificationSMS(User $user, string $code): void
    {
        $phone = $user->getPhone()?->getValue();
        if (!$phone) {
            error_log("❌ No phone number for user ID " . $user->getId());
            return;
        }

        error_log("📱 [SMS] To: {$phone} | Code: {$code}");

        
    }

    public function sendPasswordResetEmail(User $user, string $resetLink): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
            $mail->SMTPDebug  = 0;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? $mail->Username,
                $_ENV['MAIL_FROM_NAME'] ?? 'Library System'
            );
            $mail->addAddress($user->getEmail()->getValue(), $user->getName());

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';

            $viewPath = __DIR__ . '/../../../../view/emails/reset-password.php';
            
            if (file_exists($viewPath)) {
                ob_start();
                extract([
                    'name'      => $user->getName(),
                    'resetLink' => $resetLink,
                ]);
                include $viewPath;
                $mail->Body = ob_get_clean();
            } else {
                $mail->Body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <title>Reset Password</title>
                        <style>
                            body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 40px; }
                            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                            .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; }
                            .header h1 { color: #4f46e5; margin: 0; }
                            .content { padding: 30px 0; }
                            .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; }
                            .btn:hover { background: #4338ca; }
                            .footer { text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; }
                            .warning { color: #dc2626; font-size: 12px; margin-top: 10px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>🔐 Reset Password</h1>
                            </div>
                            <div class='content'>
                                <p>Hi <strong>{$user->getName()}</strong>,</p>
                                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                                <p style='text-align: center; margin: 30px 0;'>
                                    <a href='{$resetLink}' class='btn'>Reset Password</a>
                                </p>
                                <p>Or copy and paste this link into your browser:</p>
                                <p style='word-break: break-all; background: #f3f4f6; padding: 10px; border-radius: 6px; font-size: 14px;'>
                                    <a href='{$resetLink}' style='color: #4f46e5;'>{$resetLink}</a>
                                </p>
                                <p class='warning'>⚠️ This link expires in 24 hours. If you didn't request this, please ignore this email.</p>
                            </div>
                            <div class='footer'>
                                <p>© 2026 Library Management System. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
            }

            $mail->AltBody = "Hi {$user->getName()},\n\nClick this link to reset your password:\n\n{$resetLink}\n\nThis link expires in 24 hours.\n\nIf you didn't request this, please ignore this email.\n\n- Library Team";

            $mail->send();

        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            throw new \RuntimeException("Unable to send password reset email. " . $mail->ErrorInfo);
        }
    }

    public function verifyEmail(string $token): ?User
    {
        $user = $this->userRepository->findByEmailVerificationToken($token);
        if ($user === null) {
            return null;
        }
        if (!$user->isVerificationValid()) {
            return null;
        }
        $user->setVerificationToken(null);
        $user->setVerificationExpiresAt(null);
        return $user;
    }

    public function verifyPhone(string $code): ?User
    {
        $user = $this->userRepository->findByPhoneVerificationCode($code);
        if ($user === null) {
            return null;
        }
        if (!$user->isVerificationValid()) {
            return null;
        }
        $user->setVerificationCode(null);
        $user->setVerificationExpiresAt(null);
        return $user;
    }

    public function isTokenValid(string $token): bool
    {
        $user = $this->userRepository->findByEmailVerificationToken($token);
        return $user !== null && $user->isVerificationValid();
    }

    public function isCodeValid(string $code): bool
    {
        $user = $this->userRepository->findByPhoneVerificationCode($code);
        return $user !== null && $user->isVerificationValid();
    }

    
    public function verifyEmailByCode(string $code): ?User
    {
        // We reuse the same repository method because both email and phone codes are stored in the same `verificationCode` column.
        $user = $this->userRepository->findByPhoneVerificationCode($code);
        if ($user === null) {
            return null;
        }
        if (!$user->isVerificationValid()) {
            return null;
        }
        $user->setVerificationCode(null);
        $user->setVerificationToken(null);
        $user->setVerificationExpiresAt(null);
        return $user;
    }
}
