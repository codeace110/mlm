@extends('layouts.admin')

@section('title', 'Edit Referral Code')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Referral Code</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.referral_codes.update', $referralCode) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $referralCode->code) }}" required>
                            @error('code')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="available" {{ $referralCode->status === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="assigned" {{ $referralCode->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="used" {{ $referralCode->status === 'used' ? 'selected' : '' }}>Used</option>
                                <option value="expired" {{ $referralCode->status === 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                            @error('status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Expires At (Optional)</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at" value="{{ old('expires_at', $referralCode->expires_at ? $referralCode->expires_at->format('Y-m-d\TH:i') : '') }}">
                            @error('expires_at')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.referral_codes.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Code</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection