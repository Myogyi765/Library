<?php
namespace App\Admin\Domain\Entity;

use App\Admin\Domain\ValueObject\Email;
use App\Admin\Domain\ValueObject\Password;
use App\Admin\Domain\ValueObject\AdminRole;

class Admin
{
    private ?int $id = null;
    private string $name;
    private Email $email;
    private Password $password;
    private AdminRole $role;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $lastLogin;

    public function __construct(
        string $name,
        Email $email,
        Password $password,
        AdminRole $role
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): Email { return $this->email; }
    public function getPassword(): Password { return $this->password; }
    public function getRole(): AdminRole { return $this->role; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getLastLogin(): ?\DateTimeImmutable { return $this->lastLogin; }

    public function setId(int $id): void { $this->id = $id; }
    public function setLastLogin(\DateTimeImmutable $lastLogin): void { $this->lastLogin = $lastLogin; }

    public function changePassword(Password $newPassword): void
    {
        $this->password = $newPassword;
    }
}