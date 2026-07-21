<?php

namespace App\Admin\Application\Service;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\UserStatus;
use DateTime;

class UserManagementService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): User
    {
        $name = $data['name'] ?? '';
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $status = $data['status'] ?? 'active';

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

        if (empty($email) && !empty($phone)) {
            do {
                $uniqueId = time() . '_' . bin2hex(random_bytes(3));
                $generatedEmail = 'u_' . $uniqueId . '@p.local';
                $emailVO = new Email($generatedEmail);
            } while ($this->userRepository->findByEmail($emailVO));
            
            $finalLoginMethod = 'phone';
        } else {
            $finalLoginMethod = 'email';
        }

        $passwordVO = new Password($password);
        $statusVO = UserStatus::fromString($status);

        $roleId = $this->userRepository->getRoleIdByName('user');
        if (!$roleId) {
            throw new \RuntimeException('Default role "user" not found in database.');
        }

        $emailVerified = ($status === 'active' && !empty($email));
        $phoneVerified = ($status === 'active' && !empty($phone));

        $user = new User(
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
            null,
            null
        );

        $this->userRepository->save($user);
        return $user;
    }

    public function updateUser(int $id, array $data): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \RuntimeException('User not found.');
        }

        $name = $data['name'] ?? $user->getName();
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

        $this->userRepository->save($user);
        return $user;
    }
}