@extends('layouts.admin')

@section('title', 'Referral Codes')

@section('content')
<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Codes</p>
                                <h5 class="font-weight-bolder mb-0" id="total-codes">{{ $stats['total'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Used Codes</p>
                                <h5 class="font-weight-bolder mb-0" id="used-codes">{{ $stats['used'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Available Codes</p>
                                <h5 class="font-weight-bolder mb-0" id="available-codes">{{ $stats['available'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-circle-08 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Assigned Codes</p>
                                <h5 class="font-weight-bolder mb-0" id="assigned-codes">{{ $stats['assigned'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="ni ni-user-run text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">Referral Codes Management</h5>
                            <p class="text-sm mb-0">
                                Generate and track referral codes (50 codes per batch)
                            </p>
                        </div>
                        <div class="ms-auto my-auto mt-lg-0 mt-4">
                             <div class="ms-auto my-auto">
                                 @if($stats['available'] == 0 && $stats['assigned'] == 0)
                                 <button type="button" id="generate-codes-btn" class="btn btn-primary btn-sm">
                                     <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                     Generate 50 Codes
                                 </button>
                                 @else
                                 <span class="text-muted">Cannot generate new codes until all current codes are used</span>
                                 @endif
                             </div>
                         </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-0">
                    <!-- Bulk Actions -->
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom" id="bulk-actions" style="display: none;">
                        <div>
                            <span id="selected-count">0</span> codes selected
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="bulk-export">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning me-2" id="bulk-assign">
                                <i class="fas fa-user-plus me-1"></i>Bulk Assign
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="bulk-delete">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="clear-selection">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Code</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Used By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Sponsor</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Batch</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Generated</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($codes as $code)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="code-checkbox" value="{{ $code->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex px-2">
                                            <div class="my-auto">
                                                <h6 class="mb-0 text-sm">{{ $code->code }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{ $code->status == 'available' ? 'success' : ($code->status == 'assigned' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($code->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($code->used_by)
                                            {{ \App\Models\User::find($code->used_by)->name ?? 'User Not Found' }}
                                        @else
                                            Not Used
                                        @endif
                                    </td>
                                    <td>
                                        @if($code->assigned_to)
                                            {{ \App\Models\User::find($code->assigned_to)->name ?? 'User Not Found' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        {{ $code->batch_id ? '#' . $code->batch_id : 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $code->created_at->format('Y-m-d') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.referral_codes.show', $code) }}" class="btn btn-xs btn-info">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No referral codes found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer px-0">
                        {{ $codes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#generate-codes-btn').on('click', function() {
        const $btn = $(this);
        const $spinner = $btn.find('.spinner-border');

        // Show loading state
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');

        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.referral_codes.generate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update statistics
                    updateStatistics(response.stats);

                    // Show success message
                    showNotification(response.message, 'success');

                    // Refresh the page to show new codes
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(response.message, 'danger');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while generating codes.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'danger');
            },
            complete: function() {
                // Hide loading state
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
            }
        });
    });

    function updateStatistics(stats) {
        $('#total-codes').text(stats.total);
        $('#used-codes').text(stats.used);
        $('#available-codes').text(stats.available);
        $('#assigned-codes').text(stats.assigned);
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Append to body
        $('body').append(notification);

        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Real-time statistics updates
    function startStatisticsPolling() {
        setInterval(function() {
            $.ajax({
                url: '{{ route("admin.referral_codes.statistics") }}',
                method: 'GET',
                success: function(stats) {
                    updateStatistics(stats);
                },
                error: function() {
                    // Silently fail for polling errors
                }
            });
        }, 30000); // Update every 30 seconds
    }

    // Start polling when page loads
    startStatisticsPolling();

    // Bulk operations
    let selectedCodes = [];

    // Handle select all checkbox
    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.code-checkbox').prop('checked', isChecked);
        updateSelectedCodes();
    });

    // Handle individual checkboxes
    $(document).on('change', '.code-checkbox', function() {
        updateSelectedCodes();
    });

    function updateSelectedCodes() {
        selectedCodes = [];
        $('.code-checkbox:checked').each(function() {
            selectedCodes.push($(this).val());
        });

        if (selectedCodes.length > 0) {
            $('#bulk-actions').show();
            $('#selected-count').text(selectedCodes.length);
        } else {
            $('#bulk-actions').hide();
        }
    }

    // Clear selection
    $('#clear-selection').on('click', function() {
        $('.code-checkbox').prop('checked', false);
        $('#select-all').prop('checked', false);
        updateSelectedCodes();
    });

    // Bulk export
    $('#bulk-export').on('click', function() {
        if (selectedCodes.length === 0) {
            showNotification('Please select codes to export.', 'warning');
            return;
        }

        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("admin.referral_codes.bulk_export") }}'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        selectedCodes.forEach(function(codeId) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'codes[]',
                value: codeId
            }));
        });

        $('body').append(form);
        form.submit();
        form.remove();
    });

    // Bulk assign modal
    $('#bulk-assign').on('click', function() {
        if (selectedCodes.length === 0) {
            showNotification('Please select codes to assign.', 'warning');
            return;
        }

        // Create modal for bulk assign
        const modal = `
            <div class="modal fade" id="bulk-assign-modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Bulk Assign Codes</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Assign ${selectedCodes.length} selected codes to:</p>
                            <div class="mb-3">
                                <label for="bulk-user-search" class="form-label">Search Distributor</label>
                                <input type="text" id="bulk-user-search" class="form-control" placeholder="Type to search users...">
                                <div id="bulk-user-suggestions" class="list-group mt-1" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                                <div id="bulk-selected-user" class="mt-2" style="display: none;">
                                    <span class="badge bg-primary">Selected: <span id="bulk-selected-user-name"></span></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="bulk-clear-selection">Clear</button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirm-bulk-assign">
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                Assign Codes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modal);
        $('#bulk-assign-modal').modal('show');

        // Handle user search in modal
        let bulkSelectedUser = null;
        $('#bulk-user-search').on('input', function() {
            const query = $(this).val().trim();
            if (query.length < 2) {
                $('#bulk-user-suggestions').hide();
                return;
            }

            $.ajax({
                url: '{{ route("admin.referral_codes.search_users") }}',
                method: 'GET',
                data: { q: query },
                success: function(users) {
                    const $suggestions = $('#bulk-user-suggestions');
                    $suggestions.empty();

                    if (users.length > 0) {
                        users.forEach(function(user) {
                            const suggestion = `
                                <a href="#" class="list-group-item list-group-item-action bulk-user-suggestion"
                                   data-id="${user.id}" data-name="${user.name}" data-email="${user.email}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${user.name}</h6>
                                    </div>
                                    <small class="text-muted">${user.email}</small>
                                </a>
                            `;
                            $suggestions.append(suggestion);
                        });
                        $suggestions.show();
                    } else {
                        $suggestions.append('<div class="list-group-item">No users found</div>');
                        $suggestions.show();
                    }
                }
            });
        });

        // Handle user selection in modal
        $(document).on('click', '.bulk-user-suggestion', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');
            const userName = $(this).data('name');
            const userEmail = $(this).data('email');

            bulkSelectedUser = { id: userId, name: userName, email: userEmail };
            $('#bulk-selected-user-name').text(userName + ' (' + userEmail + ')');
            $('#bulk-selected-user').show();
            $('#bulk-user-search').val('');
            $('#bulk-user-suggestions').hide();
        });

        // Clear selection in modal
        $('#bulk-clear-selection').on('click', function() {
            bulkSelectedUser = null;
            $('#bulk-selected-user').hide();
            $('#bulk-user-search').val('');
        });

        // Confirm bulk assign
        $('#confirm-bulk-assign').on('click', function() {
            if (!bulkSelectedUser) {
                showNotification('Please select a distributor.', 'warning');
                return;
            }

            const $btn = $(this);
            const $spinner = $btn.find('.spinner-border');

            $btn.prop('disabled', true);
            $spinner.removeClass('d-none');

            $.ajax({
                url: '{{ route("admin.referral_codes.bulk_assign") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    codes: selectedCodes,
                    distributor_id: bulkSelectedUser.id
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#bulk-assign-modal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showNotification(response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    let message = 'An error occurred while assigning codes.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showNotification(message, 'danger');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');
                }
            });
        });

        // Clean up modal when hidden
        $('#bulk-assign-modal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    });

    // Bulk delete
    $('#bulk-delete').on('click', function() {
        if (selectedCodes.length === 0) {
            showNotification('Please select codes to delete.', 'warning');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selectedCodes.length} codes? This action cannot be undone.`)) {
            return;
        }

        $.ajax({
            url: '{{ route("admin.referral_codes.bulk_delete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                codes: selectedCodes
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(response.message, 'danger');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while deleting codes.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'danger');
            }
        });
    });
});
</script>
@endsection