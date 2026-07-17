<?php
namespace App\Admin\Application\DTO;

 class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) {}
}