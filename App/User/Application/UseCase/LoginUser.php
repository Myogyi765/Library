<?php

namespace App\User\Application\UseCase;

use App\User\Application\DTO\LoginDTO;
use App\User\Application\DTO\UserDTO;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\UserDomainService;
use App\User\Exception\UserNotFoundException;
use App\User\Infrastructure\Security\UserAuthenticator;
use RuntimeException;

class LoginUser
{
    private UserRepositoryInterface $userRepository;
    private UserDomainService $domainService;
    private UserAuthenticator $authenticator;
    
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserDomainService $domainService,
        UserAuthenticator $authenticator
    ) {
        $this->userRepository = $userRepository;
        $this->domainService = $domainService;
        $this->authenticator = $authenticator;
    }
    
    public function execute(LoginDTO $dto): UserDTO
    {
        $user = $this->userRepository->findByIdentifier($dto->identifier);
        
        if (!$user) {
            throw new UserNotFoundException('User not found');
        }
        
        // ✅ Allow both active and pending accounts
        $status = $user->getStatus()->getValue();
        if (!in_array($status, ['active', 'pending'])) {
            throw new RuntimeException('Account is not active or pending');
        }
        
        if (!$user->getPassword()->verify($dto->password)) {
            throw new RuntimeException('Invalid credentials');
        }
        
        $this->userRepository->updateLastLogin($user->getId());
        
        if ($dto->remember) {
            $token = $this->authenticator->generateRememberToken($user);
            $this->userRepository->updateRememberToken($user->getId(), $token);
        }
        
        $this->authenticator->login($user);
        
        return UserDTO::fromEntity($user);
    }
}