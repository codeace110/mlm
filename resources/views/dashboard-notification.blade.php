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
                    <div class="d-flex gap-2">
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
                            </ul>
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

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
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
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('bg-white', 'border-start', 'border-primary', 'border-3');
                notificationItem.classList.add('bg-light');
                const badge = notificationItem.querySelector('.badge');
                if (badge) badge.remove();
                // Remove only the "Read" button, keep the "Delete" button
                const readButton = notificationItem.querySelector('.btn-outline-primary');
                if (readButton) readButton.remove();
            }
        } else {
            console.error('Server returned success=false');
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        Alert.error('Error', 'Failed to mark notification as read. Please try again.');
    });
}

function markAllAsRead() {
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
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
        Alert.error('Error', 'Failed to mark all notifications as read. Please try again.');
    });
}

function deleteNotification(notificationId) {
    console.log('Deleting notification:', notificationId);

    Alert.confirm(
        'Delete Notification',
        'Are you sure you want to delete this notification?',
        'Delete',
        'Cancel'
    ).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
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
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.remove();
                // If no notifications left, reload the page to show empty state
                if (document.querySelectorAll('.notification-item').length === 0) {
                    location.reload();
                }
            }
        } else {
            console.error('Server returned success=false for delete');
        }
    })
    .catch(error => {
        console.error('Error deleting notification:', error);
        Alert.error('Error', 'Failed to delete notification. Please try again.');
    });
}

function deleteAllRead() {
    Alert.confirm(
        'Delete All Read Notifications',
        'Are you sure you want to delete all read notifications? This action cannot be undone.',
        'Delete All',
        'Cancel'
    ).then((result) => {
        if (!result.isConfirmed) {
            return;
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
        }
    })
    .catch(error => {
        console.error('Error deleting read notifications:', error);
        Alert.error('Error', 'Failed to delete read notifications. Please try again.');
    });
}

function filterNotifications(type) {
    const url = new URL(window.location);
    if (type === 'all') {
        url.searchParams.delete('type');
    } else {
        url.searchParams.set('type', type);
    }
    window.location.href = url.toString();
}
</script>
@endsection
