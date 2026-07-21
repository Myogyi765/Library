<?php

namespace App\User\Infrastructure\Mapper;

use App\User\Application\DTO\UserDTO;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\UserStatus;
use DateTime;

class UserMapper
{
    public static function toEntity(array $data): User
    {
        $status = isset($data['status']) 
            ? UserStatus::fromString($data['status']) 
            : UserStatus::pending();

        $phone = isset($data['phone']) ? new Phone($data['phone']) : null;

        $roleId = $data['role_id'] ?? null;
        $roleName = $data['role'] ?? 'user';

        $emailVerified = (bool)($data['email_verified'] ?? false);
        $phoneVerified = (bool)($data['phone_verified'] ?? false);

        if (isset($data['password_hash']) && !empty($data['password_hash'])) {
            $password = new Password($data['password_hash'], true);
        } else {
            $password = new Password($data['password'] ?? '', false);
        }

        $user = new User(
            $data['id'] ?? null,
            $data['name'],
            new Email($data['email']),
            $password,
            $status,
            $phone,
            $roleId,
            $roleName,
            $emailVerified,
            $phoneVerified,
            $data['login_method'] ?? 'email',
            $data['remember_token'] ?? null,
            isset($data['created_at']) ? new DateTime($data['created_at']) : null,
            isset($data['updated_at']) ? new DateTime($data['updated_at']) : null,
            isset($data['last_login_at']) ? new DateTime($data['last_login_at']) : null,
            $data['verification_token'] ?? null,
            $data['verification_code'] ?? null,
            $data['verification_expires_at'] ?? null,
            isset($data['email_verified_at']) ? new DateTime($data['email_verified_at']) : null,
            isset($data['phone_verified_at']) ? new DateTime($data['phone_verified_at']) : null,
            $data['profile_image'] ?? null
        );

        return $user;
    }

    public static function toArray(User $user): array
    {
        $data = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail()->getValue(),
            'phone' => $user->getPhone()?->getValue(),
            'password_hash' => $user->getPassword()->getHash(),
            'status' => $user->getStatus()->getValue(),
            'email_verified' => $user->isEmailVerified() ? 1 : 0,
            'phone_verified' => $user->isPhoneVerified() ? 1 : 0,
            'role' => $user->getRole(),
            'role_id' => $user->getRoleId(),
            'login_method' => $user->getLoginMethod(),
            'remember_token' => $user->getRememberToken(),
            'verification_token' => $user->getVerificationToken(),
            'verification_code' => $user->getVerificationCode(),
            'verification_expires_at' => $user->getVerificationExpiresAt(),
            'profile_image' => $user->getProfileImage(),
        ];

        $dateFields = [
            'created_at' => 'getCreatedAt',
            'updated_at' => 'getUpdatedAt',
            'last_login_at' => 'getLastLoginAt',
            'email_verified_at' => 'getEmailVerifiedAt',
            'phone_verified_at' => 'getPhoneVerifiedAt'
        ];
        foreach ($dateFields as $key => $method) {
            if (method_exists($user, $method)) {
                $date = $user->$method();
                if ($date) {
                    $data[$key] = $date instanceof DateTime ? $date->format('Y-m-d H:i:s') : (string)$date;
                } else {
                    $data[$key] = null;
                }
            }
        }

        return $data;
    }

    public function toDTO(User $user): UserDTO
    {
        return new UserDTO(
            $user->getId(),
            $user->getName(),
            $user->getEmail()->getValue(),
            $user->getPhone()?->getValue(),
            $user->getRole() ?? 'user',
            $user->getStatus()->getValue(),
            $user->isEmailVerified(),
            $user->isPhoneVerified()
        );
    }

    public function toDTOArray(array $users): array
    {
        return array_map([$this, 'toDTO'], $users);
    }
}