@extends('layouts.dashboard')

@section('content')
<style>
.notification-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background-color: rgba(0,0,0,0.02) !important;
}

.notification-actions .btn {
    white-space: nowrap;
    min-width: 70px;
}

.badge-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .notification-actions {
        flex-direction: column;
        gap: 0.25rem !important;
    }

    .notification-actions .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        min-width: 60px;
    }

    .notification-item {
        padding: 1rem 0.75rem;
    }

    .notification-content h6 {
        font-size: 0.9rem;
    }

    .notification-content p {
        font-size: 0.8rem;
    }

    .card-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch !important;
    }

    .card-header .d-flex .btn {
        width: 100%;
    }

    .dropdown {
        width: 100%;
    }

    .dropdown .dropdown-toggle {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .card-body {
        padding: 1rem 0.75rem;
    }

    .notification-icon {
        display: none;
    }

    .notification-content {
        margin-left: 0;
    }

    .notification-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        flex-direction: row;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 0.375rem;
        padding: 0.25rem;
    }

    .notification-item {
        position: relative;
        padding-right: 3rem;
    }

    .card-header .d-flex.flex-column {
        gap: 0.5rem !important;
    }

    .input-group.input-group-sm {
        margin-bottom: 0.25rem;
    }

    .card-header .d-flex.gap-2 {
        flex-wrap: wrap;
        justify-content: center;
    }

    .card-header .btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
}

@media (max-width: 480px) {
    .input-group.input-group-sm .form-control {
        font-size: 0.75rem;
    }

    .input-group.input-group-sm .input-group-text {
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
    }

    .card-header h6 {
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .card-header .d-flex.gap-2 .btn {
        flex: 1 1 auto;
        min-width: 0;
    }
}
</style>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Notifications</h6>
                        <p class="text-sm text-secondary mb-0">Track your network activity and important updates</p>
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-2">
                        <!-- Search and Filters Row -->
                        <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="notification-search" placeholder="Search notifications..." onkeyup="searchNotifications()">
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" class="form-control" id="date-from" onchange="filterByDate()">
                                <span class="input-group-text">-</span>
                                <input type="date" class="form-control" id="date-to" onchange="filterByDate()">
                            </div>
                        </div>

                        <!-- Action Buttons Row -->
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                                <i class="fas fa-check-double me-1"></i>Mark All Read
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAllRead()">
                                <i class="fas fa-trash me-1"></i>Delete All Read
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="filterNotifications('all')">All</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterNotifications('unread')">Unread</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterNotifications('success')">Success</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterNotifications('info')">Info</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterNotifications('warning')">Warning</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterNotifications('error')">Error</a></li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-sort me-1"></i>Sort
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="sortNotifications('newest')">Newest First</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortNotifications('oldest')">Oldest First</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortNotifications('unread')">Unread First</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortNotifications('type')">By Type</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pt-0 pb-2">
                    <div class="notifications-list">
                        @forelse($notifications as $notification)
                        <div class="notification-item d-flex align-items-start p-3 border-bottom {{ $notification->is_read ? 'bg-light' : 'bg-white border-start border-primary border-3' }}"
                             data-notification-id="{{ $notification->id }}"
                             onclick="markAsRead({{ $notification->id }})">
                            <div class="notification-icon me-3 mt-1">
                                <div class="icon icon-sm bg-gradient-{{ $notification->color }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-{{ $notification->icon }}"></i>
                                </div>
                            </div>
                            <div class="notification-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 text-{{ $notification->color }} font-weight-bold">
                                        {{ $notification->title }}
                                        @if(!$notification->is_read)
                                        <span class="badge bg-{{ $notification->color }} ms-2">New</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-2 text-sm">{{ $notification->message }}</p>
                                @if($notification->data)
                                <div class="notification-data">
                                    @if(isset($notification->data['amount']))
                                    <small class="text-success font-weight-bold">
                                        ₱{{ number_format($notification->data['amount'], 2) }}
                                    </small>
                                    @endif
                                    @if(isset($notification->data['referral_name']))
                                    <small class="text-primary">
                                        <i class="fas fa-user me-1"></i>{{ $notification->data['referral_name'] }}
                                    </small>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="notification-actions ms-3 d-flex gap-1">
                                @if(!$notification->is_read)
                                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); markAsRead({{ $notification->id }})" title="Mark as read">
                                    <i class="fas fa-check me-1"></i>Read
                                </button>
                                @endif
                                <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteNotification({{ $notification->id }})" title="Delete notification">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="icon icon-xl bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-bell" style="font-size: 2rem;"></i>
                            </div>
                            <h6 class="text-muted mb-2">No notifications yet</h6>
                            <p class="text-sm text-muted">When you receive notifications, they'll appear here.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Real Pagination -->
                    @if($notifications->hasPages())
                    <div class="mt-4">
                        <nav aria-label="Notifications pagination">
                            <ul class="pagination justify-content-center mb-0">
                                {{-- Previous Page Link --}}
                                @if ($notifications->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                                @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->previousPageUrl() }}" aria-label="Previous page">
                                        <span aria-hidden="true">&laquo;</span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($notifications->getUrlRange(1, min($notifications->lastPage(), 5)) as $page => $url)
                                @if ($page == $notifications->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                                @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                                @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($notifications->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->nextPageUrl() }}" aria-label="Next page">
                                        <span aria-hidden="true">&raquo;</span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </li>
                                @else
                                <li class="page-item disabled">
                                    <span class="page-link">Next</span>
                                </li>
                                @endif
                            </ul>
                        </nav>
                        <p class="text-center text-sm text-muted mt-2">
                            Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} notifications
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    console.log('Marking notification as read:', notificationId);

    const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
    const readButton = notificationItem ? notificationItem.querySelector('.btn-outline-primary') : null;

    // Show loading state
    if (readButton) {
        readButton.disabled = true;
        readButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        // Reset loading state
        if (readButton) {
            readButton.disabled = false;
            readButton.innerHTML = '<i class="fas fa-check me-1"></i>Read';
        }
        return;
    }

    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Update UI to show as read
            if (notificationItem) {
                notificationItem.classList.remove('bg-white', 'border-start', 'border-primary', 'border-3');
                notificationItem.classList.add('bg-light');
                const badge = notificationItem.querySelector('.badge');
                if (badge) badge.remove();
                // Remove the "Read" button
                if (readButton) readButton.remove();
            }

            // Update notification count in navigation if it exists
            updateNavigationBadge();
        } else {
            console.error('Server returned success=false');
            // Reset loading state
            if (readButton) {
                readButton.disabled = false;
                readButton.innerHTML = '<i class="fas fa-check me-1"></i>Read';
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        Alert.error('Error', 'Failed to mark notification as read. Please try again.');
        // Reset loading state
        if (readButton) {
            readButton.disabled = false;
            readButton.innerHTML = '<i class="fas fa-check me-1"></i>Read';
        }
    });
}

function markAllAsRead() {
    const markAllButton = document.querySelector('button[onclick="markAllAsRead()"]');

    // Show loading state
    if (markAllButton) {
        markAllButton.disabled = true;
        markAllButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Marking...';
    }

    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all notifications to show as read
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('bg-white', 'border-start', 'border-primary', 'border-3');
                item.classList.add('bg-light');
                const badge = item.querySelector('.badge');
                if (badge) badge.remove();
                // Remove only the "Read" buttons, keep the "Delete" buttons
                const readButton = item.querySelector('.btn-outline-primary');
                if (readButton) readButton.remove();
            });

            // Update notification count in navigation
            updateNavigationBadge();

            // Reset button state
            if (markAllButton) {
                markAllButton.disabled = false;
                markAllButton.innerHTML = '<i class="fas fa-check-double me-1"></i>Mark All Read';
            }
        } else {
            // Reset button state
            if (markAllButton) {
                markAllButton.disabled = false;
                markAllButton.innerHTML = '<i class="fas fa-check-double me-1"></i>Mark All Read';
            }
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
        Alert.error('Error', 'Failed to mark all notifications as read. Please try again.');
        // Reset button state
        if (markAllButton) {
            markAllButton.disabled = false;
            markAllButton.innerHTML = '<i class="fas fa-check-double me-1"></i>Mark All Read';
        }
    });
}

function deleteNotification(notificationId) {
    console.log('Deleting notification:', notificationId);

    const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
    const deleteButton = notificationItem ? notificationItem.querySelector('.btn-outline-danger') : null;

    Alert.confirm(
        'Delete Notification',
        'Are you sure you want to delete this notification?',
        'Delete',
        'Cancel'
    ).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        // Show loading state
        if (deleteButton) {
            deleteButton.disabled = true;
            deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            // Reset loading state
            if (deleteButton) {
                deleteButton.disabled = false;
                deleteButton.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
            }
            return;
        }

        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Delete response data:', data);
            if (data.success) {
                // Remove the notification from the UI
                if (notificationItem) {
                    notificationItem.remove();
                    // If no notifications left, reload the page to show empty state
                    if (document.querySelectorAll('.notification-item').length === 0) {
                        location.reload();
                    }
                }

                // Update notification count in navigation
                updateNavigationBadge();
            } else {
                console.error('Server returned success=false for delete');
                // Reset loading state
                if (deleteButton) {
                    deleteButton.disabled = false;
                    deleteButton.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
                }
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
            Alert.error('Error', 'Failed to delete notification. Please try again.');
            // Reset loading state
            if (deleteButton) {
                deleteButton.disabled = false;
                deleteButton.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
            }
        });
    });
}

function deleteAllRead() {
    const deleteAllButton = document.querySelector('button[onclick="deleteAllRead()"]');

    Alert.confirm(
        'Delete All Read Notifications',
        'Are you sure you want to delete all read notifications? This action cannot be undone.',
        'Delete All',
        'Cancel'
    ).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        // Show loading state
        if (deleteAllButton) {
            deleteAllButton.disabled = true;
            deleteAllButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
        }

        fetch('/notifications/delete-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all read notifications from the UI
                document.querySelectorAll('.notification-item:not(.border-start)').forEach(item => {
                    item.remove();
                });
                // If no notifications left, reload the page to show empty state
                if (document.querySelectorAll('.notification-item').length === 0) {
                    location.reload();
                }

                // Update notification count in navigation
                updateNavigationBadge();

                // Reset button state
                if (deleteAllButton) {
                    deleteAllButton.disabled = false;
                    deleteAllButton.innerHTML = '<i class="fas fa-trash me-1"></i>Delete All Read';
                }
            } else {
                // Reset button state
                if (deleteAllButton) {
                    deleteAllButton.disabled = false;
                    deleteAllButton.innerHTML = '<i class="fas fa-trash me-1"></i>Delete All Read';
                }
            }
        })
        .catch(error => {
            console.error('Error deleting read notifications:', error);
            Alert.error('Error', 'Failed to delete read notifications. Please try again.');
            // Reset button state
            if (deleteAllButton) {
                deleteAllButton.disabled = false;
                deleteAllButton.innerHTML = '<i class="fas fa-trash me-1"></i>Delete All Read';
            }
        });
    });
}

function filterNotifications(type) {
    const url = new URL(window.location);
    if (type === 'all') {
        url.searchParams.delete('type');
    } else {
        url.searchParams.set('type', type);
    }
    // Preserve other filters
    const search = document.getElementById('notification-search').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;

    if (search) url.searchParams.set('search', search);
    if (dateFrom) url.searchParams.set('date_from', dateFrom);
    if (dateTo) url.searchParams.set('date_to', dateTo);

    window.location.href = url.toString();
}

function searchNotifications() {
    const searchTerm = document.getElementById('notification-search').value.toLowerCase();
    const notifications = document.querySelectorAll('.notification-item');

    notifications.forEach(notification => {
        const title = notification.querySelector('h6').textContent.toLowerCase();
        const message = notification.querySelector('p').textContent.toLowerCase();

        if (title.includes(searchTerm) || message.includes(searchTerm)) {
            notification.style.display = 'flex';
        } else {
            notification.style.display = 'none';
        }
    });
}

function filterByDate() {
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    const notifications = document.querySelectorAll('.notification-item');

    notifications.forEach(notification => {
        const notificationDate = new Date(notification.querySelector('small').textContent);
        let show = true;

        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            if (notificationDate < fromDate) show = false;
        }

        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(23, 59, 59, 999); // End of day
            if (notificationDate > toDate) show = false;
        }

        notification.style.display = show ? 'flex' : 'none';
    });
}

function sortNotifications(sortBy) {
    const container = document.querySelector('.notifications-list');
    const notifications = Array.from(document.querySelectorAll('.notification-item'));

    notifications.sort((a, b) => {
        switch (sortBy) {
            case 'newest':
                return new Date(b.querySelector('small').textContent) - new Date(a.querySelector('small').textContent);
            case 'oldest':
                return new Date(a.querySelector('small').textContent) - new Date(b.querySelector('small').textContent);
            case 'unread':
                const aUnread = a.classList.contains('border-start');
                const bUnread = b.classList.contains('border-start');
                if (aUnread && !bUnread) return -1;
                if (!aUnread && bUnread) return 1;
                return 0;
            case 'type':
                const aType = a.querySelector('.badge')?.textContent || '';
                const bType = b.querySelector('.badge')?.textContent || '';
                return aType.localeCompare(bType);
            default:
                return 0;
        }
    });

    // Clear and re-append sorted notifications
    container.innerHTML = '';
    notifications.forEach(notification => container.appendChild(notification));
}

function clearFilters() {
    // Clear search input
    document.getElementById('notification-search').value = '';

    // Clear date inputs
    document.getElementById('date-from').value = '';
    document.getElementById('date-to').value = '';

    // Show all notifications
    const notifications = document.querySelectorAll('.notification-item');
    notifications.forEach(notification => {
        notification.style.display = 'flex';
    });

    // Reset URL parameters
    const url = new URL(window.location);
    url.searchParams.delete('search');
    url.searchParams.delete('date_from');
    url.searchParams.delete('date_to');
    url.searchParams.delete('type');
    window.history.replaceState({}, '', url.toString());
}

// Real-time notification polling
let notificationPollingInterval;
let lastNotificationCount = {{ $notifications->total() }};
let lastUnreadCount = {{ $notifications->where('is_read', false)->count() }};

function startNotificationPolling() {
    notificationPollingInterval = setInterval(() => {
        checkForNewNotifications();
    }, 30000); // Poll every 30 seconds
}

function checkForNewNotifications() {
    fetch('/ajax/notifications/check-updates')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const currentTotal = data.total;
                const currentUnread = data.unread;

                // Update notification counts in header if they exist
                updateNotificationCounts(currentTotal, currentUnread);

                // If there are new notifications, show a subtle indicator
                if (currentTotal > lastNotificationCount) {
                    showNewNotificationIndicator(currentTotal - lastNotificationCount);
                }

                // Update last counts
                lastNotificationCount = currentTotal;
                lastUnreadCount = currentUnread;
            }
        })
        .catch(error => {
            console.error('Error checking for new notifications:', error);
        });
}

function updateNotificationCounts(total, unread) {
    // Update notification badge in navigation if it exists
    const notificationBadge = document.querySelector('.notification-badge');
    if (notificationBadge) {
        notificationBadge.textContent = unread;
        notificationBadge.style.display = unread > 0 ? 'inline' : 'none';
    }

    // Update page title with unread count
    if (unread > 0) {
        document.title = `(${unread}) Notifications - MLM Dashboard`;
    } else {
        document.title = 'Notifications - MLM Dashboard';
    }
}

function showNewNotificationIndicator(newCount) {
    // Create a subtle notification indicator
    const indicator = document.createElement('div');
    indicator.className = 'alert alert-info alert-dismissible position-fixed fade show';
    indicator.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    `;
    indicator.innerHTML = `
        <i class="fas fa-bell me-2"></i>
        You have ${newCount} new notification${newCount > 1 ? 's' : ''}!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(indicator);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (indicator.parentNode) {
            indicator.classList.remove('show');
            setTimeout(() => indicator.remove(), 150);
        }
    }, 5000);

    // Add click handler to refresh the page
    indicator.addEventListener('click', () => {
        location.reload();
    });
}

// Auto-refresh notifications list when new ones arrive
function refreshNotificationsList() {
    const currentFilter = new URLSearchParams(window.location.search).get('type') || 'all';

    fetch(`/ajax/notifications/list?type=${currentFilter}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                updateNotificationsList(data.notifications);
            }
        })
        .catch(error => {
            console.error('Error refreshing notifications:', error);
        });
}

function updateNotificationsList(notifications) {
    const container = document.querySelector('.notifications-list');

    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="icon icon-xl bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-bell" style="font-size: 2rem;"></i>
                </div>
                <h6 class="text-muted mb-2">No notifications yet</h6>
                <p class="text-sm text-muted">When you receive notifications, they'll appear here.</p>
            </div>
        `;
        return;
    }

    let html = '';
    notifications.forEach(notification => {
        html += `
            <div class="notification-item d-flex align-items-start p-3 border-bottom ${notification.is_read ? 'bg-light' : 'bg-white border-start border-primary border-3'}"
                 data-notification-id="${notification.id}"
                 onclick="markAsRead(${notification.id})">
                <div class="notification-icon me-3 mt-1">
                    <div class="icon icon-sm bg-gradient-${notification.color} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-${notification.icon}"></i>
                    </div>
                </div>
                <div class="notification-content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="mb-0 text-${notification.color} font-weight-bold">
                            ${notification.title}
                            ${!notification.is_read ? '<span class="badge bg-' + notification.color + ' ms-2">New</span>' : ''}
                        </h6>
                        <small class="text-muted">${formatTimeAgo(notification.created_at)}</small>
                    </div>
                    <p class="mb-2 text-sm">${notification.message}</p>
                    ${notification.data ? generateNotificationDataHtml(notification.data) : ''}
                </div>
                <div class="notification-actions ms-3 d-flex gap-1">
                    ${!notification.is_read ? '<button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); markAsRead(' + notification.id + ')" title="Mark as read"><i class="fas fa-check me-1"></i>Read</button>' : ''}
                    <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteNotification(${notification.id})" title="Delete notification">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;

    return date.toLocaleDateString();
}

function generateNotificationDataHtml(data) {
    let html = '<div class="notification-data">';

    if (data.amount) {
        html += `<small class="text-success font-weight-bold">₱${parseFloat(data.amount).toLocaleString()}</small>`;
    }

    if (data.referral_name) {
        html += `<small class="text-primary"><i class="fas fa-user me-1"></i>${data.referral_name}</small>`;
    }

    html += '</div>';
    return html;
}

// Browser notification support
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

function showBrowserNotification(title, message, icon = '/favicon.ico') {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            body: message,
            icon: icon,
            tag: 'mlm-notification'
        });
    }
}

// Initialize real-time features
document.addEventListener('DOMContentLoaded', function() {
    startNotificationPolling();
    requestNotificationPermission();

    // Listen for visibility change to optimize polling
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Pause polling when tab is not visible
            if (notificationPollingInterval) {
                clearInterval(notificationPollingInterval);
            }
        } else {
            // Resume polling when tab becomes visible
            startNotificationPolling();
        }
    });
});

// Update navigation notification badge
function updateNavigationBadge() {
    // Update notification badge in navigation if it exists
    const notificationBadge = document.querySelector('.notification-badge');
    if (notificationBadge) {
        fetch('/ajax/notifications/check-updates')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.unread > 0) {
                        notificationBadge.textContent = data.unread > 99 ? '99+' : data.unread;
                        notificationBadge.style.display = 'inline';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating navigation badge:', error);
            });
    }
}

// Clean up polling when page unloads
window.addEventListener('beforeunload', function() {
    if (notificationPollingInterval) {
        clearInterval(notificationPollingInterval);
    }
});
</script>
@endsection
