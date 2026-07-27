<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-gray-700" for="update_password_current_password">{{ __('Current Password') }}</label>
            <x-ui.input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" :error="$errors->updatePassword->has('current_password')" />
            @if($errors->updatePassword->has('current_password'))
                <p class="text-xs text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-gray-700" for="update_password_password">{{ __('New Password') }}</label>
            <x-ui.input id="update_password_password" name="password" type="password" autocomplete="new-password" :error="$errors->updatePassword->has('password')" />
            @if($errors->updatePassword->has('password'))
                <p class="text-xs text-red-600">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-gray-700" for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
            <x-ui.input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" :error="$errors->updatePassword->has('password_confirmation')" />
            @if($errors->updatePassword->has('password_confirmation'))
                <p class="text-xs text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-500"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
