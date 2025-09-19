
@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Genealogy Viewer</h2>
        <a href="{{ route('admin.genealogy.index') }}" class="btn btn-primary">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
        </a>
    </div>

    <!-- Search Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Search by name, email, or referral code..."
                           value="{{ $query ?? '' }}">
                    <div id="searchSuggestions" class="list-group mt-1" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                </div>
                <div class="col-md-4">
                    <button type="button" id="searchBtn" class="btn btn-outline-primary me-2">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <button type="button" id="clearBtn" class="btn btn-outline-secondary" style="display: none;">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>
            </div>
            <div id="searchStatus" class="mt-2" style="display: none;">
                <small class="text-muted">Found <span id="resultCount">0</span> results</small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Sponsor</th>
                        <th>Level</th>
                        <th>Total Left Volume</th>
                        <th>Total Right Volume</th>
                        <th>Left Consumed</th>
                        <th>Right Consumed</th>
                        <th>Effective Left</th>
                        <th>Effective Right</th>
                        <th>Level</th>
                        <th>Joined At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="genealogyTableBody">
                    @forelse(isset($users) ? $users : $genealogy as $member)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $member->profile_image ? asset($member->profile_image) : asset('assets/img/team-1.jpg') }}"
                                         class="avatar avatar-sm me-3" alt="Profile">
                                    <div>
                                        <div class="fw-bold">{{ $member->name }}</div>
                                        <small class="text-muted">{{ $member->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $member->sponsor->name ?? 'N/A' }}</td>
                            <td>{{ $member->level ?? 'N/A' }}</td>
                            <td>{{ $member->total_left_volume ?? 0 }}</td>
                            <td>{{ $member->total_right_volume ?? 0 }}</td>
                            <td>{{ $member->left_consumed ?? 0 }}</td>
                            <td>{{ $member->right_consumed ?? 0 }}</td>
                            <td>{{ $member->effective_left ?? 0 }}</td>
                            <td>{{ $member->effective_right ?? 0 }}</td>
                            <td>{{ $member->level_index ?? 1 }}</td>
                            <td>{{ $member->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.genealogy.network', $member->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-diagram-3 me-1"></i>View Network
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="noResultsRow">
                            <td colspan="13" class="text-center">No genealogy data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let searchTimeout;
let currentPage = 1;
let isLoading = false;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const clearBtn = document.getElementById('clearBtn');
    const searchStatus = document.getElementById('searchStatus');
    const resultCount = document.getElementById('resultCount');

    // Live search with debouncing
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimeout);

        if (query.length === 0) {
            clearBtn.style.display = 'none';
            searchStatus.style.display = 'none';
            return;
        }

        clearBtn.style.display = 'inline-block';

        if (query.length < 2) {
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query, 1);
        }, 500);
    });

    // Search button click
    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.trim();
        if (query.length >= 2) {
            performSearch(query, 1);
        }
    });

    // Clear button click
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        searchStatus.style.display = 'none';
        loadInitialData();
    });

    // Enter key support
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query.length >= 2) {
                performSearch(query, 1);
            }
        }
    });
});

function performSearch(query, page = 1) {
    if (isLoading) return;

    isLoading = true;
    currentPage = page;

    const searchBtn = document.getElementById('searchBtn');
    const spinner = searchBtn.querySelector('.spinner-border');
    const searchStatus = document.getElementById('searchStatus');

    // Show loading state
    spinner.classList.remove('d-none');
    searchBtn.disabled = true;

    fetch(`{{ route('admin.genealogy.ajax_search') }}?q=${encodeURIComponent(query)}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateTable(data.users);
                updatePagination(data.pagination);
                updateSearchStatus(data.pagination.total);

                searchStatus.style.display = 'block';
            } else {
                showError('Search failed. Please try again.');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showError('Network error occurred. Please try again.');
        })
        .finally(() => {
            // Hide loading state
            spinner.classList.add('d-none');
            searchBtn.disabled = false;
            isLoading = false;
        });
}

function updateTable(users) {
    const tbody = document.getElementById('genealogyTableBody');

    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="13" class="text-center">
                    <div class="py-4">
                        <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No users found matching your search.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    users.forEach((user, index) => {
        const rowNumber = (currentPage - 1) * 20 + index + 1;
        html += `
            <tr>
                <td>${rowNumber}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${user.profile_image ? '{{ asset('') }}' + user.profile_image : '{{ asset('assets/img/team-1.jpg') }}'}"
                             class="avatar avatar-sm me-3" alt="Profile">
                        <div>
                            <div class="fw-bold">${user.name}</div>
                            <small class="text-muted">${user.email}</small>
                        </div>
                    </div>
                </td>
                <td>${user.sponsor_name}</td>
                <td>${user.level}</td>
                <td>${user.total_left_volume}</td>
                <td>${user.total_right_volume}</td>
                <td>${user.left_consumed}</td>
                <td>${user.right_consumed}</td>
                <td>${user.effective_left}</td>
                <td>${user.effective_right}</td>
                <td>${user.level_index}</td>
                <td>${user.created_at}</td>
                <td>
                    <a href="{{ route('admin.genealogy.network', '') }}/${user.id}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-diagram-3 me-1"></i>View Network
                    </a>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function updatePagination(pagination) {
    // Remove existing pagination
    const existingPagination = document.querySelector('.card-footer');
    if (existingPagination) {
        existingPagination.remove();
    }

    if (pagination.last_page > 1) {
        const card = document.querySelector('.card');
        const paginationHtml = `
            <div class="card-footer">
                <nav aria-label="Genealogy pagination">
                    <ul class="pagination justify-content-center mb-0">
                        ${pagination.current_page > 1 ?
                            `<li class="page-item">
                                <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1})">Previous</a>
                            </li>` : ''
                        }

                        ${generatePageLinks(pagination)}

                        ${pagination.current_page < pagination.last_page ?
                            `<li class="page-item">
                                <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1})">Next</a>
                            </li>` : ''
                        }
                    </ul>
                </nav>
            </div>
        `;

        card.insertAdjacentHTML('beforeend', paginationHtml);
    }
}

function generatePageLinks(pagination) {
    let links = '';

    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
        links += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }

    return links;
}

function changePage(page) {
    const query = document.getElementById('searchInput').value.trim();
    if (query.length >= 2) {
        performSearch(query, page);
    }
}

function updateSearchStatus(count) {
    document.getElementById('resultCount').textContent = count;
}

function loadInitialData() {
    // Load initial genealogy data via AJAX
    fetch('{{ route('admin.genealogy.ajax_search') }}?page=1')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateTable(data.users);
                updatePagination(data.pagination);
            }
        })
        .catch(error => console.error('Error loading initial data:', error));
}

function showError(message) {
    const tbody = document.getElementById('genealogyTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="13" class="text-center text-danger">
                <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                <p class="mt-2">${message}</p>
            </td>
        </tr>
    `;
}
</script>
@endsection