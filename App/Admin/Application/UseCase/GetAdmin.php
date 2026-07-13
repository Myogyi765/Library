<?php
namespace App\Admin\Application\UseCase;

use App\Admin\Domain\Repository\AdminRepositoryInterface;
use App\Admin\Domain\Entity\Admin;

class GetAdmin
{
    
    public function __construct(
        private AdminRepositoryInterface $adminRepo
    ) {}

    public function execute(int $id): ?Admin
    {
        return $this->adminRepo->findById($id);
    }
}