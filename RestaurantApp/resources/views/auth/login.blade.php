<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h1 class="text-4xl text-center text-white font-black mb-8">Employee Login</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-white" for="email">{{ __('Email') }}</label>
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" :error="$errors->has('email')" />
            @if($errors->has('email'))
                <p class="text-xs text-red-300">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <!-- Password -->
        <div class="mt-4 flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-white" for="password">{{ __('Password') }}</label>
            <x-ui.input id="password" type="password" name="password" required autocomplete="current-password" :error="$errors->has('password')" />
            @if($errors->has('password'))
                <p class="text-xs text-red-300">{{ $errors->first('password') }}</p>
            @endif
        </div>


        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-white hover:text-white/80 font-black rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white focus:ring-offset-molveno-blue-300" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-ui.button type="submit" variant="secondary" class="ms-3">
                {{ __('Log in') }}
            </x-ui.button>
        </div>
    </form>
</x-guest-layout>
