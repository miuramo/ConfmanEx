<x-app-layout>
<!-- user.entry -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            <a href="/" title="トップページへのリンク"
                class="font-semibold text-gray-800 hover:text-blue-700 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ env('APP_NAME') }}</a>

            {{ __('投稿者アカウントの作成') }}
        </h2>
    </x-slot>

    <div class="py-2">

        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif

        <div class="py-2 px-6">
            <form action="{{ route('entry') }}" method="post" id="editform">
                @csrf
                @method('post')
                <div class="text-xl py-6 dark:text-gray-400">
                    あなたのメールアドレスを入力して、送信ボタンを押してください。<br>
                    届いたメールを確認し、60分以内に、認証URLをクリックしてください。
                </div>
                <div class="mb-6">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email
                        address</label>
                    <input type="email" id="email" name="email"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg dark:bg-slate-800 dark:text-slate-400 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="your@email.com" required />
                </div>

                <div>
                    <x-element.submitbutton>
                        認証URLをメール送信
                    </x-element.submitbutton>
                </div>
            </form>

        </div>

        <x-element.sankou>
            参考：このあとの流れは、以下のようになります。
            <ol class="list-decimal px-8 pt-4">
                <li> メールで届いた認証URLをクリック</li>
                <li> パスワードの設定</li>
                <li> 氏名と所属の登録</li>
                <li> 新規投稿情報の作成と、確認事項への了承</li>
                <li> 論文PDF等のアップロード、アンケートに回答</li>
            </ol>

            @php
                $tutorial_url = App\Models\Setting::findByIdOrName('TUTORIAL_URL', 'value');
            @endphp
            @isset($tutorial_url)
            <div class="mt-4">
                <x-element.linkbutton :href="$tutorial_url" color="cyan" target="_blank">
                    4. 5. の投稿手順を動画で観る
                </x-element.linkbutton>
            </div>
            @endisset
        </x-element.sankou>
    </div>


</x-app-layout>
