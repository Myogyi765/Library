// ================================================================
// ===================== THEME TOGGLE ==============================
// ================================================================
(function() {
    'use strict';
    
    const toggle = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-icon');

    function setTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            if (icon) icon.className = 'fas fa-sun';
        } else {
            document.documentElement.classList.remove('dark');
            if (icon) icon.className = 'fas fa-moon';
        }
        localStorage.setItem('theme', theme);
        console.log('🌓 Theme changed to:', theme);
    }

    if (toggle) {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            setTheme('dark');
        } else {
            setTheme('light');
        }

        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isDark = document.documentElement.classList.contains('dark');
            setTheme(isDark ? 'light' : 'dark');
        });
        console.log('✅ Theme toggle initialized');
    } else {
        console.warn('⚠️ Theme toggle button not found');
    }
})();

// ================================================================
// ===================== MOBILE MENU ===============================
// ================================================================
(function() {
    'use strict';
    
    const mobileBtn = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            mobileMenu.classList.toggle('hidden');
            console.log('📱 Mobile menu toggled');
        });

        const links = mobileMenu.querySelectorAll('a, button');
        links.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                mobileMenu.classList.add('hidden');
            }
        });
        
        console.log('✅ Mobile menu initialized');
    } else {
        console.warn('⚠️ Mobile menu elements not found');
    }
})();

// ================================================================
// ===================== MODAL CONTROLS ============================
// ================================================================
(function() {
    'use strict';
    
    const loginModal = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');

    function openModal(modal) {
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            console.log('🔓 Modal opened:', modal.id);
        }
    }

    function closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            console.log('🔒 Modal closed:', modal.id);
        }
    }

})();

// ================================================================
// ===================== PASSWORD TOGGLE ===========================
// ================================================================
(function() {
    'use strict';
    
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.closest('.input-wrapper').querySelector('input');
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
            }
        });
    });
})();

console.log('✅ header.js loaded successfully');
console.log('🌓 Current theme:', document.documentElement.classList.contains('dark') ? 'dark' : 'light');

// ================================================================
// ===================== NOTIFICATION SYSTEM =======================
// ================================================================
(function() {
    'use strict';

    let BASE_URL = window.BASE_URL;
    
    if (!BASE_URL) {
        console.warn('⚠️ window.BASE_URL is not set, using fallback');
        const meta = document.querySelector('meta[name="base-url"]');
        if (meta) {
            BASE_URL = meta.getAttribute('content');
            console.log('📌 Using meta tag base URL:', BASE_URL);
        } else {
            BASE_URL = window.location.origin + '/Library/public';
            console.log('📌 Using constructed base URL:', BASE_URL);
        }
    } else {
        console.log('✅ Using window.BASE_URL:', BASE_URL);
    }

    BASE_URL = BASE_URL.replace(/\/+$/, '');
    console.log('🔔 [Notification] FINAL BASE_URL =', BASE_URL);

    function initNotifications() {
        console.log('🔔 Initializing notification system...');

        const bell = document.getElementById('notification-bell');
        const badge = document.getElementById('notification-badge');
        const dropdown = document.getElementById('notification-dropdown');
        const list = document.getElementById('notification-list');
        const markAllBtn = document.getElementById('mark-all-read');
        const mobileBell = document.getElementById('mobile-notification-bell');
        const mobileBadge = document.getElementById('mobile-notification-badge');

        if (!bell) {
            console.warn('⚠️ Notification bell not found in DOM (user may not be logged in)');
            return;
        }

        console.log('✅ Notification bell found');

        let isDropdownOpen = false;
        let pollingInterval = null;

        function fetchJSON(url, options = {}) {
            const defaultOptions = {
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            };
            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...(options.headers || {})
                }
            };
            return fetch(url, mergedOptions)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status} - ${response.statusText}\n${text.slice(0, 200)}`);
                        });
                    }
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            throw new Error(`Expected JSON but got ${contentType || 'unknown content-type'}\n${text.slice(0, 200)}`);
                        });
                    }
                });
        }

        function fetchNotifications() {
            const url = BASE_URL + '/api/notifications';
            console.log('🔄 Fetching notifications from:', url);
            fetchJSON(url)
                .then(data => {
                    console.log('✅ Notifications data received:', data);
                    updateBadge(data.unread_count);
                    if (isDropdownOpen) {
                        renderNotifications(data.notifications);
                    }
                })
                .catch(err => {
                    console.error('❌ Error fetching notifications:', err.message);
                });
        }

        function updateBadge(count) {
            if (count > 0) {
                const text = count > 99 ? '99+' : count;
                if (badge) {
                    badge.textContent = text;
                    badge.classList.remove('hidden');
                }
                if (mobileBadge) {
                    mobileBadge.textContent = text;
                    mobileBadge.classList.remove('hidden');
                }
            } else {
                if (badge) {
                    badge.textContent = '0';
                    badge.classList.add('hidden');
                }
                if (mobileBadge) {
                    mobileBadge.textContent = '0';
                    mobileBadge.classList.add('hidden');
                }
            }
        }

        // ✅ FIXED: linkUrl variable properly defined
        function renderNotifications(notifications) {
            if (!list) return;
            if (notifications.length === 0) {
                list.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">No notifications</div>';
                return;
            }
            let html = '';
            notifications.forEach(n => {
                const isRead = n.is_read ? '' : 'bg-blue-50 dark:bg-blue-900/20';
                const linkUrl = n.link || '#'; // ← FIX: linkUrl ကို ဒီမှာ သတ်မှတ်ပါ
                
                html += `
                    <a href="${escapeHtml(linkUrl)}" 
                       class="block p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition notification-item ${isRead}" 
                       data-id="${escapeHtml(String(n.id))}">
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(n.title)}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(n.message)}</p>
                                <p class="text-xs text-gray-400 mt-1">${escapeHtml(n.time_ago)}</p>
                            </div>
                            ${!n.is_read ? `<span class="w-2 h-2 bg-blue-500 rounded-full mt-1 flex-shrink-0"></span>` : ''}
                        </div>
                    </a>
                `;
            });
            list.innerHTML = html;

            list.querySelectorAll('[data-id]').forEach(el => {
                el.addEventListener('click', function(e) {
                    const id = this.dataset.id;
                    markAsRead(id);
                });
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ================================================================
        // ✅ OPTIMISTIC UI – Instant removal of notification
        // ================================================================
        function markAsRead(id) {
            console.log('📌 Marking as read (optimistic):', id);

            // ---------- 1. Update badge instantly ----------
            const oldCount = parseInt(badge?.textContent) || 0;
            const newCount = Math.max(0, oldCount - 1);
            updateBadge(newCount);

            // ---------- 2. Remove the notification item from DOM ----------
            if (list) {
                const items = list.querySelectorAll('[data-id]');
                let removed = false;
                items.forEach(el => {
                    if (el.dataset.id == id) {
                        el.remove();
                        removed = true;
                        console.log('🎨 UI instantly removed notification:', id);
                    }
                });
                // If list is now empty, show empty message
                if (removed) {
                    const remaining = list.querySelectorAll('[data-id]');
                    if (remaining.length === 0) {
                        list.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">No notifications</div>';
                    }
                }
            }

            // ---------- 3. Send request to server ----------
            fetchJSON(BASE_URL + '/api/notifications/read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(data => {
                if (data.success) {
                    console.log('✅ Server confirmed mark as read.');
                } else {
                    console.warn('⚠️ Server failed to mark as read. Rolling back...');
                    rollbackMarkAsRead(id, oldCount);
                }
            })
            .catch(err => {
                console.error('❌ Error marking as read:', err.message);
                rollbackMarkAsRead(id, oldCount);
            });
        }

        // ---------- Rollback function: re-fetch and re-render ----------
        function rollbackMarkAsRead(id, oldCount) {
            // Restore badge count
            updateBadge(oldCount);
            // Re-fetch notifications to restore the item (if still unread)
            fetchNotifications();
            console.log('🔄 Re-fetched notifications to rollback for id:', id);
        }

        // ── Bell click ──
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('🔔 Bell clicked');
            isDropdownOpen = !isDropdownOpen;
            if (dropdown) {
                dropdown.classList.toggle('hidden', !isDropdownOpen);
            }
            if (isDropdownOpen) {
                fetchNotifications();
            }
        });

        if (mobileBell) {
            mobileBell.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('📱 Mobile bell clicked');
                bell.click();
            });
        }

        // ── Click outside to close ──
        document.addEventListener('click', function(e) {
            const container = document.getElementById('notification-container');
            if (container && !container.contains(e.target)) {
                if (dropdown) {
                    dropdown.classList.add('hidden');
                    isDropdownOpen = false;
                }
            }
        });

        // ── Mark all as read ──
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('📌 Marking all as read');
                fetchJSON(BASE_URL + '/api/notifications/read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                })
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                        if (dropdown) dropdown.classList.add('hidden');
                        isDropdownOpen = false;
                    } else {
                        console.warn('⚠️ Mark all as read failed:', data);
                    }
                })
                .catch(err => console.error('❌ Error marking all as read:', err.message));
            });
        }

        // ================================================================
        // 🚀 REAL-TIME POLLING – EVERY 2 SECONDS
        // ================================================================

        fetchNotifications(); // initial
        pollingInterval = setInterval(fetchNotifications, 2000);
        console.log('⏱️ Polling set to 2 seconds');

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('👁️ Tab visible – refreshing notifications');
                fetchNotifications();
            }
        });

        window.addEventListener('focus', function() {
            console.log('👁️ Window focused – refreshing notifications');
            fetchNotifications();
        });

        window.addEventListener('beforeunload', function() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                console.log('🧹 Polling interval cleared');
            }
        });

        console.log('✅ Notification system ready (polling every 2 seconds)');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
})();