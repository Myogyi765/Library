<?php

namespace App\User\Domain\Service;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\User\Exception\DuplicateEmailException;
use App\User\Exception\DuplicatePhoneException;

class UserDomainService
{
    private UserRepositoryInterface $repository;
    
    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    public function ensureUniqueEmail(Email $email): void
    {
        if ($this->repository->emailExists($email)) {
            throw new DuplicateEmailException('Email already registered');
        }
    }
    
    public function ensureUniquePhone(Phone $phone): void
    {
        if ($this->repository->phoneExists($phone)) {
            throw new DuplicatePhoneException('Phone already registered');
        }
    }
    
    public function validateUserCredentials(string $identifier, string $password): bool
    {
        $user = $this->repository->findByIdentifier($identifier);
        
        if (!$user) {
            return false;
        }
        
        return $user->getPassword()->verify($password);
    }
}