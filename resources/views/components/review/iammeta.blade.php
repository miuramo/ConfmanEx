@props([
    'bb_id' => 1,
])
@php
    $bb = App\Models\Bb::find($bb_id);
    $ismeta = $bb->ismeta_myself();
    if ($bb->type == 1) {
        if ($ismeta) {
            $revus = $bb->get_reviewers();
            $revuid2rev = $bb->revuid2rev();
        } else {
            $metauser = $bb->metauser();
        }
        $nameofmeta = App\Models\Setting::findByIdOrName('NAME_OF_META')->value;
    }
@endphp

<!-- components.review.iammeta 自分がメタのときだけ、査読者 -->
@isset($revus)
    <div class="text-sm p-2 rounded-md bg-pink-200 inline-block">
        <table class="divide-y divide-gray-200 mb-2">
            <thead>
                <tr>
                    <th class="p-1 bg-pink-200 text-pink-400"> （{{ $nameofmeta }}のみに表示）担当した査読者 と</th>
                    <th class="p-1 bg-pink-200 text-pink-400"> RevID</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($revus as $revu)
                    <tr
                        class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300 text-slate-200 dark:text-slate-100 hover:text-slate-500' : 'bg-white dark:bg-slate-500 text-slate-50 dark:text-slate-500 hover:text-slate-400' }}">
                        <td class="p-1 text-center ">
                            {{ $revu->name }} ({{ $revu->affil }})
                        </td>
                        <td class="p-1 text-center">
                            {{-- {{ $revuid2rev[$revu->id] }} --}}
                            <x-review.pubshow_link :rev_id="$revuid2rev[$revu->id]"></x-review.pubshow_link>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-sm p-2 rounded-md bg-pink-200 inline-block">
        <div class="text-center">
            <span class="text-pink-400">{{ $nameofmeta }}の名前</span>
        </div>
        <div class="text-center">
            {{ $metauser->name }} ({{ $metauser->affil }})
        </div>
    </div>
@endisset
