@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold">Create Bonus Rule</h2>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.bonus_rules.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Rule Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Rule Type</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="direct_referral" {{ old('type') == 'direct_referral' ? 'selected' : '' }}>Direct Referral</option>
                                <option value="level_bonus" {{ old('type') == 'level_bonus' ? 'selected' : '' }}>Level Bonus</option>
                                <option value="matching_bonus" {{ old('type') == 'matching_bonus' ? 'selected' : '' }}>Matching Bonus</option>
                                <option value="leadership_bonus" {{ old('type') == 'leadership_bonus' ? 'selected' : '' }}>Leadership Bonus</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="percentage" class="form-label">Percentage (%)</label>
                            <input type="number" class="form-control @error('percentage') is-invalid @enderror" id="percentage" name="percentage" step="0.01" value="{{ old('percentage') }}" required>
                            @error('percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Rule
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="min_amount" class="form-label">Minimum Amount (₱)</label>
                            <input type="number" class="form-control @error('min_amount') is-invalid @enderror" id="min_amount" name="min_amount" step="0.01" value="{{ old('min_amount') }}">
                            @error('min_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_amount" class="form-label">Maximum Amount (₱)</label>
                            <input type="number" class="form-control @error('max_amount') is-invalid @enderror" id="max_amount" name="max_amount" step="0.01" value="{{ old('max_amount') }}">
                            @error('max_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Create Rule
                    </button>
                    <a href="{{ route('admin.bonus_rules.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection