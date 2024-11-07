@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('採択状況の確認（グラフ表示）') }}
        </h2>
        <div class="mx-2 mt-10">
            <span class="text-gray-400">←弱い 【中心に集める強さ】 強い→</span> 
            <br>
            <input type="range" id="centerForceSlider" min="0.01" max="0.5" step="0.003" value="0.1"> 
        </div>
    </x-slot>

    <style>
        .node {
            stroke: #fff;
            stroke-width: 1.5px;
        }

        .typeA {
            fill: #1f77b4;
        }

        .typeB {
            fill: #ff7f0e;
        }

        .link {
            stroke: #999;
            stroke-opacity: 0.6;
        }

        text {
            font-size: 12px;
            font-family: sans-serif;
            fill: #fff;
            text-anchor: middle;
            alignment-baseline: middle;
        }

        body {
            background-color: #f4f4f9;
        }

        svg {
            background-color: white;
            border-radius: 8px;
            margin: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Tooltipのスタイル */
        .tooltip {
            position: absolute;
            text-align: center;
            padding: 8px;
            font-size: 12px;
            background: #999;
            color: #fff;
            border-radius: 4px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        #centerForceSlider {
            width: 500px;
        }
    </style>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @push('localjs')
        <script src="https://d3js.org/d3.v6.min.js"></script>
        <script src="js/acc_status_graph.js"></script>
    @endpush

    @php
        $nodes_and_links = App\Models\Accept::nodes();
        $nodes = json_encode($nodes_and_links['nodes']);
        $links = json_encode($nodes_and_links['links']);
    @endphp
    <script>
        const nodes = {!! $nodes !!};
        const links = {!! $links !!};
        // ノードデータの定義（番号付き）
        //         const nodes = [
        //     { id: "A1", type: "A", label: "登壇採択" },
        //     { id: "A2", type: "A", label: "デモ採択" },
        //     { id: "B1", type: "B", label: "001" },
        //     { id: "B2", type: "B", label: "002" },
        //     { id: "B3", type: "B", label: "003" },
        //     { id: "B4", type: "B", label: "004" },
        //     { id: "B5", type: "B", label: "005" },
        //     { id: "B6", type: "B", label: "006" },

        //     { id: "C1", type: "B", label: "C1" },
        //     { id: "C2", type: "B", label: "C2" },
        //     { id: "C3", type: "B", label: "C3" },
        //     { id: "A3", type: "A", label: "デモ未定" },

        // ];

        // リンクデータの定義
        // const links = [
        //     { source: "A1", target: "B1" },
        //     { source: "A2", target: "B1" },
        //     { source: "A2", target: "B2" },
        //     { source: "A1", target: "B3" },
        //     { source: "A1", target: "B4" },
        //     { source: "A1", target: "B5" },
        //     { source: "A1", target: "B6" },
        //     { source: "A2", target: "B6" },

        //     { source: "A3", target: "C1" },
        //     { source: "A3", target: "C2" },
        //     { source: "A3", target: "C3" },
        //     { source: "A3", target: "A2" },
        // ];
        const slider = document.getElementById("centerForceSlider");
        slider.addEventListener("input", () => {
            const strength = parseFloat(slider.value);
            simulation.force("x").strength(strength);
            simulation.force("y").strength(strength);
            simulation.alpha(1).restart(); // シミュレーションを再起動して新しい強度で適用
        });
    </script>

</x-app-layout>
