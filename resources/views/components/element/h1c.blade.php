@props(
    [
        'color' => 'blue',
        'dark' => 100,
        'options' => [],
    ]
)

<!-- components.element.h1c -->
<div class="text-md my-2 p-3 bg-{{$color}}-{{$dark}} rounded-lg  dark:bg-{{$color}}-700 dark:text-slate-200
@foreach($options as $value)
    {{ $value }}
@endforeach
">
    {{ $slot }}
</div>
