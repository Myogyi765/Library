<?php
namespace App\Admin\Domain\Repository;

use App\Admin\Domain\Entity\Admin;
use App\Admin\Domain\ValueObject\Email;

interface AdminRepositoryInterface
{
    public function save(Admin $admin): void;
    public function findById(int $id): ?Admin;
    public function findByEmail(Email $email): ?Admin;
   
}