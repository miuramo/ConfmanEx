@props([
    'bb_id' => 1,
])
@php
    $bb = App\Models\Bb::find($bb_id);
    $ismeta = $bb->ismeta_myself();
    if ($bb->type == 1 && $ismeta) {
        $revs = $bb->get_reviewers();
        $nameofmeta = App\Models\Setting::findByIdOrName('name_of_meta')->value;
    }
@endphp

<!-- components.review.iammeta 自分がメタのときだけ、査読者 -->
@isset($revs)
    <div class="text-sm p-2 rounded-md bg-pink-200 inline-block">
        <table class="divide-y divide-gray-200 mb-2">
            <thead>
                <tr>
                    <th class="p-1 bg-pink-200 text-pink-400"> （{{$nameofmeta}}のみに表示）担当した査読者 と</th>
                    <th class="p-1 bg-pink-200 text-pink-400">  RevID</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($revs as $rev)
                    <tr
                        class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300 text-slate-200 dark:text-slate-100 hover:text-slate-500' : 'bg-white dark:bg-slate-500 text-slate-50 dark:text-slate-500 hover:text-slate-400' }}">
                        <td class="p-1 text-center ">
                            {{ $rev->name }} ({{ $rev->affil }})
                        </td>
                        <td class="p-1 text-center">
                            {{ $rev->id }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endisset
