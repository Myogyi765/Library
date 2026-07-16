<?php

namespace App\User\Domain\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;

interface UserRepositoryInterface
{
    public function save(User $user): User;
    public function findById(int $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function findByPhone(Phone $phone): ?User;
    public function findByIdentifier(string $identifier): ?User;
    public function findByRememberToken(string $token): ?User;
    public function findAll(): array; 
    public function findByEmailVerificationToken(string $token): ?User;
    public function findByPhoneVerificationCode(string $code): ?User;
    public function emailExists(Email $email): bool;
    public function phoneExists(Phone $phone): bool;
    public function delete(int $id): bool;
    public function updateRememberToken(int $userId, ?string $token): void;
    public function updateLastLogin(int $userId): void;

    public function getAllRoles(): array;

    public function getRoleIdByName(string $roleName): ?int;

    public function findByRole(string $roleName): array;

      public function count(): int;
    public function countByRole(string $role): int;


}