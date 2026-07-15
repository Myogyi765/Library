<?php

namespace App\User\Application\UseCase;

use App\User\Application\DTO\UserDTO;
use App\User\Application\Request\RegisterRequest;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\UserDomainService;
use App\User\Domain\Service\VerificationServiceInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\UserStatus;
use DateTime;

class RegisterUser
{
    private UserRepositoryInterface $repository;
    private UserDomainService $domainService;
    private VerificationServiceInterface $verificationService;

    public function __construct(
        UserRepositoryInterface $repository,
        UserDomainService $domainService,
        VerificationServiceInterface $verificationService
    ) {
        $this->repository = $repository;
        $this->domainService = $domainService;
        $this->verificationService = $verificationService;
    }

    public function execute(RegisterRequest $request): UserDTO
    {
        $name = trim($request->getName());
        $email = trim($request->getEmail());
        $phone = trim($request->getPhone());
        $password = $request->getPassword();
        $method = $request->getMethod();

        $emailObj = null;
        $phoneObj = null;

        // ===========================
        // Validation
        // ===========================
        if ($method === 'email') {

            if (empty($email)) {
                throw new \InvalidArgumentException('Email is required.');
            }

            $emailObj = new Email($email);
            $this->domainService->ensureUniqueEmail($emailObj);

        } elseif ($method === 'phone') {

            if (empty($phone)) {
                throw new \InvalidArgumentException('Phone number is required.');
            }

            if (!preg_match('/^\+95[0-9]{7,10}$/', $phone)) {
                throw new \InvalidArgumentException(
                    'Phone number must be in format +95XXXXXXXXX'
                );
            }

            $phoneObj = new Phone($phone);
            $this->domainService->ensureUniquePhone($phoneObj);

        } else {
            throw new \InvalidArgumentException('Invalid registration method.');
        }

        $passwordObj = new Password($password);

        // ===========================
        // Get default role ID for 'user'
        // ===========================
        $roleId = $this->repository->getRoleIdByName('user');
        if (!$roleId) {
            throw new \RuntimeException('Default role "user" not found in database.');
        }

        // ===========================
        // Temporary User (for generating token)
        // ===========================
        $tempUser = new User(
            null,
            $name,
            $emailObj ?? new Email('placeholder@example.com'),
            $phoneObj ?? new Phone('+95000000000'),
            $passwordObj,
            UserStatus::pending(),
            $roleId,                // ✅ roleId (int)
            'user',                 // ✅ roleName (string)
            false,
            false,
            $method,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );

        // ===========================
        // Generate Verification
        // ===========================
        $token = $this->verificationService
            ->generateVerificationToken($tempUser);

        $code = $this->verificationService
            ->generateVerificationCode();

        $expiresAt = (new DateTime('+15 minutes'))
            ->format('Y-m-d H:i:s');

        // ===========================
        // Create Final User
        // ===========================
        $user = new User(
            null,
            $name,
            $emailObj ?? new Email('placeholder@example.com'),
            $phoneObj ,
            $passwordObj,
            UserStatus::pending(),
            $roleId,                // ✅ roleId (int)
            'user',                 // ✅ roleName (string)
            false,
            false,
            $method,
            null,
            null,
            null,
            null,
            $token,
            $code,
            $expiresAt,
            null,
            null
        );

        // ===========================
        // Save User
        // ===========================
        $savedUser = $this->repository->save($user);

        // ===========================
        // Send Verification
        // ===========================
        if ($method === 'email') {

            $this->verificationService->sendVerificationEmail(
                $savedUser,
                $token,
                $code
            );

        } else {

            $this->verificationService->sendVerificationSMS(
                $savedUser,
                $code
            );

        }

        // ===========================
        // Return DTO
        // ===========================
        return UserDTO::fromEntity($savedUser);
    }
}