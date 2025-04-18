<x-guest-layout>
    <div class="auth-container" data-aos="fade-up">
        <div class="auth-logo">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
            </svg>
        </div>
        <h2 class="auth-title">Reset your password</h2>
        <div class="text-sm text-gray-600 mb-4">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="auth-session-status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="auth-form-group">
                <label for="email" class="auth-input-label">{{ __('Email') }}</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                @error('email')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="auth-button">
                {{ __('Email Password Reset Link') }}
            </button>
        </form>

        <div class="auth-footer">
            <p>Remember your password? <a class="auth-link" href="{{ route('login') }}">Sign in</a></p>
        </div>
    </div>
</x-guest-layout>