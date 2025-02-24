<div>
    <input id="id_{{ $modelName}}" type="text" wire:model.live.debounce.500ms="search" wire:keydown.escape="resetSearch" placeholder="Search on {{ $modelName }}" size="40"
        x-init="$el.focus()" />
    <table>
        <thead>
            <tr>
                @foreach ($fs as $f=>$type)
                    <th class="border px-2 bg-slate-200">{{ $f }} <span class="text-xs">{{$type}}</span></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $dat)
                <livewire:crudajax-tr :dat="$dat" :fs="$fs" :key="$dat->id" />
            @endforeach
        </tbody>
    </table>
</div>
