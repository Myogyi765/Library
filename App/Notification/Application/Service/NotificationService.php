<?php
namespace App\Notification\Application\Service;

use App\Notification\Domain\Entity\Notification;
use App\Notification\Domain\Repository\NotificationRepositoryInterface;

class NotificationService
{
    private NotificationRepositoryInterface $repository;

    public function __construct(NotificationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createNotification(
        ?int $userId,          
        string $role,
        string $type,
        string $title,
        string $message,
        ?string $link = null
    ): void {
        $notification = new Notification($userId, $role, $type, $title, $message, $link);
        $this->repository->save($notification);
    }

    public function getUnread(int $userId, string $role): array
    {
        return $this->repository->findUnreadByUserIdAndRole($userId, $role);
    }

    public function getLatest(int $userId, string $role, int $limit = 10): array
    {
        return $this->repository->findLatest($userId, $role, $limit);
    }

    public function markAsRead(int $id): void
    {
        $this->repository->markAsRead($id);
    }

    public function markAllAsRead(int $userId, string $role): void
    {
        $this->repository->markAllAsRead($userId, $role);
    }

    public function getUnreadCount(int $userId, string $role): int
    {
        return count($this->getUnread($userId, $role));
    }
}