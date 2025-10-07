@extends('layouts.admin')

@section('title', 'Generate Admin Code Batch')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6>Generate Admin Code Batch</h6>
                        <a href="{{ route('admin.admin_codes.index') }}" class="btn btn-secondary btn-sm ms-auto">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.admin_codes.generate') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="batch_name" class="form-control-label">Batch Name *</label>
                                    <input class="form-control" type="text" id="batch_name" name="batch_name"
                                           placeholder="e.g., January 2024 Batch, Premium Codes, etc."
                                           value="{{ old('batch_name') }}" required>
                                    <small class="form-text text-muted">A descriptive name for this batch of codes</small>
                                    @error('batch_name')
                                        <div class="text-danger text-sm">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="count" class="form-control-label">Number of Codes *</label>
                                    <input class="form-control" type="number" id="count" name="count"
                                           min="15" max="1000" value="50"
                                           value="{{ old('count') }}" required>
                                    <small class="form-text text-muted">Minimum 15 codes per batch</small>
                                    @error('count')
                                        <div class="text-danger text-sm">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <div class="d-flex">
                                <div class="alert-icon">
                                    <i class="ni ni-bulb-61"></i>
                                </div>
                                <div class="alert-text">
                                    <strong>Batch Information:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>All codes will be 8 characters long and unique</li>
                                        <li>Codes are automatically converted to uppercase</li>
                                        <li>Each batch gets a unique UUID for tracking</li>
                                        <li>Generated codes start as "available" status</li>
                                        <li>You can assign codes to distributors after generation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.admin_codes.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Generate {{ old('count', 50) }} unique admin codes?')">
                                <i class="ni ni-fat-add"></i> Generate Batch
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Code Preview -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Code Format Preview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <strong class="text-primary">ABC12345</strong>
                                <br><small class="text-muted">8 characters</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <strong class="text-success">XYZ99999</strong>
                                <br><small class="text-muted">Uppercase only</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <strong class="text-info">A7B2C9D1</strong>
                                <br><small class="text-muted">Unique & random</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-update preview count
document.getElementById('count').addEventListener('input', function() {
    const count = this.value;
    if (count < 15) {
        this.setCustomValidity('Minimum 15 codes required');
    } else if (count > 1000) {
        this.setCustomValidity('Maximum 1000 codes allowed');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endsection