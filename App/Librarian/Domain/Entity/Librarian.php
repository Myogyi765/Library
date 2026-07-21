<?php
namespace App\Librarian\Domain\Entity;

use App\Librarian\Domain\ValueObject\Email;
use App\Librarian\Domain\ValueObject\Password;
use App\Librarian\Domain\ValueObject\Department;
use DateTimeImmutable;

class Librarian
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    private ?int $id;
    private string $name;
    private Email $email;
    private Password $password;
    private Department $department;
    private string $status;
    private ?DateTimeImmutable $hiredAt;
    private ?DateTimeImmutable $lastLogin;

    public function __construct(
        string $name,
        Email $email,
        Password $password,
        Department $department,
        ?int $id = null,
        string $status = self::STATUS_ACTIVE,
        ?DateTimeImmutable $hiredAt = null,
        ?DateTimeImmutable $lastLogin = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->department = $department;
        $this->id = $id;
        $this->status = $status;
        $this->hiredAt = $hiredAt ?? new DateTimeImmutable();
        $this->lastLogin = $lastLogin;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): Email { return $this->email; }
    public function getPassword(): Password { return $this->password; }
    public function getDepartment(): Department { return $this->department; }
    public function getStatus(): string { return $this->status; }
    public function getHiredAt(): ?DateTimeImmutable { return $this->hiredAt; }
    public function getLastLogin(): ?DateTimeImmutable { return $this->lastLogin; }

    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setEmail(Email $email): self { $this->email = $email; return $this; }
    public function setPassword(Password $password): self { $this->password = $password; return $this; }
    public function setDepartment(Department $department): self { $this->department = $department; return $this; }
    public function setStatus(string $status): self {
        if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE])) {
            throw new \InvalidArgumentException('Invalid status. Must be "active" or "inactive".');
        }
        $this->status = $status;
        return $this;
    }
    public function setHiredAt(DateTimeImmutable $hiredAt): self { $this->hiredAt = $hiredAt; return $this; }
    public function setLastLogin(DateTimeImmutable $lastLogin): self { $this->lastLogin = $lastLogin; return $this; }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function activate(): self
    {
        $this->status = self::STATUS_ACTIVE;
        return $this;
    }

    public function deactivate(): self
    {
        $this->status = self::STATUS_INACTIVE;
        return $this;
    }
}