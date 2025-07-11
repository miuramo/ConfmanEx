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
        <x-element.linkbutton2 href="{{ route('pub.accstatus') }}" color="cyan" target="_blank">
            採択状況の確認
        </x-element.linkbutton2>
        <span class="mx-2"></span>
        <x-element.linkbutton2 href="{{ route('pub.accstatusgraph') }}" color="cyan" target="_blank" size="xs">
            採択状況のグラフ表示（試験的）
        </x-element.linkbutton2>
        <span class="mx-4"></span>
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
        </x-element.linkbutton> <span
            class="text-sm mx-2 mr-10">書誌情報（タイトル、著者名と所属、概要など）の投稿者による編集可否をカテゴリ別に設定できる画面が開きます。</span>
    </x-element.h1>


    <x-element.h1>
        書誌情報の確認と修正
        @foreach ($cats as $cid => $cname)
            <span class="px-2"></span>
            <x-element.linkbutton href="{{ route('pub.bibinfochk', ['cat' => $cid]) }}" color="lime" target="_blank">
                {{ $cname }}
            </x-element.linkbutton>
        @endforeach
        <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('affil.index') }}" color="purple" target="_blank">
            所属修正ルールの一覧
        </x-element.linkbutton>
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
                $dkey = App\Models\Setting::getval('AWARDJSON_DLKEY');
            @endphp
            <x-element.linkbutton href="{{ route('pub.json_booth_title_author', ['key' => $dkey]) }}" color="cyan"
                size="sm" target="_blank">
                JSON
            </x-element.linkbutton>
            <span class="mx-2"></span>
            <x-element.linkbutton
                href="{{ route('pub.json_booth_title_author', ['key' => $dkey, 'readable' => true]) }}" color="teal"
                size="sm" target="_blank">
                Web確認用
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

    <x-element.h1>
        アンケート
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('enq.index') }}" color="green">
            （出版Roleおよび自分のRoleで参照可能な）アンケート一覧
        </x-element.linkbutton>
    </x-element.h1>


    <x-element.h1>採択論文・ファイルのタイムスタンプ
        @foreach ($cats as $cid => $cname)
            <span class="mx-2"></span>
            <x-element.linkbutton2 href="{{ route('pub.fileinfochk', ['cat' => $cid]) }}" color="lime"
                target="_blank">
                {{ $cname }}
            </x-element.linkbutton2>
        @endforeach
    </x-element.h1>


    <x-element.h1>採択論文・ファイルダウンロード</x-element.h1>
    <x-file.bundle_download />


    <x-element.h1>メール送信
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('mt.index') }}" color="pink">
            メール雛形
        </x-element.linkbutton>

        <span class="mx-10"></span>
        掲示板
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('bb.index_for_pub') }}" color="pink">
            出版掲示板の管理
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>自分の権限確認（Role一覧）</x-element.h1>
    @php
        $user = App\Models\User::find(auth()->id());
    @endphp
    <div class="mx-4">
        @foreach ($user->roles as $ro)
            <span
                class="inline-block bg-slate-300 rounded-md p-1 mb-0.5 dark:bg-slate-500 dark:text-gray-300">{{ $ro->desc }}
                ({{ $ro->name }})
            </span>
        @endforeach
    </div>

    <x-element.h1> <x-element.linkbutton href="{{ route('admin.hiroba_excel') }}" color="teal">
            情報学広場登録用Excel Download
        </x-element.linkbutton>
    </x-element.h1>


</div>
@push('localjs')
    <script src="/js/jquery.min.js"></script>
    <script src="/js/openclose.js"></script>
@endpush
