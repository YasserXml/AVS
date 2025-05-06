<x-filament-panels::page.simple>
    <x-slot name="title">
        {{ __('Verifikasi Email Anda') }}
    </x-slot>

    <div class="space-y-4">
        <p class="text-center">
            {{ __('Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan kepada Anda? Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan email yang lain.') }}
        </p>

        @if (session('status') === 'verification-link-sent')
            <p class="text-sm text-success-600 dark:text-success-400 text-center">
                {{ __('Link verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.') }}
            </p>
        @endif

        <div class="text-center">
            <x-filament::button
                wire:click="resendNotification"
                wire:loading.attr="disabled"
                class="w-full sm:w-auto"
            >
                {{ __('Kirim Ulang Email Verifikasi') }}
            </x-filament::button>
        </div>

        <div class="text-center">
            <x-filament::link
                href="{{ route('filament.admin.auth.login') }}"
                class="text-primary-600 hover:text-primary-500"
            >
                {{ __('Kembali ke Halaman Login') }}
            </x-filament::link>
        </div>
    </div>
</x-filament-panels::page.simple>