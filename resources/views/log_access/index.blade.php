<x-app-layout>
    <!-- paper.index -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('ログ') }}
            @if($user)
                <span class="mx-4"></span>
                {{$users[$user]}}
            @endif
        </h2>
    </x-slot>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    
    <div class="py-2 px-6">
        @foreach ($recentusers as $uid => $uname)
            <a class="text-sm bg-lime-200 p-1 m-1 hover:bg-yellow-200" href="{{ route('logac.index', ['user' => $uid]) }}">
                {{ $uname }}
            </a>
        @endforeach
    </div>

    <div class="py-2 px-6">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-2 py-0.5 text-left text-xs font-medium text-gray-500 tracking-wider">
                        User
                    </th>
                    <th scope="col" class="px-2 py-0.5 text-left text-xs font-medium text-gray-500 tracking-wider">
                        日時
                    </th>
                    <th scope="col" class="px-2 py-0.5 text-left text-xs font-medium text-gray-500 tracking-wider">
                        URL
                    </th>
                    <th scope="col" class="px-2 py-0.5 text-left text-xs font-medium text-gray-500 tracking-wider">
                        mes
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                @foreach ($logs as $log)
                    <tr>
                        <td class="px-2 py-0.5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                            @if(isset($users[$log->uid]))
                                <a class="hover:font-bold hover:text-blue-600" href="/logac/{{$log->uid}}">{{ $users[$log->uid] }}</a>
                            @else
                                {{ $log->uid }}
                            @endif 
                        </td>
                        <td class="px-2 py-0.5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                            {{ $log->created_at }}
                        </td>
                        <td class="px-2 py-0.5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                            {{ substr($log->url,0,50) }}
                        </td>
                        <td class="px-2 py-0.5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                            {{ $log->method }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-app-layout>
