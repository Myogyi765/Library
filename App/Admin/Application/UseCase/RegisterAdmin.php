<?php
namespace App\Admin\Application\UseCase;

use App\Admin\Domain\Entity\Admin;
use App\Admin\Domain\Repository\AdminRepositoryInterface;
use App\Admin\Domain\ValueObject\Email;
use App\Admin\Domain\ValueObject\Password;
use App\Admin\Domain\ValueObject\AdminRole;
use App\Admin\Application\DTO\RegisterDTO;

class RegisterAdmin
{
    public function __construct(
        private AdminRepositoryInterface $adminRepo
    ) {}

    public function execute(RegisterDTO $dto): Admin
    {
        $admin = new Admin(
            $dto->name,
            new Email($dto->email),
            new Password($dto->password), 
            new AdminRole($dto->role)
        );
        
        $this->adminRepo->save($admin);
        
        return $admin;
    }
}