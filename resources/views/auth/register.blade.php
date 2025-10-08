<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
         
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

       <!-- Registration Code (required) -->
       <div class="mt-4">
           <x-input-label for="registration_code" :value="__('Referral Code')" />
           <small class="text-gray-600">Enter the referral code provided by your sponsor (Format: AKEN + 15 characters)</small>

           <x-text-input id="registration_code" class="block mt-1 w-full"
                           type="text"
                           name="registration_code"
                           :value="old('registration_code')"
                           required
                           autocomplete="registration_code"
                           placeholder="AKEN1A2B3C4D5E6F7G8H9I" />

           <x-input-error :messages="$errors->get('registration_code')" class="mt-2" />
       </div>

        <!-- Preferred Side (optional) -->
        <div class="mt-4">
            <x-input-label for="preferred_side" :value="__('Preferred Side (Optional)')" />

            <select id="preferred_side" name="preferred_side" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">No preference</option>
                <option value="left" {{ old('preferred_side') === 'left' ? 'selected' : '' }}>Left</option>
                <option value="right" {{ old('preferred_side') === 'right' ? 'selected' : '' }}>Right</option>
            </select>

            <x-input-error :messages="$errors->get('preferred_side')" class="mt-2" />
        </div>


        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
