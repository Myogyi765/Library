<?php include BASE_PATH . '/view/layout/header.php'; ?>

<div class="container mx-auto px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-bell text-blue-500 mr-2"></i> Notifications
        </h1>
        <?php
            $dashboardLink = match($userRole ?? 'user') {
                'admin'     => '/admin/dashboard',
                'librarian' => '/librarian/dashboard',
                default     => '/user-dashboard',
            };
        ?>
        <a href="<?= BASE_URL . $dashboardLink ?>" 
           class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($unreadCount > 0): ?>
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400" id="unread-message">
            You have <span class="font-bold text-blue-600" id="unread-count"><?= $unreadCount ?></span> unread notifications.
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <?php if (empty($notifications)): ?>
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <i class="fas fa-bell-slash text-4xl mb-3"></i>
                <p>No notifications yet</p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700" id="notification-list">
                <?php foreach ($notifications as $notif): ?>
                    <li class="notification-item p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer <?= $notif->isRead() ? 'read' : 'unread' ?>"
                        data-id="<?= $notif->getId() ?>"
                        data-read="<?= $notif->isRead() ? 'true' : 'false' ?>"
                        data-link="<?= $notif->getLink() ? htmlspecialchars($notif->getLink()) : '' ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 dark:text-white">
                                    <?= htmlspecialchars($notif->getTitle()) ?>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <?= htmlspecialchars($notif->getMessage()) ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?= $notif->getCreatedAt()->format('Y-m-d H:i') ?>
                                </p>
                            </div>
                            <?php if (!$notif->isRead()): ?>
                                <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1 unread-dot"></span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = window.BASE_URL || '<?= BASE_URL ?>';
    console.log('🔔 Notifications page using BASE_URL:', BASE_URL);

    const list = document.getElementById('notification-list');
    if (!list) {
        console.warn('⚠️ Notification list not found');
        return;
    }

    list.addEventListener('click', function(e) {
        const item = e.target.closest('.notification-item');
        if (!item) {
            return;
        }

        const id = item.dataset.id;
        if (!id) {
            return;
        }

        // ✅ If there's a link, navigate to it (for unread or read)
        const link = item.dataset.link;
        if (link && link.trim() !== '') {
            console.log('🔗 Navigating to:', link);
            window.location.href = link;
            return;
        }

        // If already read, do nothing (no link)
        if (item.dataset.read === 'true') {
            console.log('ℹ️ Notification already read and no link:', id);
            return;
        }

        console.log('📌 Marking notification as read:', id);

        // Optimistic UI Update
        const dot = item.querySelector('.unread-dot');
        if (dot) {
            dot.remove();
            item.style.transition = 'background 0.2s ease';
            item.style.background = 'rgba(59, 130, 246, 0.1)';
            setTimeout(() => {
                item.style.background = '';
            }, 300);
        }

        // Mark as read on server
        fetch(BASE_URL + '/api/notifications/read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: parseInt(id) })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.error || 'Server error (HTTP ' + response.status + ')');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                item.dataset.read = 'true';
                item.classList.remove('unread');
                item.classList.add('read');

                const countSpan = document.getElementById('unread-count');
                if (countSpan) {
                    let current = parseInt(countSpan.textContent);
                    if (current > 0) {
                        countSpan.textContent = current - 1;
                        if (current - 1 === 0) {
                            const msg = document.getElementById('unread-message');
                            if (msg) msg.style.display = 'none';
                        }
                    }
                }

                const badge = document.getElementById('notification-badge');
                if (badge) {
                    let badgeCount = parseInt(badge.textContent) || 0;
                    if (badgeCount > 0) {
                        badgeCount = badgeCount - 1;
                        if (badgeCount === 0) {
                            badge.classList.add('hidden');
                        } else {
                            badge.textContent = badgeCount > 99 ? '99+' : badgeCount;
                        }
                    }
                }

                console.log('✅ Notification #' + id + ' marked as read');
            } else {
                // Rollback
                const dotContainer = item.querySelector('.flex.items-start.justify-between.gap-3');
                if (dotContainer && !item.querySelector('.unread-dot')) {
                    const newDot = document.createElement('span');
                    newDot.className = 'w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1 unread-dot';
                    dotContainer.appendChild(newDot);
                }
                showToast('Failed to mark as read', 'error');
            }
        })
        .catch(err => {
            console.error('❌ Error:', err.message);
            const dotContainer = item.querySelector('.flex.items-start.justify-between.gap-3');
            if (dotContainer && !item.querySelector('.unread-dot')) {
                const newDot = document.createElement('span');
                newDot.className = 'w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1 unread-dot';
                dotContainer.appendChild(newDot);
            }
            showToast('Failed to mark as read. Please try again.', 'error');
        });
    });

    function showToast(message, type = 'info') {
        const colors = {
            info: 'bg-blue-500',
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500'
        };
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${colors[type] || 'bg-gray-700'} text-white px-6 py-3 rounded-lg shadow-lg text-sm z-50 transition-opacity duration-300`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    console.log('✅ Notification page ready');
});
</script>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>