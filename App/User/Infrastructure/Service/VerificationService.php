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
            // SMTP Configuration
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

            // Build verification link
            $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
            $verifyLink = $baseUrl . '/verify-email?token=' . urlencode($token);

            // Try to load the email template – corrected path (4 levels up)
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
                // Fallback inline HTML if template is missing
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
        error_log("Verification SMS to: " . $user->getPhone()->getValue() . " | Code: " . $code);
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