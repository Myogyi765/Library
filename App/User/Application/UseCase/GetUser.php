<?php

namespace App\User\Application\UseCase;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Security\UserAuthenticator;

class GetUser
{
    private UserRepositoryInterface $repository;
    private UserAuthenticator $authenticator;

    public function __construct(
        UserRepositoryInterface $repository,
        UserAuthenticator $authenticator
    ) {
        $this->repository = $repository;
        $this->authenticator = $authenticator;
    }

    public function getCurrentUser(): ?User
    {
        return $this->authenticator->getCurrentUser();
    }

    public function getUserById(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    public function getAllUsers(int $limit = 100, int $offset = 0): array
    {
        // Implement or use your repository method if available
        // For now, return empty array (you can implement later)
        return [];
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticator->isAuthenticated();
    }
}