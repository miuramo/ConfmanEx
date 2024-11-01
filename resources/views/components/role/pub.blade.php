@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

    $cat_paper_count = App\Models\Category::withCount('papers')->get();
@endphp
<!-- components.role.pub -->
<style>
    .hidden-content {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
</style>

<div class="px-6 py-4">
    <x-element.h1>
        <x-element.linkbutton href="{{ route('pub.addsubmit') }}" color="cyan" target="_blank">
            別カテゴリでの採否を追加する
        </x-element.linkbutton>
    </x-element.h1>
    
    <x-element.h1>
        セッション割り当て
        @foreach ($cats as $cid => $cname)
            <span class="px-2"></span>
            <x-element.linkbutton href="{{ route('pub.booth', ['cat' => $cid]) }}" color="cyan" target="_blank">
                {{ $cname }}
            </x-element.linkbutton>
        @endforeach
    </x-element.h1>

    <x-element.h1>
        書誌情報を、投稿者が修正できないように設定する
        <span class="mx-2"></span>
        <x-element.linkbutton href="{{ route('paper.adminlock') }}" color="green">
            書誌情報の管理
        </x-element.linkbutton> <span class="text-sm mx-2 mr-10">書誌情報（タイトル、著者名と所属、概要など）の編集権限をカテゴリ別に設定できる画面が開きます。</span>
    </x-element.h1>


    <x-element.h1>
        書誌情報の確認と修正
        @foreach ($cats as $cid => $cname)
            <span class="px-2"></span>
            <x-element.linkbutton href="{{ route('pub.bibinfochk', ['cat' => $cid]) }}" color="lime" target="_blank">
                {{ $cname }}
            </x-element.linkbutton>
        @endforeach
    </x-element.h1>

    <x-element.h1>
        プログラム出力
        @foreach ($cats as $cid => $cname)
            <span class="px-2"></span>
            <x-element.linkbutton href="{{ route('pub.bibinfo', ['cat' => $cid]) }}" color="teal" target="_blank">
                {{ $cname }}
            </x-element.linkbutton>
        @endforeach

        <span class="px-2"></span>
        <x-element.button id="toggleButton" value="出力をカスタマイズしたい場合" color='cyan' size='sm'
            onclick="openclose('awardjson')">
        </x-element.button>

        <div class="hidden-content bg-slate-300 p-2 mt-2 dark:text-gray-600" id="awardjson" style="display:none;">
            書誌情報JSON
            @php
                $dkey = App\Models\Setting::findByIdOrName('AWARDJSON_DLKEY', 'value');
            @endphp
            <x-element.linkbutton href="{{ route('pub.json_booth_title_author', ['key' => $dkey]) }}" color="cyan"
                size="sm" target="_blank">
                JSON
            </x-element.linkbutton>
            <div class="text-sm">
                ダウンロードURLは {{ route('pub.json_booth_title_author', ['key' => $dkey]) }} <br>
                ダウンロードキーは {{ $dkey }}<br>
                以下のようなプログラムを作成して、出力をカスタマイズしてください。
            </div>
            <textarea name="custom_program" cols="90" rows="5">
&lt;?php
  $url = "{{ route('pub.json_booth_title_author', ['key' => $dkey]) }}" ;
  $json = file_get_contents($url) ;
  $ary = json_decode($json, true) ;
  # var_dump($ary) ;
  foreach($ary as $booth=&gt;$bib){
    echo $booth ;
    echo " " ;
    echo $bib['title'] ;
    echo "\n" ;
    foreach($bib['authors'] as $n=&gt;$u){
      echo "    " ;
      echo $u ;
      echo " " ;
      echo "(".$bib['affils'][$n].")" ;
      echo "\n" ;
    }
  }</textarea>
        </div>

    </x-element.h1>


    <x-element.h1>採択論文・ファイルのタイムスタンプ
        @foreach ($cats as $cid => $cname)
        <span class="mx-2"></span>
        <x-element.linkbutton2 href="{{ route('pub.fileinfochk', ['cat' => $cid]) }}" color="lime" target="_blank">
            {{ $cname }}
        </x-element.linkbutton2>
        @endforeach
    </x-element.h1>

    
    <x-element.h1>採択論文・ファイルダウンロード</x-element.h1>

    <div class="px-6 py-0 flex">
        <div class="px-2 py-0 flex-grow">
            @php
                $fts = ['pdf', 'img', 'video', 'altpdf'];
            @endphp
            <form action="{{ route('pub.zipdownload') }}" method="post" id="pub_zipdownload">
                @csrf
                @method('post')
                <div>
                    @foreach ($cats as $catid => $catname)
                        <input type="radio" name="targetcat" value="{{ $catid }}"
                            id="label{{ $catid }}" @if ($catid == 1) checked="checked" @endif>
                        <label for="label{{ $catid }}"
                            class="dark:text-gray-300">{{ $catname }}</label>&nbsp;
                    @endforeach
                </div>
                <div>
                    @foreach ($fts as $ft)
                        <input type="checkbox" name="filetype{{ $ft }}" value="{{ $ft }}"
                            id="label{{ $ft }}" @if ($ft == 'pdf') checked="checked" @endif>
                        <label for="label{{ $ft }}"
                            class="dark:text-gray-300">{{ $ft }}</label>&nbsp;
                    @endforeach
                </div>
                <div class="dark:text-gray-400">
                    ファイル名は、Prefix→ <input type="text" name="fn_prefix"
                        value="{{ env('PUB_DL_PREFIX', 'IPSJ-SSS2024') }}" class="p-1 dark:bg-slate-600"> +
                    [ブース記番].pdf になります。ファイル名が重複するため、pdf と altpdf は同時に選択しないでください。
                </div>

                <x-element.submitbutton value="view" color="yellow">↑選択したカテゴリ・種別の採択ファイルをDownload
                </x-element.submitbutton>
            </form>

        </div>
    </div>

    <x-element.h1>メール送信
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('mt.index') }}" color="pink">
            メール雛形
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>自分の権限確認（Role一覧）</x-element.h1>
    @php
        $user = App\Models\User::find(auth()->id());
    @endphp
    @foreach ($user->roles as $ro)
        <span class="inline-block bg-slate-300 rounded-md p-1 mb-0.5 dark:bg-slate-500 dark:text-gray-300">{{ $ro->desc }} ({{ $ro->name }})</span>
    @endforeach

    <x-element.h1> <x-element.linkbutton href="{{ route('admin.hiroba_excel') }}" color="teal">
            情報学広場登録用Excel Download
        </x-element.linkbutton>
    </x-element.h1>


</div>
@push('localjs')
    <script src="/js/jquery.min.js"></script>
    <script src="/js/openclose.js"></script>
@endpush
