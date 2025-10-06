@extends('layouts.admin')

@section('title', 'Admin Code Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6>Admin Code: {{ $code->code }}</h6>
                        <a href="{{ route('admin.admin_codes.index') }}" class="btn btn-primary btn-sm ms-auto">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Code Information</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Code:</dt>
                                        <dd class="col-sm-8">
                                            <span class="font-weight-bold text-primary h5">{{ $code->code }}</span>
                                            @if($code->batch_name)
                                                <br><small class="text-muted">Batch: {{ $code->batch_name }}</small>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Status:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-lg bg-gradient-{{ $code->status == 'issued' ? 'success' : ($code->status == 'unused' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($code->status) }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Created:</dt>
                                        <dd class="col-sm-8">{{ $code->created_at->format('M d, Y H:i:s') }}</dd>

                                        <dt class="col-sm-4">Updated:</dt>
                                        <dd class="col-sm-8">{{ $code->updated_at->format('M d, Y H:i:s') }}</dd>

                                        @if($code->batch_id)
                                        <dt class="col-sm-4">Batch ID:</dt>
                                        <dd class="col-sm-8">
                                            <code>{{ $code->batch_id }}</code>
                                        </dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Assignment & Usage</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Assigned To:</dt>
                                        <dd class="col-sm-8">
                                            @if($code->distributor)
                                                {{ $code->distributor->name }} ({{ $code->distributor->email }})
                                            @else
                                                Not Assigned
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Used By:</dt>
                                        <dd class="col-sm-8">
                                            @if($code->usedByUser)
                                                {{ $code->usedByUser->name }} ({{ $code->usedByUser->email }})
                                            @else
                                                Not Used
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Used At:</dt>
                                        <dd class="col-sm-8">
                                            @if($code->used_at)
                                                {{ $code->used_at->format('Y-m-d H:i:s') }}
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection