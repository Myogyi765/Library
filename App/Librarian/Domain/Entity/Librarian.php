<?php
namespace App\Librarian\Domain\Entity;

use App\Librarian\Domain\ValueObject\Email;
use App\Librarian\Domain\ValueObject\Password;
use App\Librarian\Domain\ValueObject\Department;
use DateTimeImmutable;

class Librarian
{
    private ?int $id;
    
    private ?int $role_id;
    private string $name;
    private Email $email;
    private Password $password;
    private Department $department;
    private ?DateTimeImmutable $hiredAt;
    private ?DateTimeImmutable $lastLogin;

    public function __construct(
        string $name,
        
        Email $email,
        Password $password,
        Department $department,
        ?int $id = null,
        ?DateTimeImmutable $hiredAt = null,
        ?DateTimeImmutable $lastLogin = null
    ) {
        $this->name = $name;
        $this->email = $email;
       
        $this->password = $password;
        $this->department = $department;
        $this->id = $id;
        $this->hiredAt = $hiredAt ?? new DateTimeImmutable();
        $this->lastLogin = $lastLogin;
    }

    // -------- Getters --------
    public function getId(): ?int { return $this->id; }

    // public function getRoleId(): ?int { return $this->role_id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): Email { return $this->email; }
    public function getPassword(): Password { return $this->password; }
    public function getDepartment(): Department { return $this->department; }
    public function getHiredAt(): ?DateTimeImmutable { return $this->hiredAt; }
    public function getLastLogin(): ?DateTimeImmutable { return $this->lastLogin; }

    // -------- Setters (fluent) --------
    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setEmail(Email $email): self { $this->email = $email; return $this; }
    public function setPassword(Password $password): self { $this->password = $password; return $this; }
    public function setDepartment(Department $department): self { $this->department = $department; return $this; }
    public function setHiredAt(DateTimeImmutable $hiredAt): self { $this->hiredAt = $hiredAt; return $this; }
    public function setLastLogin(DateTimeImmutable $lastLogin): self { $this->lastLogin = $lastLogin; return $this; }
}