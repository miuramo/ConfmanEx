<div>
    {{ $count_null_lastlogin }}件の過去ログインがあります。
    <input type="button" value="last_login_atの更新 (10件ずつ)" class="bg-blue-200 rounded-md p-2 hover:bg-blue-300" wire:click="get_null_lastlogin_count">
</div>
