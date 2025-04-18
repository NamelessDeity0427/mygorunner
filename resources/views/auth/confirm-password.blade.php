<x-guest-layout>
    <div class="auth-container" data-aos="fade-up">
        <div class="auth-logo">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
            </svg>
        </div>
        <h2 class="auth-title">Confirm your password</h2>
        <div class="text-sm text-gray-600 mb-4">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div class="auth-form-group">
                <label for="password" class="auth-input-label">{{ __('Password') }}</label>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" />
                @error('password')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="auth-button">
                {{ __('Confirm') }}
            </button>
        </form>

        <div class="auth-footer">
            <p>Need help? <a class="auth-link" href="{{ route('login') }}">Sign in again</a></p>
        </div>
    </div>
</x-guest-layout>