<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-white" for="email">{{ __('Email') }}</label>
            <x-ui.input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" :error="$errors->has('email')" />
            @if($errors->has('email'))
                <p class="text-xs text-red-300">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <!-- Password -->
        <div class="mt-4 flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-white" for="password">{{ __('Password') }}</label>
            <x-ui.input id="password" type="password" name="password" required autocomplete="new-password" :error="$errors->has('password')" />
            @if($errors->has('password'))
                <p class="text-xs text-red-300">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="mt-4 flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-white" for="password_confirmation">{{ __('Confirm Password') }}</label>
            <x-ui.input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" :error="$errors->has('password_confirmation')" />
            @if($errors->has('password_confirmation'))
                <p class="text-xs text-red-300">{{ $errors->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-ui.button type="submit" variant="secondary">
                {{ __('Reset Password') }}
            </x-ui.button>
        </div>
    </form>
</x-guest-layout>
