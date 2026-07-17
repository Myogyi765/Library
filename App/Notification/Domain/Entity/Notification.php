<?php
namespace App\Notification\Domain\Entity;

class Notification
{
    private ?int $id;
    private ?int $userId;         
    private string $role;
    private string $type;
    private string $title;
    private string $message;
    private ?string $link;
    private bool $isRead;
    private \DateTime $createdAt;

    public function __construct(
        ?int $userId,            
        string $role,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?int $id = null,
        bool $isRead = false,
        ?\DateTime $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->role = $role;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->link = $link;
        $this->isRead = $isRead;
        $this->createdAt = $createdAt ?? new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->userId; }  
    public function getRole(): string { return $this->role; }
    public function getType(): string { return $this->type; }
    public function getTitle(): string { return $this->title; }
    public function getMessage(): string { return $this->message; }
    public function getLink(): ?string { return $this->link; }
    public function isRead(): bool { return $this->isRead; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    public function markAsRead(): void { $this->isRead = true; }
}