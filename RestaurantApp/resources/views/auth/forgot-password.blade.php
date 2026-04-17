<x-guest-layout>
    <div class="mb-4 text-base font-thin text-white/80">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-white" for="email">{{ __('Email') }}</label>
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autofocus :error="$errors->has('email')" />
            @if($errors->has('email'))
                <p class="text-xs text-red-300">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('login') }}" class="text-sm text-white/70 hover:text-white transition-colors">
                {{ session('status') ? __('Back to login') : __('Cancel') }}
            </a>
            <x-ui.button type="submit" variant="secondary">
                {{ __('Email Password Reset Link') }}
            </x-ui.button>
        </div>
    </form>
</x-guest-layout>
