<x-guest-layout>
    <div class="w-full max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-900 dark:text-gray-100">
                {{ __('Complete Your Profile') }}
            </h2>

            <form method="POST" action="{{ route('onboarding.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Profile Image -->
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label for="profile_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:w-1/3 sm:text-right sm:pr-4 mb-2 sm:mb-0">
                        {{ __('Profile Image') }}
                    </label>
                    <div class="sm:w-2/3">
                        <input id="profile_image" type="file"
                               class="block w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100
                                      dark:file:bg-blue-900 dark:file:text-blue-300"
                               name="profile_image" accept="image/*">
                        @error('profile_image')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:w-1/3 sm:text-right sm:pr-4 mb-2 sm:mb-0">
                        {{ __('Phone Number') }}
                    </label>
                    <div class="sm:w-2/3">
                        <input id="phone" type="text"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300 @error('phone') border-red-500 @enderror"
                               name="phone" value="{{ old('phone') }}" required autocomplete="phone">
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Address -->
                <div class="flex flex-col sm:flex-row sm:items-start">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:w-1/3 sm:text-right sm:pr-4 mb-2 sm:mb-0 sm:pt-2">
                        {{ __('Address') }}
                    </label>
                    <div class="sm:w-2/3">
                        <textarea id="address"
                                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300 @error('address') border-red-500 @enderror"
                                  name="address" rows="3" required autocomplete="address" placeholder="Street address, barangay">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- City -->
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:w-1/3 sm:text-right sm:pr-4 mb-2 sm:mb-0">
                        {{ __('City') }}
                    </label>
                    <div class="sm:w-2/3">
                        <input id="city" type="text"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300 @error('city') border-red-500 @enderror"
                               name="city" value="{{ old('city') }}" required autocomplete="city">
                        @error('city')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Province -->
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label for="province" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:w-1/3 sm:text-right sm:pr-4 mb-2 sm:mb-0">
                        {{ __('Province') }}
                    </label>
                    <div class="sm:w-2/3">
                        <input id="province" type="text"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300 @error('province') border-red-500 @enderror"
                               name="province" value="{{ old('province') }}" required autocomplete="province">
                        @error('province')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Postal Code -->
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:w-1/3 sm:text-right sm:pr-4 mb-2 sm:mb-0">
                        {{ __('Postal Code') }}
                    </label>
                    <div class="sm:w-2/3">
                        <input id="postal_code" type="text"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300 @error('postal_code') border-red-500 @enderror"
                               name="postal_code" value="{{ old('postal_code') }}" autocomplete="postal-code">
                        @error('postal_code')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Shipping Name -->
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label for="shipping_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:w-1/3 sm:text-right sm:pr-4 mb-2 sm:mb-0">
                        {{ __('Full Name (for Shipping)') }}
                    </label>
                    <div class="sm:w-2/3">
                        <input id="shipping_name" type="text"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300 @error('shipping_name') border-red-500 @enderror"
                               name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" required autocomplete="name">
                        @error('shipping_name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Complete Profile & Continue') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>