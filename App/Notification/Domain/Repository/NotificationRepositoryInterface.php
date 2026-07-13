<?php
namespace App\Notification\Domain\Repository;

use App\Notification\Domain\Entity\Notification;

interface NotificationRepositoryInterface
{
    public function save(Notification $notification): void;
    public function findUnreadByUserIdAndRole(int $userId, string $role): array;
    public function findUnreadByRole(string $role): array;
    public function markAsRead(int $id): void;
    public function markAllAsRead(int $userId, string $role): void;
    public function findLatest(int $userId, string $role, int $limit = 10): array;
}