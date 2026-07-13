document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.getElementById('main-content');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    function loadContent(url) {
        // Show loading indicator
        mainContent.innerHTML = `
            <div class="flex justify-center items-center h-64">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
            </div>
        `;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            mainContent.innerHTML = html;
            // Re-bind any dynamic events (e.g., delete confirmation)
            reinitializeDynamicContent();
        })
        .catch(error => {
            mainContent.innerHTML = `
                <div class="text-red-500 text-center p-8">
                    <i class="fas fa-exclamation-circle text-3xl mb-2 block"></i>
                    Failed to load content. Please try again.
                </div>
            `;
            console.error('Error loading content:', error);
        });
    }

    // Re-bind events for dynamic content (e.g., delete buttons)
    function reinitializeDynamicContent() {
        // Delete buttons already have onclick attributes, but if you use addEventListener,
        // you can attach here.
    }

    // Sidebar click handling
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            if (url) {
                // Update browser history
                history.pushState({ url: url }, '', url);
                loadContent(url);
                // Update active link style
                sidebarLinks.forEach(l => l.classList.remove('bg-blue-50', 'dark:bg-blue-900/30', 'text-blue-600', 'dark:text-blue-400'));
                this.classList.add('bg-blue-50', 'dark:bg-blue-900/30', 'text-blue-600', 'dark:text-blue-400');
            }
        });
    });

    // Handle browser back/forward
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.url) {
            loadContent(event.state.url);
            // Update active link
            sidebarLinks.forEach(link => {
                link.classList.remove('bg-blue-50', 'dark:bg-blue-900/30', 'text-blue-600', 'dark:text-blue-400');
                if (link.getAttribute('href') === event.state.url) {
                    link.classList.add('bg-blue-50', 'dark:bg-blue-900/30', 'text-blue-600', 'dark:text-blue-400');
                }
            });
        }
    });
});