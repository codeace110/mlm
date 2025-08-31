
@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold">Bonus Rules</h2>
    <div class="mb-3">
        <a href="{{ route('admin.bonus_rules.create') }}" class="btn btn-primary">Create New Rule</a>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Rule Name</th>
                        <th>Type</th>
                        <th>Percentage</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rule->name }}</td>
                            <td>{{ $rule->type }}</td>
                            <td>{{ $rule->percentage }}%</td>
                            <td>
                                <a href="{{ route('admin.bonus_rules.edit', $rule->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.bonus_rules.destroy', $rule->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this rule?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No bonus rules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection