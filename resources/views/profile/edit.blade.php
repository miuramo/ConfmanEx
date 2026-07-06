<x-app-layout>
    <!-- profile.edit -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg dark:bg-slate-800 dark:text-slate-400">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg dark:bg-slate-800 dark:text-slate-400">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <script type="module" src="/js/webauthn.js"></script>
 
            <div id="passkeys-section" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Passkeys') }}
                </h2>

                {{-- 登録済みパスキー一覧 --}}
                @if ($passkeys->isNotEmpty())
                    <div class="max-w-xl mt-4 space-y-2" id="passkey-list">
                        @foreach ($passkeys as $passkey)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3"
                                 id="passkey-row-{{ $passkey->id }}">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $passkey->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if ($passkey->authenticator)
                                            {{ $passkey->authenticator }} &middot;
                                        @endif
                                        {{ __('Registered') }}: {{ $passkey->created_at->format('Y/m/d') }}
                                        @if ($passkey->last_used_at)
                                            &middot; {{ __('Last used') }}: {{ $passkey->last_used_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                                <button type="button"
                                        onclick="deletePasskey({{ $passkey->id }})"
                                        class="ml-4 text-sm text-red-600 hover:text-red-800">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500" id="passkey-empty">
                        {{ __('No passkeys registered yet.') }}
                    </p>
                @endif

                {{-- 新規登録フォーム --}}
                <div class="max-w-xl mt-6 space-y-3">
                    <h3 class="text-sm font-medium text-gray-700">{{ __('Register New Passkey') }}</h3>
                    <div>
                        <x-input-label for="passkey-name" :value="__('Passkey Name')" />
                        <x-text-input id="passkey-name" type="text" class="mt-1 block w-full"
                            placeholder="{{ __('e.g. MacBook Touch ID') }}" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Enter a name to identify this passkey, such as your device name (e.g. "MacBook Touch ID", "iPhone Face ID").') }}
                        </p>
                    </div>
                    <x-setcolor-button id="passkey-register" color="green"
                        type="button">{{ __('Register Passkey') }}</x-setcolor-button>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg dark:bg-slate-800 dark:text-slate-400">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
