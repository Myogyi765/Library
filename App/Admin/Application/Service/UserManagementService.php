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
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? null;
        $password = $data['password'] ?? '';
        $status = $data['status'] ?? 'active';

        if (empty($name) || empty($email) || empty($password)) {
            throw new \InvalidArgumentException('Name, email, and password are required.');
        }

        $emailVO = new Email($email);
        
        if ($this->userRepository->findByEmail($emailVO)) {
            throw new \RuntimeException('Email already registered.');
        }

        $phoneVO = $phone ? new Phone($phone) : null;
        if ($phoneVO && $this->userRepository->findByPhone($phoneVO)) {
            throw new \RuntimeException('Phone number already registered.');
        }

        $passwordVO = new Password($password);
        $statusVO = UserStatus::fromString($status);

        $roleId = $this->userRepository->getRoleIdByName('user');
        if (!$roleId) {
            throw new \RuntimeException('Default role "user" not found in database.');
        }

        $user = new User(
            null, $name, $emailVO, $phoneVO, $passwordVO, $statusVO,
            $roleId, 'user', false, false, 'email',
            null, new DateTime(), new DateTime(), null,
            null, null, null, null, null
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
        $email = $data['email'] ?? $user->getEmail()->getValue();
        $phone = $data['phone'] ?? ($user->getPhone() ? $user->getPhone()->getValue() : null);
        $status = $data['status'] ?? $user->getStatus()->getValue();
        $password = $data['password'] ?? null;

        if ($email !== $user->getEmail()->getValue()) {
            $emailVO = new Email($email);
            if ($this->userRepository->findByEmail($emailVO)) {
                throw new \RuntimeException('Email already taken.');
            }
            $user->setEmail($emailVO);
        }

        $currentPhone = $user->getPhone() ? $user->getPhone()->getValue() : null;
        if ($phone !== $currentPhone) {
            $phoneVO = $phone ? new Phone($phone) : null;
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