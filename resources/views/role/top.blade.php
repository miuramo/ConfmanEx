<!-- role.top -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ $role->desc }} Toppage ({{ $name }})
            {{-- &nbsp;
            <x-element.linkbutton href="{{ route('file.create') }}" color="cyan">
                Upload New File</x-element.linkbutton>

            <x-element.deletebutton action="{{ route('file.delall') }}" color="red" confirm="全部削除してよいですか？"> Delete All
            </x-element.deletebutton> --}}
            <span class="mx-4"></span>
            @if ($role->name == 'pc')
                <x-element.linkbutton
                    href="https://scrapbox.io/confman/ConfmanEx_PC%E5%A7%94%E5%93%A1%E9%95%B7%E5%90%91%E3%81%91%E3%81%AE%E8%B3%87%E6%96%99"
                    color="cyan" size="sm" target="_blank">
                    マニュアル (Cosense/Scrapbox)</x-element.linkbutton>
            @elseif($role->name == 'pub')
            <x-element.linkbutton
            href="https://scrapbox.io/confman/%E5%87%BA%E7%89%88%E6%8B%85%E5%BD%93%E3%81%AE%E3%81%8B%E3%81%9F%E3%81%B8"
            color="orange" size="sm" target="_blank">
            出版マニュアル (Cosense/Scrapbox)</x-element.linkbutton>

            @endif
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @can('role_any', 'metareviewer|reviewer|pc|pub|award|acc|demo|web|wc|admin')
        @if ($role->name == 'reviewer')
            <x-role.reviewer :role="$role">
            </x-role.reviewer>
        @endif
        @if ($role->name == 'metareviewer')
            <x-role.reviewer :role="$role">
            </x-role.reviewer>
        @endif
        @if ($role->name == 'pc')
            <x-role.pc :role="$role">
            </x-role.pc>
        @endif
        @if ($role->name == 'web')
            <x-role.web :role="$role">
            </x-role.web>
        @endif
        @if ($role->name == 'wc')
            <x-role.pcsub :role="$role">
            </x-role.pcsub>
        @endif
        @if ($role->name == 'pub')
            <x-role.pub :role="$role">
            </x-role.pub>
        @endif
        @if ($role->name == 'award')
            <x-role.award :role="$role">
            </x-role.award>
        @endif
        @if ($role->name == 'acc')
            <x-role.acc :role="$role">
            </x-role.acc>
        @endif
        @if ($role->name == 'demo')
            <x-role.demo :role="$role">
            </x-role.demo>
        @endif
        @if ($role->name == 'admin')
            <x-role.admin :role="$role">
            </x-role.admin>
        @endif
    @endcan


</x-app-layout>
