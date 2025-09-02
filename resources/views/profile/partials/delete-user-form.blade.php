<div class="alert alert-warning">
    <h5>⚠️ Danger Zone</h5>
    <p class="mb-3">Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        Delete Account
    </button>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Are you sure you want to delete your account?</strong>
                    </div>
                    <p>Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Password</label>
                        <input type="password" id="delete_password" name="password"
                               class="form-control" placeholder="Enter your password" required>
                        @if($errors->userDeletion && $errors->userDeletion->has('password'))
                            <div class="text-danger mt-1">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
