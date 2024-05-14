<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                {{-- <div class="shrink-0 flex items-center">
                    <a href="/" title="トップページへのリンク" class="text-bold text-2xl hover:text-blue-700">

                    </a>
                </div> --}}

                <div class="hidden space-x-8 sm:-my-px sm:ms-5 sm:flex">
                    <x-nav-link :href="route('guesttop')" :active="request()->routeIs('guesttop')" size="2xl" weight="bold">
                        {{ env('APP_NAME', 'ConfmanEx') }}
                    </x-nav-link>
                </div>

                @auth
                    @php
                        $navs_href = ['新規投稿' => route('paper.create'), '投稿一覧' => route('paper.index')];
                        $navs_active = [
                            '新規投稿' => request()->routeIs('paper.create'),
                            '投稿一覧' => request()->routeIs('paper.index'),
                        ];
                    @endphp
                    @can('role', 'reviewer')
                        @php
                            $navs_href['査読'] = route('role.top', ['role' => 'reviewer']);
                            $navs_active['査読'] = request()->routeIs('role.top', ['role' => 'reviewer']);
                        @endphp
                    @endcan
                    @can('role', 'pc')
                        @php
                            $navs_href['PC'] = route('role.pc', ['role' => 'pc']);
                            $navs_active['PC'] = request()->routeIs('role.pc', ['role' => 'pc']);
                        @endphp
                    @endcan
                    @can('role', 'pub')
                        @php
                            $navs_href['出版'] = route('role.pub', ['role' => 'pub']);
                            $navs_active['出版'] = request()->routeIs('role.pub', ['role' => 'pub']);
                        @endphp
                    @endcan
                    @can('role', 'acc')
                        @php
                            $navs_href['会計'] = route('role.top', ['role' => 'acc']);
                            $navs_active['会計'] = request()->routeIs('role.top', ['role' => 'acc']);
                        @endphp
                    @endcan
                    @can('admin')
                        @php
                            $navs_href['管理'] = route('admin.dashboard');
                            $navs_active['管理'] = request()->routeIs('admin.dashboard');
                        @endphp
                    @endcan
                    <!-- Navigation Links -->
                    @foreach ($navs_href as $label => $href)
                        <div class="hidden space-x-8 sm:-my-px sm:ms-5 sm:flex">
                            <x-nav-link :href="$href" :active="$navs_active[$label]">
                                {{ __($label) }}
                            </x-nav-link>
                        </div>
                    @endforeach
                @else
                @endauth


            </div>
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex h-16" align="right">
                        <div class="hidden space-x-8 sm:-my-px sm:ms-5 sm:flex">
                            <x-nav-link :href="route('entry0')" :active="request()->routeIs('entry0')">
                                {{ __('投稿者アカウントの作成') }}
                            </x-nav-link>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ms-5 sm:flex">
                            <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                                {{ __('Log In') }}
                            </x-nav-link>
                        </div>
                        {{-- <div class="hidden space-x-8 sm:-my-px sm:ms-5 sm:flex"> --}}
                        {{-- ダークモード切り替え --}}
                        {{-- </div> --}}
                    </div>


                @endauth
                <x-theme-toggle />
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>


    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @foreach ($navs_href as $label => $href)
                    <x-responsive-nav-link :href="$href" :active="$navs_active[$label]">
                        {{ __($label) }}
                    </x-responsive-nav-link>
                @endforeach
            @else
                <x-responsive-nav-link :href="route('entry0')" :active="request()->routeIs('entry0')">
                    {{ __('投稿者アカウントの作成') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')">
                    {{ __('Log In') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
