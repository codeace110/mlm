<div class="row">
    <div class="col-md-4 mb-3">
        <label for="current_password" class="form-label">Current Password</label>
        <input type="password" id="current_password" name="current_password"
               class="form-control border px-3" autocomplete="current-password">
        @if($errors->updatePassword && $errors->updatePassword->has('current_password'))
            <div class="text-danger mt-1">{{ $errors->updatePassword->first('current_password') }}</div>
        @endif
    </div>

    <div class="col-md-4 mb-3">
        <label for="password" class="form-label">New Password</label>
        <input type="password" id="password" name="password"
               class="form-control border px-3" autocomplete="new-password">
        @if($errors->updatePassword && $errors->updatePassword->has('password'))
            <div class="text-danger mt-1">{{ $errors->updatePassword->first('password') }}</div>
        @endif
    </div>

    <div class="col-md-4 mb-3">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="form-control border px-3" autocomplete="new-password">
        @if($errors->updatePassword && $errors->updatePassword->has('password_confirmation'))
            <div class="text-danger mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</div>
        @endif
    </div>
</div>

<div class="text-end">
    <button type="submit" class="btn bg-gradient-primary">Update Password</button>
    @if (session('status') === 'password-updated')
        <div class="alert alert-success mt-3">
            Password updated successfully!
        </div>
    @endif
</div>
