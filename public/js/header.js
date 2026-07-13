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

    const openLoginBtns = document.querySelectorAll('#open-login-modal, #mobile-open-login');
    openLoginBtns.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal(registerModal);
                openModal(loginModal);
                const mobileMenu = document.getElementById('mobile-menu');
                if (mobileMenu) mobileMenu.classList.add('hidden');
            });
        }
    });

    const openRegisterBtns = document.querySelectorAll('#open-register-modal, #mobile-open-register');
    openRegisterBtns.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal(loginModal);
                openModal(registerModal);
                const mobileMenu = document.getElementById('mobile-menu');
                if (mobileMenu) mobileMenu.classList.add('hidden');
            });
        }
    });

    const closeBtns = document.querySelectorAll('.modal-close');
    closeBtns.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal-overlay');
                if (modal) closeModal(modal);
            });
        }
    });

    [loginModal, registerModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this);
                }
            });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (loginModal && loginModal.classList.contains('active')) closeModal(loginModal);
            if (registerModal && registerModal.classList.contains('active')) closeModal(registerModal);
        }
    });

    const switchToRegister = document.getElementById('switch-to-register');
    if (switchToRegister) {
        switchToRegister.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal(loginModal);
            setTimeout(function() { openModal(registerModal); }, 300);
        });
    }

    const switchToLogin = document.getElementById('switch-to-login');
    if (switchToLogin) {
        switchToLogin.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal(registerModal);
            setTimeout(function() { openModal(loginModal); }, 300);
        });
    }
    
    console.log('✅ Modal controls initialized');
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
// ===================== NOTIFICATION SYSTEM (IMPROVED) ============
// ================================================================
(function() {
    'use strict';

    // Wait for DOM to be fully ready
    function initNotifications() {
        console.log('🔔 Initializing notification system...');

        const bell = document.getElementById('notification-bell');
        const badge = document.getElementById('notification-badge');
        const dropdown = document.getElementById('notification-dropdown');
        const list = document.getElementById('notification-list');
        const markAllBtn = document.getElementById('mark-all-read');
        const mobileBell = document.getElementById('mobile-notification-bell');
        const mobileBadge = document.getElementById('mobile-notification-badge');

        // ✅ If bell is missing, log and exit (maybe user not logged in)
        if (!bell) {
            console.warn('⚠️ Notification bell not found in DOM (user may not be logged in)');
            return;
        }

        console.log('✅ Notification bell found');

        let isDropdownOpen = false;
        let lastNotificationCount = 0;

        // ── Fetch notifications ──
        function fetchNotifications() {
            console.log('🔄 Fetching notifications...');
            fetch('/api/notifications', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                updateBadge(data.unread_count);
                if (isDropdownOpen) {
                    renderNotifications(data.notifications);
                }
                lastNotificationCount = data.unread_count;
            })
            .catch(err => console.error('❌ Error fetching notifications:', err));
        }

        // ── Update badge ──
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
                if (badge) badge.classList.add('hidden');
                if (mobileBadge) mobileBadge.classList.add('hidden');
            }
        }

        // ── Render notifications in dropdown ──
        function renderNotifications(notifications) {
            if (!list) return;
            if (notifications.length === 0) {
                list.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">No notifications</div>';
                return;
            }
            let html = '';
            notifications.forEach(n => {
                const isRead = n.is_read ? '' : 'bg-blue-50 dark:bg-blue-900/20';
                html += `
                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer notification-item ${isRead}" data-id="${n.id}">
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(n.title)}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(n.message)}</p>
                                <p class="text-xs text-gray-400 mt-1">${escapeHtml(n.time_ago)}</p>
                            </div>
                            ${!n.is_read ? `<span class="w-2 h-2 bg-blue-500 rounded-full mt-1 flex-shrink-0"></span>` : ''}
                        </div>
                    </div>
                `;
            });
            list.innerHTML = html;

            // Click on notification to mark as read
            list.querySelectorAll('[data-id]').forEach(el => {
                el.addEventListener('click', function() {
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

        // ── Mark one as read ──
        function markAsRead(id) {
            fetch('/api/notifications/read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchNotifications();
                }
            })
            .catch(err => console.error('❌ Error marking as read:', err));
        }

        // ── Toggle dropdown on bell click ──
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('🔔 Bell clicked');
            isDropdownOpen = !isDropdownOpen;
            if (dropdown) {
                dropdown.classList.toggle('hidden', !isDropdownOpen);
                console.log('📋 Dropdown visibility:', dropdown.classList.contains('hidden') ? 'hidden' : 'visible');
            }
            if (isDropdownOpen) {
                fetchNotifications();
            }
        });

        // ── Mobile bell click → trigger desktop bell ──
        if (mobileBell) {
            mobileBell.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('📱 Mobile bell clicked');
                bell.click(); // simulate desktop click
            });
        }

        // ── Close dropdown on outside click ──
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
                fetch('/api/notifications/read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                        if (dropdown) dropdown.classList.add('hidden');
                        isDropdownOpen = false;
                    }
                })
                .catch(err => console.error('❌ Error marking all as read:', err));
            });
        }

        // ── Initial fetch and interval ──
        fetchNotifications();
        setInterval(fetchNotifications, 30000);

        console.log('✅ Notification system fully initialized');
    }

    // Run when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
})();