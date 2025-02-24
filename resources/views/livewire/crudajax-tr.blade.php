<tr>
    @foreach ($fs as $f=>$type)
        <livewire:crudajax-td :dat="$dat" :field="$f" :type="$type" :id="$dat->id" />
    @endforeach
</tr>
