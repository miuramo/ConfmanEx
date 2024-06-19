<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="mx-5 my-10">
        <div class="p-3 text-4xl">あなたはすでにログインしています。</div>
        <div class="p-3 text-4xl">査読者のかたは、上部メニューの「査読」をおしてください。</div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-slate-800 dark:text-slate-400">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}

                    <x-element.dummylinkbutton> Dummy </x-element.dummylinkbutton>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
