<?php

namespace App\User\Application\Mapper;

use App\User\Application\DTO\UserDTO;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\UserStatus;
use DateTime;

class UserMapper
{
    /**
     * Convert an array (from DB) to a User entity.
     * The constructor accepts exactly 8 parameters.
     */
    public static function toEntity(array $data): User
    {
        $status = isset($data['status']) 
            ? UserStatus::fromString($data['status']) 
            : UserStatus::pending();

        // Only 8 arguments – no timestamps here
        $user = new User(
            $data['id'] ?? null,
            $data['name'],
            new Email($data['email']),
            new Phone($data['phone'] ?? '+95000000000'),
            new Password($data['password_hash'] ?? $data['password'] ?? '', true),
            $status,
            (bool)($data['email_verified'] ?? false),
            (bool)($data['phone_verified'] ?? false)
        );

        // If the User entity has setters for timestamps, set them after creation.
        if (isset($data['created_at']) && method_exists($user, 'setCreatedAt')) {
            $user->setCreatedAt(new DateTime($data['created_at']));
        }
        if (isset($data['updated_at']) && method_exists($user, 'setUpdatedAt')) {
            $user->setUpdatedAt(new DateTime($data['updated_at']));
        }
        // last_login_at, verification fields can be set similarly if needed.

        return $user;
    }

    /**
     * Convert a User entity to an array (for DB storage).
     * Only use getters that are guaranteed to exist.
     */
    public static function toArray(User $user): array
    {
        $data = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail()->getValue(),
            'phone' => $user->getPhone()->getValue(),
            'password_hash' => $user->getPassword()->getHash(),
            'status' => $user->getStatus()->getValue(),
            'email_verified' => $user->isEmailVerified() ? 1 : 0,
            'phone_verified' => $user->isPhoneVerified() ? 1 : 0,
        ];

        // Add optional fields only if the getters exist
        if (method_exists($user, 'getCreatedAt')) {
            $data['created_at'] = $user->getCreatedAt()?->format('Y-m-d H:i:s');
        }
        if (method_exists($user, 'getUpdatedAt')) {
            $data['updated_at'] = $user->getUpdatedAt()?->format('Y-m-d H:i:s');
        }
        if (method_exists($user, 'getLastLoginAt')) {
            $data['last_login_at'] = $user->getLastLoginAt()?->format('Y-m-d H:i:s');
        }
        if (method_exists($user, 'getVerificationToken')) {
            $data['verification_token'] = $user->getVerificationToken();
        }
        // etc.

        return $data;
    }

    /**
     * Convert a User entity to a UserDTO (for API/View).
     * Only use getters that exist – for missing ones, provide defaults.
     */
    public function toDTO(User $user): UserDTO
{
    return new UserDTO(
        $user->getId(),
        $user->getName(),
        $user->getEmail()->getValue(),
        $user->getPhone()->getValue(),
        $user->getRole() ?? 'user',
        $user->getStatus()->getValue(),
        $user->isEmailVerified(),
        $user->isPhoneVerified()
    );
}
    /**
     * Convert an array of User entities to an array of UserDTOs.
     */
    public function toDTOArray(array $users): array
    {
        return array_map([$this, 'toDTO'], $users);
    }
}