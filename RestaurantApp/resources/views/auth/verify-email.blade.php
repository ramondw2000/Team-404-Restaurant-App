<x-guest-layout>
    <div class="mb-4 text-sm text-white/80">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-300">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-ui.button type="submit" variant="secondary">
                    {{ __('Resend Verification Email') }}
                </x-ui.button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-ui.button type="submit" variant="ghost" class="text-white/70 hover:text-white hover:bg-white/10">
                {{ __('Log Out') }}
            </x-ui.button>
        </form>
    </div>
</x-guest-layout>
