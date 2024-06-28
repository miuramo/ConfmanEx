@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

    $cat_paper_count = App\Models\Category::withCount('papers')->get();
@endphp
<!-- components.role.award -->
<div class="px-6 py-4">
    <x-element.h1>
        表彰用JSON →
        @php
            $dkey = App\Models\Setting::findByIdOrName('AWARDJSON_DLKEY','value');
        @endphp
        <x-element.linkbutton href="{{ route('pub.json_booth_title_author', ['key' => $dkey]) }}" color="cyan" target="_blank">
            JSON
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>
        ダウンロードURLは {{ route('pub.json_booth_title_author', ['key' => $dkey]) }} <br>
        ダウンロードキーは {{$dkey}}
    </x-element.h1>


</div>
