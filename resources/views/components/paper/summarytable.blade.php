@props([])
<!-- components.paper.authorlist -->
@php
    $cat_paper_count = App\Models\Category::withCount('papers')->get();
    // PDFファイルがある投稿の数
    $count_paper_haspdf = App\Models\Paper::select(DB::raw('count(id) as count, category_id'))
        ->whereNotNull('pdf_file_id')
        ->whereNot('pdf_file_id', 0) // 一度PDFをアップして、あとで消すとnullではなく0になることがあった。現在は修正済み
        ->groupBy('category_id')
        ->get()
        ->pluck('count', 'category_id');

@endphp
<table class="w-32 divide-y divide-gray-400 flex-grow  dark:text-gray-300">
    <thead>
        <tr>
            <th class="px-2">Category</th>
            <th class="px-2">Papers</th>
            <th class="px-2">withPDF</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cat_paper_count as $cpc)
            <tr>
                <td class="px-2 text-center">{{ $cpc->name }}</td>
                <td class="px-2 text-right">{{ $cpc->papers_count }}</td>
                @isset($count_paper_haspdf[$cpc->id])
                    <td class="px-2 text-right">{{ $count_paper_haspdf[$cpc->id] }}</td>
                @else
                    <td class="px-2 text-right">0</td>
                @endisset
            </tr>
        @endforeach
    </tbody>
</table>
