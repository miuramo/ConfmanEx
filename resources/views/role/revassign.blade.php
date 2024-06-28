<!-- role.revassign -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            査読割り当て <span class="mx-2"></span>
            {{ $cat->name }} {{ count($papers) }}件 → {{ $role->desc }}
        </h2>
    </x-slot>
    @section('title', "査読割当 {$cat->name}")

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $roles = App\Models\Role::where('name', 'like', '%reviewer')->get();

                $nameofmeta = App\Models\Setting::findByIdOrName("NAME_OF_META","value");
            @endphp
            @foreach ($roles as $role)
                @if ($role->users->count() > 1)
                    @foreach ($cats as $catid => $catname)
                        <x-element.linkbutton href="{{ route('role.revassign', ['cat' => $catid, 'role' => $role]) }}"
                            color="lime">
                            {{ $catname }}→{{ $role->desc }}
                        </x-element.linkbutton>
                        <span class="mx-2"></span>
                    @endforeach
                @endif
            @endforeach

        </div>
    </div>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-role.revmap :reviewers="$reviewers" :papers="$papers">
            </x-role.revmap>

        </div>
    </div>

    <div class="tooltip"
        style="font-size:10px; border: 1px solid #faa; background-color: #ffa; position: absolute; padding: 2px; width: 200px; height: 70px; top: -100px; opacity: 0.9;">
        hoge</div>

    <div class="saihim"
        style="font-size:14px; border: 3px solid #ccc; background-color: #eee; position: absolute; padding: 2px; width: 140px; height: 112px; top: -960px; opacity: 0.9; text-align: center;">
        <div class="saihi" id="saihimenu_cancel">（Close Menu）</div>
        <div class="saihi" id="saihimenu_99">---------------</div>
        <div class="saihi" id="saihimenu_1">一般査読者にする</div>
        <div class="saihi" id="saihimenu_2">{{$nameofmeta}}にする</div>
        <div class="saihi" id="saihimenu_0">割り当て解除</div>
    </div>

    <form action="{{ route('role.revassignpost', ['role' => $role, 'cat' => $cat]) }}" method="post" id="revass">
        @csrf
        @method('post')
        <input type="hidden" name="paper_id" id="revass_paper_id">
        <input type="hidden" name="user_id" id="revass_user_id">
        <input type="hidden" name="status" id="revass_status">
    </form>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/rev_ass.js"></script>
    @endpush


</x-app-layout>
