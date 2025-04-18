<x-guest-layout>
    <div class="auth-container" data-aos="fade-up">
        <div class="auth-logo">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
            </svg>
        </div>
        <h2 class="auth-title">Create your Sulogoon account</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="auth-form-group">
                <label for="name" class="auth-input-label">{{ __('Name') }}</label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                @error('name')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="auth-form-group">
                <label for="email" class="auth-input-label">{{ __('Email') }}</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                @error('email')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Phone Number -->
            <div class="auth-form-group">
                <label for="phone" class="auth-input-label">{{ __('Phone Number') }}</label>
                <input id="phone" class="auth-input" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" />
                @error('phone')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="auth-form-group">
                <label for="password" class="auth-input-label">{{ __('Password') }}</label>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" />
                @error('password')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="auth-form-group">
                <label for="password_confirmation" class="auth-input-label">{{ __('Confirm Password') }}</label>
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" />
                @error('password_confirmation')
                    <div class="auth-input-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="auth-button">
                {{ __('Create account') }}
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a class="auth-link" href="{{ route('login') }}">Sign in</a></p>
        </div>
    </div>
</x-guest-layout>