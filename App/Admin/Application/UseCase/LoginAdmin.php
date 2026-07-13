<?php
namespace App\Admin\Application\UseCase;

use App\Admin\Domain\Repository\AdminRepositoryInterface;
use App\Admin\Domain\ValueObject\Email;
use App\Admin\Application\DTO\LoginDTO;
use App\Admin\Domain\Entity\Admin;

class LoginAdmin
{

    public function __construct(
        private AdminRepositoryInterface $adminRepo
    ) {}

    public function execute(LoginDTO $dto): ?Admin
    {
      
        $admin = $this->adminRepo->findByEmail(new Email($dto->email));

   
        if ($admin !== null && $admin->getPassword()->verify($dto->password)) {
            return $admin;
        }

        return null;
    }
}