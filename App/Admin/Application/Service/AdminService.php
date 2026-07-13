<?php
namespace App\Admin\Application\Service;

use App\Admin\Domain\Repository\AdminRepositoryInterface;
use App\Admin\Application\UseCase\LoginAdmin;
use App\Admin\Application\UseCase\RegisterAdmin;
use App\Admin\Application\UseCase\GetAdmin;
use App\Admin\Application\DTO\LoginDTO;
use App\Admin\Application\DTO\RegisterDTO;

use App\Admin\Domain\Entity\Admin; 

class AdminService
{
    public function __construct(
        private LoginAdmin $loginUseCase,
        private RegisterAdmin $registerUseCase,
        private GetAdmin $getUseCase,
        private AdminRepositoryInterface $adminRepo
    ) {}

    
    public function login(string $email, string $password): ?Admin
    {
        return $this->loginUseCase->execute(new LoginDTO($email, $password));
    }

    public function register(array $data): Admin
    {
        return $this->registerUseCase->execute(new RegisterDTO(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role']
        ));
    }

    public function getAdmin(int $id): ?Admin
    {
        return $this->getUseCase->execute($id);
    }

    public function count(): int
    {
        return count($this->adminRepo->findAll());
    }
}