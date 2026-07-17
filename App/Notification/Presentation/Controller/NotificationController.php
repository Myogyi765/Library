<?php
namespace App\Notification\Presentation\Controller;

use App\Notification\Application\Service\NotificationService;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;

class NotificationController extends BaseController
{
    private NotificationService $notificationService;
    private UserAuthenticator $authenticator;
    private Authorization $authorization;

    // ✅ Constructor ကို ပြင်ပါ – Service များကို တိုက်ရိုက်လက်ခံပါ
    public function __construct(
        NotificationService $notificationService,
        UserAuthenticator $authenticator,
        Authorization $authorization
    ) {
        $this->notificationService = $notificationService;
        $this->authenticator = $authenticator;
        $this->authorization = $authorization;
    }

    public function getNotifications(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = $this->authenticator->getCurrentUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $user->getId();
        $role = $user->getRole();

        $unread = $this->notificationService->getUnread($userId, $role);
        $latest = $this->notificationService->getLatest($userId, $role, 10);

        $notifications = array_map(function($n) {
            return [
                'id' => $n->getId(),
                'title' => $n->getTitle(),
                'message' => $n->getMessage(),
                'link' => $n->getLink(),
                'is_read' => $n->isRead(),
                'created_at' => $n->getCreatedAt()->format('Y-m-d H:i:s'),
                'time_ago' => $this->timeAgo($n->getCreatedAt())
            ];
        }, $latest);

        header('Content-Type: application/json');
        echo json_encode([
            'unread_count' => count($unread),
            'notifications' => $notifications
        ]);
    }

    public function markRead(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = $this->authenticator->getCurrentUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if ($id) {
            $this->notificationService->markAsRead((int)$id);
        } else {
            $user = $this->authenticator->getCurrentUser();
            $this->notificationService->markAllAsRead($user->getId(), $user->getRole());
        }

        echo json_encode(['success' => true]);
    }

    private function timeAgo(\DateTime $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
    }
}