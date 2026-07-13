<?php
namespace App\Notification\Infrastructure\Persistence;

use App\Notification\Domain\Entity\Notification;
use App\Notification\Domain\Repository\NotificationRepositoryInterface;
use PDO;

class NotificationRepository implements NotificationRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(Notification $notification): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, role, type, title, message, link, is_read, created_at) 
             VALUES (:user_id, :role, :type, :title, :message, :link, :is_read, :created_at)"
        );
        $stmt->execute([
            ':user_id' => $notification->getUserId(),
            ':role' => $notification->getRole(),
            ':type' => $notification->getType(),
            ':title' => $notification->getTitle(),
            ':message' => $notification->getMessage(),
            ':link' => $notification->getLink(),
            ':is_read' => $notification->isRead() ? 1 : 0,
            ':created_at' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
        $id = (int)$this->db->lastInsertId();
        $reflection = new \ReflectionClass($notification);
        $prop = $reflection->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($notification, $id);
    }

    public function findUnreadByUserIdAndRole(int $userId, string $role): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications WHERE user_id = :user_id AND role = :role AND is_read = 0 ORDER BY created_at DESC"
        );
        $stmt->execute([':user_id' => $userId, ':role' => $role]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrate'], $rows);
    }

    public function findUnreadByRole(string $role): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications WHERE role = :role AND is_read = 0 ORDER BY created_at DESC"
        );
        $stmt->execute([':role' => $role]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrate'], $rows);
    }

    public function markAsRead(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function markAllAsRead(int $userId, string $role): void
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND role = :role AND is_read = 0"
        );
        $stmt->execute([':user_id' => $userId, ':role' => $role]);
    }

    public function findLatest(int $userId, string $role, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications WHERE user_id = :user_id AND role = :role 
             ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrate'], $rows);
    }

    private function hydrate(array $row): Notification
    {
        return new Notification(
            (int)$row['user_id'],
            $row['role'],
            $row['type'],
            $row['title'],
            $row['message'],
            $row['link'] ?? null,
            (int)$row['id'],
            (bool)$row['is_read'],
            new \DateTime($row['created_at'])
        );
    }
}