<?php

namespace App\User\Application\Service;

use App\User\Application\DTO\LoginDTO;
use App\User\Application\DTO\RegisterDTO;
use App\User\Application\DTO\UserDTO;
use App\User\Infrastructure\Mapper\UserMapper;
use App\User\Application\Request\RegisterRequest;
use App\User\Application\Response\LoginResponse;
use App\User\Application\Response\UserResponse;
use App\User\Application\UseCase\GetUser;
use App\User\Application\UseCase\LoginUser;
use App\User\Application\UseCase\LogoutUser;
use App\User\Application\UseCase\RegisterUser;
use App\User\Exception\UserException;

class UserService
{
    private RegisterUser $registerUser;
    private LoginUser $loginUser;
    private LogoutUser $logoutUser;
    private GetUser $getUser;
    private UserMapper $userMapper;

    public function __construct(
        RegisterUser $registerUser,
        LoginUser $loginUser,
        LogoutUser $logoutUser,
        GetUser $getUser,
        UserMapper $userMapper
    ) {
        $this->registerUser = $registerUser;
        $this->loginUser = $loginUser;
        $this->logoutUser = $logoutUser;
        $this->getUser = $getUser;
        $this->userMapper = $userMapper;
    }

    public function register(RegisterDTO $dto): UserResponse
    {
        try {
            $request = new RegisterRequest(
                $dto->getName(),
                $dto->getEmail(),
                $dto->getPhone(),
                $dto->getPassword(),
                $dto->getMethod(),
              
            );

            $userDTO = $this->registerUser->execute($request);

            return new UserResponse(true, 'Registration successful! Please login.', $userDTO);
        } catch (UserException $e) {
            return new UserResponse(false, $e->getMessage(), null, ['registration' => $e->getMessage()]);
        } catch (\Exception $e) {
            return new UserResponse(false, 'Registration failed: ' . $e->getMessage(), null, ['registration' => $e->getMessage()]);
        }
    }

    public function login(LoginDTO $dto): LoginResponse
    {
        try {
            $userDTO = $this->loginUser->execute($dto);
            $userId = $userDTO->id ?? null;

            return new LoginResponse(true, 'Login successful!', $userId);
        } catch (UserException $e) {
            return new LoginResponse(false, $e->getMessage(), null, ['login' => $e->getMessage()]);
        } catch (\Exception $e) {
            return new LoginResponse(false, 'Login failed: ' . $e->getMessage(), null, ['login' => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        $this->logoutUser->execute();
    }

    public function getCurrentUser(): ?UserDTO
    {
        $user = $this->getUser->getCurrentUser();
        return $user ? $this->userMapper->toDTO($user) : null;
    }

    public function getUserById(int $userId): ?UserDTO
    {
        $user = $this->getUser->getUserById($userId);
        return $user ? $this->userMapper->toDTO($user) : null;
    }

    public function getAllUsers(int $limit = 100, int $offset = 0): array
    {
        $users = $this->getUser->getAllUsers($limit, $offset);
        return $this->userMapper->toDTOArray($users);
    }

    public function isAuthenticated(): bool
    {
        return $this->getUser->isAuthenticated();
    }
}