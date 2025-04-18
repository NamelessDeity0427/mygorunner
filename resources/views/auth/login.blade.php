<x-guest-layout>
    <div class="auth-container" data-aos="fade-up">
        <div class="auth-logo">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
            </svg>
        </div>
        <h2 class="auth-title">Sign in to Sulogoon</h2>

        <!-- Session Status -->
        @if (session('status'))
            <div class="auth-session-status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="auth-form-group">
                <label for="email" class="auth-input-label">{{ __('Email') }}</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                @error('email')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="auth-form-group">
                <label for="password" class="auth-input-label">{{ __('Password') }}</label>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" />
                @error('password')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="auth-checkbox-label">
                <input id="remember_me" type="checkbox" class="auth-checkbox" name="remember">
                <span>{{ __('Remember me') }}</span>
            </div>

            <div class="flex items-center justify-between mt-4">
                @if (Route::has('password.request'))
                    <a class="auth-link" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @else
                    <span></span>
                @endif

                <button type="submit" class="auth-button">
                    {{ __('Sign in') }}
                </button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a class="auth-link" href="{{ route('register') }}">Sign up</a></p>
        </div>
    </div>
</x-guest-layout>