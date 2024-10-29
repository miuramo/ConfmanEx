<x-app-layout>
    @php
        $names = [1 => '査読議論', 2 => 'メタと著者の', 3 => '出版担当と著者の'];
        $nameofmeta = App\Models\Setting::findByIdOrName('name_of_meta')->value;
        if ($nameofmeta != null) {
            $names[2] = $nameofmeta . 'と著者の';
        }
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ $names[$bb->type] . __('掲示板') }}

            <span class="mx-2"></span>
            <x-element.paperid size=2 :paper_id="$bb->paper_id">
            </x-element.paperid>
            <span class="mx-2"></span>
            <x-element.category :cat="$bb->category_id">
            </x-element.category>
        </h2>
        <div class="text-lg mt-4 font-bold bg-slate-200 py-2 px-4 inline-block rounded-md">{{ $bb->paper->title }}</div>
    </x-slot>
    @section('title', $bb->paper->id_03d() . ' 掲示板')

    <!-- paper.show -->

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-2 px-6">
        @foreach ($bb->messages as $mes)
            <x-bb.mes :mes="$mes"></x-bb.mes>
        @endforeach

        <div class="text-right mt-1">
            <form action="{{ route('bb.store', ['bb' => $bb->id, 'key' => $bb->key]) }}" method="post" id="post_bbmes"
                onsubmit="return showMessageBeforeSend();">
                @csrf
                @method('post')
                <input type="hidden" name="key" value="{{ $bb->key }}">

                <div
                    class="inline-block w-3/4 bg-green-300 p-2 rounded-md mt-5 hover:bg-green-400 hover:transition-colors">
                    <div class="px-2 text-left text-sm">送信フォーム</div>
                    <input class="w-full p-2 bg-green-200 rounded-md border-green-300 border-2" type="text"
                        size="70" name="sub" id="bbsub" placeholder="ここに Subject (Title) を入力"
                        @isset($revid)
                            value="[RevID : {{ $revid }}]  "
                        @endisset>
                    <textarea class="w-full mt-1 p-2 bg-green-100 rounded-md border-green-300  border-2" name="mes" id="bbmes"
                        cols="70" rows="10" placeholder="ここにメッセージを入力"></textarea>
                    送信すると、関係者にメールで通知されます。<x-element.submitbutton value="submit" color="green" id="bb_submit">了解して送信する
                    </x-element.submitbutton>
                </div>
            </form>

        </div>
        <div class="my-10"></div>

        {{-- bb.type == 1 and メタのみに表示される査読者 --}}
        {{-- <x-review.iammeta :bb_id="$bb->id"></x-review.iammeta> --}}
        @if ($bb->type == 1)
            <x-review.paperscores :paper_id="$bb->paper_id" :cat_id="$bb->category_id" :bb_id="$bb->id"></x-review.paperscores>
        @endif
    </div>
    <script>
        function showMessageBeforeSend() {
            var mes = document.getElementById('bbmes').value;
            var sub = document.getElementById('bbsub').value;
            if (mes == '' || sub == '') {
                alert('Subject と Message は必ず入力してください。');
                return false;
            }
            // 確認ダイアログを表示
            if (!confirm('この内容で送信します。よろしいですか？（送信にすこし時間がかかります）')) {
                return false;
            }
            // submitボタンを無効化
            document.getElementById('bb_submit').disabled = true;
            return true;
        }
    </script>

</x-app-layout>
