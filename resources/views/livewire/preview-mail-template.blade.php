<div>
@php
    $to_cc = $target_item->get_mail_to_cc();
@endphp

@if($type==='user')
            <div class="bg-sky-100 px-9 pt-2 pb-6">
<span class="px-4 bg-sky-200 text-sky-600 text-2xl font-bold">
                user {{ sprintf("%03d", $id) }}
</span>
@else
            <div class="bg-pink-100 px-9 pt-2 pb-6">
<span class="px-4 bg-pink-200 text-pink-600 text-2xl font-bold">
               paper {{ sprintf("%03d", $id) }}
</span>
@endif
<br>
            <div class="bg-slate-50 py-2 px-4 font-bold text-sm flex justify-between" id="to">
                To : {{ $to_cc['to'] }}
                <span class="text-slate-400 text-right">To</span>
            </div>
            <div class="bg-slate-100 py-2 px-4 font-bold text-sm flex justify-between" id="cc">
                Cc : {{ implode(' , ', $to_cc['cc']) }}
                @isset($mt->cc)
                    , {{ str_replace(',', ' , ', $mt->cc) }}
                @endisset
                <span class="text-slate-400 text-right">Cc</span>
            </div>
            @isset($mt->bcc)
                <div class="bg-slate-100 py-2 px-4 font-bold text-sm flex justify-between" id="bcc">
                    Bcc :
                    {{ str_replace(',', ' , ', $mt->bcc) }}
                    <span class="text-slate-400 text-right">Bcc</span>
                </div>
            @endisset
            <div class="bg-slate-200 py-2 px-4 font-bold text-xl flex justify-between" id="subject">
                {{ $subject }} <span class="text-slate-400 text-right">subject</span>
            </div>
            <div class="bg-white px-7 py-4 text-gray-700 text-md preview" id="body">
                {!! $markdown !!}
            </div>

        </div>

</div>
