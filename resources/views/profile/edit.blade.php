@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-xl border-radius-xl">
                <div class="card-header pb-0 px-3">
                    <h6 class="mb-0">Edit Profile</h6>
                </div>
                <div class="card-body pt-4 p-3">
                    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')
                        @include('profile.partials.update-profile-information-form')
                    </form>
                </div>
            </div>

            <div class="card shadow-xl border-radius-xl mt-4">
                <div class="card-header pb-0 px-3">
                    <h6 class="mb-0">Update Password</h6>
                </div>
                <div class="card-body pt-4 p-3">
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')
                        @include('profile.partials.update-password-form')
                    </form>
                </div>
            </div>

            <div class="card shadow-xl border-radius-xl mt-4">
                <div class="card-header pb-0 px-3">
                    <h6 class="mb-0">Delete Account</h6>
                </div>
                <div class="card-body pt-4 p-3">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
