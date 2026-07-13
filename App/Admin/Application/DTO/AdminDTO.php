<?php
namespace App\Admin\Application\DTO;

 class AdminDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public string $createdAt,
        public ?string $lastLogin = null
    ) {}
}