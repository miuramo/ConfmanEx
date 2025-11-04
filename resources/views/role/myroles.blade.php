<!-- role.revassign -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            Role確認と招待
        </h2>
    </x-slot>
    @section('title', "Role確認と招待")

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach ($roles as $role)
                @if ($role->users->count() > 12) @continue @endif
                <livewire:role-check :roleobj="$role" />
            @endforeach
        </div>
    </div>


    {{-- @push('localjs')
    @endpush --}}


</x-app-layout>
