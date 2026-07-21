<?php

namespace App\User\Domain\Entity;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\UserStatus;
use DateTime;

class User
{
    private ?int $id;
    private string $name;
    private Email $email;
    private ?Phone $phone;          
    private Password $password;
    private ?int $roleId;
    private string $roleName;
    private UserStatus $status;
    private bool $emailVerified;
    private bool $phoneVerified;
    private string $loginMethod;
    private ?string $rememberToken;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;
    private ?DateTime $lastLoginAt;
    private ?string $verificationToken;
    private ?string $verificationCode;
    private ?string $verificationExpiresAt;
    private ?DateTime $emailVerifiedAt;
    private ?DateTime $phoneVerifiedAt;
    private ?string $profileImage = null;

    // ✅ FIXED: Required parameters first, optional after
    public function __construct(
        ?int $id,
        string $name,
        Email $email,
        Password $password,
        UserStatus $status,
        ?Phone $phone = null,
        ?int $roleId = null,
        string $roleName = 'user',
        bool $emailVerified = false,
        bool $phoneVerified = false,
        string $loginMethod = 'email',
        ?string $rememberToken = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null,
        ?DateTime $lastLoginAt = null,
        ?string $verificationToken = null,
        ?string $verificationCode = null,
        ?string $verificationExpiresAt = null,
        ?DateTime $emailVerifiedAt = null,
        ?DateTime $phoneVerifiedAt = null,
        ?string $profileImage = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
        $this->status = $status;
        $this->roleId = $roleId;
        $this->roleName = $roleName;
        $this->emailVerified = $emailVerified;
        $this->phoneVerified = $phoneVerified;
        $this->loginMethod = $loginMethod;
        $this->rememberToken = $rememberToken;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
        $this->lastLoginAt = $lastLoginAt;
        $this->verificationToken = $verificationToken;
        $this->verificationCode = $verificationCode;
        $this->verificationExpiresAt = $verificationExpiresAt;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->phoneVerifiedAt = $phoneVerifiedAt;
        $this->profileImage = $profileImage;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): Email { return $this->email; }
    public function getPhone(): ?Phone { return $this->phone; }
    public function getPassword(): Password { return $this->password; }
    public function getRole(): string { return $this->roleName ?? 'user'; }
    public function getRoleId(): ?int { return $this->roleId; }
    public function getStatus(): UserStatus { return $this->status; }
    public function isEmailVerified(): bool { return $this->emailVerified; }
    public function isPhoneVerified(): bool { return $this->phoneVerified; }
    public function getLoginMethod(): string { return $this->loginMethod; }
    public function getIdentifier(): string
    {
        return $this->loginMethod === 'phone' ? ($this->phone ? $this->phone->getValue() : '') : $this->email->getValue();
    }
    public function getRememberToken(): ?string { return $this->rememberToken; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; }
    public function getLastLoginAt(): ?DateTime { return $this->lastLoginAt; }
    public function getVerificationToken(): ?string { return $this->verificationToken; }
    public function getVerificationCode(): ?string { return $this->verificationCode; }
    public function getVerificationExpiresAt(): ?string { return $this->verificationExpiresAt; }
    public function getEmailVerifiedAt(): ?DateTime { return $this->emailVerifiedAt; }
    public function getPhoneVerifiedAt(): ?DateTime { return $this->phoneVerifiedAt; }
    
    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setEmail(Email $email): self { $this->email = $email; return $this; }
    public function setPhone(?Phone $phone): self { $this->phone = $phone; return $this; }
    public function setPassword(Password $password): self { $this->password = $password; return $this; }
    public function setRoleId(?int $roleId): self { $this->roleId = $roleId; return $this; }
    public function setRoleName(string $roleName): self { $this->roleName = $roleName; return $this; }
    public function setStatus(UserStatus $status): self { $this->status = $status; return $this; }
    public function setEmailVerified(bool $verified): self { $this->emailVerified = $verified; return $this; }
    public function setPhoneVerified(bool $verified): self { $this->phoneVerified = $verified; return $this; }
    public function setLoginMethod(string $method): self { $this->loginMethod = $method; return $this; }
    public function setRememberToken(?string $token): self { $this->rememberToken = $token; return $this; }
    public function setUpdatedAt(?DateTime $updatedAt): self { $this->updatedAt = $updatedAt ?? new DateTime(); return $this; }
    public function setLastLoginAt(?DateTime $lastLoginAt): self { $this->lastLoginAt = $lastLoginAt; return $this; }
    public function setVerificationToken(?string $token): self { $this->verificationToken = $token; return $this; }
    public function setVerificationCode(?string $code): self { $this->verificationCode = $code; return $this; }
    public function setVerificationExpiresAt(?string $expiresAt): self { $this->verificationExpiresAt = $expiresAt; return $this; }
    public function setEmailVerifiedAt(?DateTime $verifiedAt): self { $this->emailVerifiedAt = $verifiedAt; return $this; }
    public function setPhoneVerifiedAt(?DateTime $verifiedAt): self { $this->phoneVerifiedAt = $verifiedAt; return $this; }
    
    public function setProfileImage(?string $profileImage): self
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    public function isVerified(): bool { return $this->emailVerified || $this->phoneVerified; }
    public function isActive(): bool { return $this->status->getValue() === 'active'; }
    public function canLogin(): bool { return $this->isActive(); }
    public function isPending(): bool { return $this->status->getValue() === 'pending'; }
    public function verifyEmail(): self { $this->emailVerified = true; $this->emailVerifiedAt = new DateTime(); return $this; }
    public function verifyPhone(): self { $this->phoneVerified = true; $this->phoneVerifiedAt = new DateTime(); return $this; }
    public function isVerificationValid(): bool {
        if (!$this->verificationExpiresAt) return false;
        $expires = new DateTime($this->verificationExpiresAt);
        return (new DateTime()) < $expires;
    }
}