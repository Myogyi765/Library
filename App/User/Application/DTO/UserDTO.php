<?php

namespace App\User\Application\DTO;

use App\User\Domain\Entity\User;

class UserDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public string $phone,
        public string $status,
        public string $createdAt,
        public ?string $updatedAt,
        public ?string $lastLoginAt
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            $user->getId(),
            $user->getName(),
            $user->getEmail()->getValue(),
            $user->getPhone()->getValue(),
            (string) $user->getStatus(),  
            $user->getCreatedAt()->format('Y-m-d H:i:s'),
            $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
            $user->getLastLoginAt()?->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_login_at' => $this->lastLoginAt,
        ];
    }
}