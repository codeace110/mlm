<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" id="name" name="name" class="form-control border px-3"
               value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        @if($errors->has('name'))
            <div class="text-danger mt-1">{{ $errors->first('name') }}</div>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" class="form-control border px-3"
               value="{{ old('email', $user->email) }}" required autocomplete="username">
        @if($errors->has('email'))
            <div class="text-danger mt-1">{{ $errors->first('email') }}</div>
        @endif
    </div>
</div>

<!-- Profile Image -->
<div class="mb-3">
    <label for="profile_image" class="form-label">Profile Image</label>
    <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*">
    @if($errors->has('profile_image'))
        <div class="text-danger mt-1">{{ $errors->first('profile_image') }}</div>
    @endif
    @if($user->profile_image)
        <div class="mt-2">
            <img src="{{ asset($user->profile_image) }}" alt="Current Profile" class="rounded-circle" width="60" height="60">
            <small class="text-muted">Current profile image</small>
        </div>
    @endif
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" id="phone" name="phone" class="form-control border px-3"
               value="{{ old('phone', $user->phone) }}" autocomplete="phone">
        @if($errors->has('phone'))
            <div class="text-danger mt-1">{{ $errors->first('phone') }}</div>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label for="shipping_name" class="form-label">Full Name (for Shipping)</label>
        <input type="text" id="shipping_name" name="shipping_name" class="form-control border px-3"
               value="{{ old('shipping_name', $user->shipping_name) }}" autocomplete="name">
        @if($errors->has('shipping_name'))
            <div class="text-danger mt-1">{{ $errors->first('shipping_name') }}</div>
        @endif
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <textarea id="address" name="address" rows="3" class="form-control border px-3">{{ old('address', $user->address) }}</textarea>
    @if($errors->has('address'))
        <div class="text-danger mt-1">{{ $errors->first('address') }}</div>
    @endif
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="city" class="form-label">City</label>
        <input type="text" id="city" name="city" class="form-control border px-3"
               value="{{ old('city', $user->city) }}" autocomplete="city">
        @if($errors->has('city'))
            <div class="text-danger mt-1">{{ $errors->first('city') }}</div>
        @endif
    </div>

    <div class="col-md-4 mb-3">
        <label for="province" class="form-label">Province</label>
        <input type="text" id="province" name="province" class="form-control border px-3"
               value="{{ old('province', $user->province) }}" autocomplete="province">
        @if($errors->has('province'))
            <div class="text-danger mt-1">{{ $errors->first('province') }}</div>
        @endif
    </div>

    <div class="col-md-4 mb-3">
        <label for="postal_code" class="form-label">Postal Code</label>
        <input type="text" id="postal_code" name="postal_code" class="form-control border px-3"
               value="{{ old('postal_code', $user->postal_code) }}" autocomplete="postal-code">
        @if($errors->has('postal_code'))
            <div class="text-danger mt-1">{{ $errors->first('postal_code') }}</div>
        @endif
    </div>
</div>

<div class="text-end">
    <button type="submit" class="btn bg-gradient-primary">Save Changes</button>
    @if (session('status') === 'profile-updated')
        <div class="alert alert-success mt-3">
            Profile updated successfully!
        </div>
    @endif
</div>
