<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Notifications Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="80" class="notification-dropdown">
                    <x-slot name="trigger">
                        <button id="notification-trigger" class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 12.683A17.925 17.925 0 012 21.6V19a2 2 0 00-2-2H2a2 2 0 002-2V9a2 2 0 012-2 2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 002 2v2a2 2 0 01-2 2H6.8a17.925 17.925 0 01-2.932-8.317z"></path>
                            </svg>
                            <span id="notification-badge" class="hidden absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">0</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-600">
                            <h6 class="text-sm font-medium text-gray-900 dark:text-gray-100">Notifications</h6>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Stay updated with your network</p>
                        </div>

                        <div id="notification-list" class="max-h-96 overflow-y-auto">
                            <div id="notification-loading" class="flex items-center justify-center py-4">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                                <span class="ml-2 text-sm text-gray-500">Loading...</span>
                            </div>
                        </div>

                        <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-600">
                            <a href="{{ route('notifications') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                View all notifications
                            </a>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <!-- Mobile Notifications -->
            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Notifications</span>
                    <span id="mobile-notification-badge" class="hidden inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">0</span>
                </div>
                <div id="mobile-notification-list" class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                    <div id="mobile-notification-loading" class="flex items-center justify-center py-2">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                        <span class="ml-2 text-xs text-gray-500">Loading...</span>
                    </div>
                </div>
                <a href="{{ route('notifications') }}" class="block mt-2 text-xs text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                    View all notifications
                </a>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
// Notification dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    let notificationPollingInterval;
    let lastUnreadCount = 0;

    // Load initial notifications
    loadNotifications();

    // Start polling for new notifications
    startNotificationPolling();

    function loadNotifications() {
        const notificationList = document.getElementById('notification-list');
        const loadingDiv = document.getElementById('notification-loading');

        fetch('/ajax/notifications/dropdown', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationDropdown(data.notifications);
                updateNotificationBadge(data.unread_count);
                lastUnreadCount = data.unread_count;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showNotificationError();

            // Retry after 5 seconds
            setTimeout(() => {
                loadNotifications();
            }, 5000);
        });
    }

    function updateNotificationDropdown(notifications) {
        // Update desktop dropdown
        const notificationList = document.getElementById('notification-list');
        const loadingDiv = document.getElementById('notification-loading');

        // Update mobile dropdown
        const mobileNotificationList = document.getElementById('mobile-notification-list');
        const mobileLoadingDiv = document.getElementById('mobile-notification-loading');

        if (notifications.length === 0) {
            // Desktop empty state
            notificationList.innerHTML = `
                <div class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 12.683A17.925 17.925 0 012 21.6V19a2 2 0 00-2-2H2a2 2 0 002-2V9a2 2 0 012-2 2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 002 2v2a2 2 0 01-2 2H6.8a17.925 17.925 0 01-2.932-8.317z"></path>
                    </svg>
                    No new notifications
                </div>
            `;

            // Mobile empty state
            mobileNotificationList.innerHTML = `
                <div class="px-2 py-2 text-center text-xs text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-6 w-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 12.683A17.925 17.925 0 012 21.6V19a2 2 0 00-2-2H2a2 2 0 002-2V9a2 2 0 012-2 2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 002 2v2a2 2 0 01-2 2H6.8a17.925 17.925 0 01-2.932-8.317z"></path>
                    </svg>
                    No notifications
                </div>
            `;
        } else {
            let desktopHtml = '';
            let mobileHtml = '';

            notifications.slice(0, 5).forEach(notification => {
                // Desktop notification item
                desktopHtml += `
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer ${!notification.is_read ? 'bg-blue-50 dark:bg-blue-900/20' : ''}"
                         onclick="markNotificationAsRead(${notification.id})">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gradient-${notification.color} rounded-full flex items-center justify-content-center">
                                    <i class="fas fa-${notification.icon} text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    ${notification.title}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                    ${notification.message.length > 60 ? notification.message.substring(0, 60) + '...' : notification.message}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    ${formatTimeAgo(notification.created_at)}
                                </p>
                            </div>
                        </div>
                    </div>
                `;

                // Mobile notification item
                mobileHtml += `
                    <div class="mobile-notification-item px-2 py-2 border-b border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer ${!notification.is_read ? 'bg-blue-50 dark:bg-blue-900/20' : ''}"
                         onclick="markNotificationAsRead(${notification.id})">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-2">
                                <div class="w-6 h-6 bg-gradient-${notification.color} rounded-full flex items-center justify-content-center">
                                    <i class="fas fa-${notification.icon} text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                                    ${notification.title}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    ${notification.message.length > 40 ? notification.message.substring(0, 40) + '...' : notification.message}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });

            if (notifications.length > 5) {
                desktopHtml += `
                    <div class="px-4 py-2 text-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">+${notifications.length - 5} more notifications</span>
                    </div>
                `;
            }

            notificationList.innerHTML = desktopHtml;
            mobileNotificationList.innerHTML = mobileHtml;
        }
    }

    function updateNotificationBadge(count) {
        // Update desktop badge
        const badge = document.getElementById('notification-badge');
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }

        // Update mobile badge
        const mobileBadge = document.getElementById('mobile-notification-badge');
        if (count > 0) {
            mobileBadge.textContent = count > 99 ? '99+' : count;
            mobileBadge.classList.remove('hidden');
        } else {
            mobileBadge.classList.add('hidden');
        }
    }

    function showNotificationError() {
        const notificationList = document.getElementById('notification-list');
        notificationList.innerHTML = `
            <div class="px-4 py-3 text-center text-sm text-red-600 dark:text-red-400">
                <svg class="mx-auto h-8 w-8 text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Failed to load notifications
            </div>
        `;
    }

    function startNotificationPolling() {
        notificationPollingInterval = setInterval(() => {
            checkForNewNotifications();
        }, 30000); // Poll every 30 seconds
    }

    function checkForNewNotifications() {
        fetch('/ajax/notifications/check-updates', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread);

                // If there are new notifications, refresh the dropdown
                if (data.unread > lastUnreadCount) {
                    loadNotifications();
                }

                lastUnreadCount = data.unread;
            }
        })
        .catch(error => {
            console.error('Error checking for new notifications:', error);
            // Continue polling even if there's an error
        });
    }

    // Global function to mark notification as read
    window.markNotificationAsRead = function(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications(); // Refresh the dropdown
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
            // Show user-friendly error message
            showToast('Failed to mark notification as read. Please try again.', 'error');
        });
    };

    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

        return date.toLocaleDateString();
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible position-fixed fade show`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        `;

        const iconClass = type === 'error' ? 'exclamation-triangle' :
                         type === 'success' ? 'check-circle' :
                         type === 'warning' ? 'exclamation-circle' : 'info-circle';

        toast.innerHTML = `
            <i class="fas fa-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 150);
            }
        }, 3000);
    }

    // Clean up polling when page unloads
    window.addEventListener('beforeunload', function() {
        if (notificationPollingInterval) {
            clearInterval(notificationPollingInterval);
        }
    });

    // Add responsive styles
    const style = document.createElement('style');
    style.textContent = `
        @media (max-width: 768px) {
            .notification-dropdown {
                width: 90vw !important;
                max-width: 320px;
            }

            .mobile-notification-item {
                padding: 0.75rem;
            }

            .mobile-notification-item .text-xs {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .notification-dropdown {
                width: 95vw !important;
                left: 2.5vw !important;
                right: 2.5vw !important;
            }
        }
    `;
    document.head.appendChild(style);
});
</script>
