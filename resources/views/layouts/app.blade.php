<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @hasSection('title')
        <title>@yield('title')</title>
    @else
        <title>{{ config('app.name', 'Laravel') }}</title>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ route('file.favicon') }}" type="image/x-icon">

    <!-- 最初に差し込む、個別のcssやフォント -->
    @stack('localcss')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased" x-cloak x-data="{darkMode: $persist(false)}" :class="{'dark': darkMode === true }" >
    @php
        $announce = App\Models\Setting::where("name","ANNOUNCE")->where("valid",true)->first();
    @endphp
    @isset($announce)
    <div class="border border-yellow-800 bg-yellow-200 px-4 py-0 text-center text-yellow-800 font-bold dark:border-yellow-800 dark:bg-yellow-500 ">
        {!!$announce->value!!}</div>
    @endisset
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

        {{-- @auth --}}
            @include('layouts.navigation')
        {{-- @endauth --}}

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <!-- 最後に差し込む、個別のJS -->
    @stack('localjs')

    {{-- <script>
        var darkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        console.log("darkMode:"+darkMode);
    </script> --}}
    @livewireScripts
</body>

</html>
