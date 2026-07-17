<?php

namespace App\User\Application\UseCase;

use App\User\Application\DTO\LoginDTO;
use App\User\Application\DTO\UserDTO;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Exception\UserNotFoundException;
use App\User\Infrastructure\Security\UserAuthenticator;

class LoginUser
{
    private UserRepositoryInterface $userRepository;
    private UserAuthenticator $authenticator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserAuthenticator $authenticator
    ) {
        $this->userRepository = $userRepository;
        $this->authenticator = $authenticator;
    }

    public function execute(LoginDTO $dto): UserDTO
    {
        $user = $this->userRepository->findByIdentifier($dto->getIdentifier());
        
        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        if (!$user->getPassword()->verify($dto->getPassword())) {
            throw new \RuntimeException('Invalid credentials');
        }

        $this->authenticator->login($user);
        
        return UserDTO::fromEntity($user);
    }
}