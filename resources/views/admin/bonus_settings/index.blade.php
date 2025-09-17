@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Bonus Settings</h6>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <form method="POST" action="{{ route('admin.bonus_settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row px-4 py-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="package_value" class="form-control-label">Package Value (₱)</label>
                                    <input type="number" step="0.01" class="form-control @error('package_value') is-invalid @enderror"
                                           name="package_value" id="package_value"
                                           value="{{ old('package_value', $settings->package_value ?? 1200.00) }}" required>
                                    @error('package_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="direct_bonus_percent" class="form-control-label">Direct Referral Bonus (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('direct_bonus_percent') is-invalid @enderror"
                                           name="direct_bonus_percent" id="direct_bonus_percent"
                                           value="{{ old('direct_bonus_percent', $settings->direct_bonus_percent ?? 100.00) }}" required>
                                    @error('direct_bonus_percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row px-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pair_bonus_amount" class="form-control-label">Pair Bonus Amount (₱)</label>
                                    <input type="number" step="0.01" class="form-control @error('pair_bonus_amount') is-invalid @enderror"
                                           name="pair_bonus_amount" id="pair_bonus_amount"
                                           value="{{ old('pair_bonus_amount', $settings->pair_bonus_amount ?? 240.00) }}" required>
                                    @error('pair_bonus_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="balancer_ratio" class="form-control-label">Balancer Ratio</label>
                                    <select class="form-control @error('balancer_ratio') is-invalid @enderror"
                                            name="balancer_ratio" id="balancer_ratio" required>
                                        <option value="1:1" {{ old('balancer_ratio', $settings->balancer_ratio ?? '1:1') == '1:1' ? 'selected' : '' }}>1:1</option>
                                        <option value="2:1" {{ old('balancer_ratio', $settings->balancer_ratio ?? '1:1') == '2:1' ? 'selected' : '' }}>2:1</option>
                                        <option value="3:1" {{ old('balancer_ratio', $settings->balancer_ratio ?? '1:1') == '3:1' ? 'selected' : '' }}>3:1</option>
                                    </select>
                                    @error('balancer_ratio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row px-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="matching_bonus_percent" class="form-control-label">Matching Bonus (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('matching_bonus_percent') is-invalid @enderror"
                                           name="matching_bonus_percent" id="matching_bonus_percent"
                                           value="{{ old('matching_bonus_percent', $settings->matching_bonus_percent ?? 20.00) }}" required>
                                    @error('matching_bonus_percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row px-4 py-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Update Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection