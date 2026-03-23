<x-guest-layout>
    <div class="mb-4 text-sm text-white/80">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-white" for="password">{{ __('Password') }}</label>
            <x-ui.input id="password" type="password" name="password" required autocomplete="current-password" :error="$errors->has('password')" />
            @if($errors->has('password'))
                <p class="text-xs text-red-300">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <div class="flex justify-end mt-4">
            <x-ui.button type="submit" variant="secondary">
                {{ __('Confirm') }}
            </x-ui.button>
        </div>
    </form>
</x-guest-layout>
