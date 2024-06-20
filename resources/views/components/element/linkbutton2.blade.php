@props([
    'href' => '',
    'confirm' => null,
    'color' => 'blue',
    'target' => '_self',
    'size' => 'md',
])
@php
    if (!function_exists('confirm_func')){
        function confirm_func($conf){
            if ($conf == null) return "true";
            else return "confirm('{$conf}');";
        }
    }
@endphp
<!-- components.element.linkbutton -->
<a href="{!! $href !!}" target="{{ $target}}" onclick="return {{ confirm_func($confirm) }}" class="inline-flex justify-center py-1 px-2 mb-0.5
 border border-transparent shadow-sm text-{{$size}} font-medium rounded-md
 text-{{$color}}-700 bg-{{$color}}-300 hover:text-{{$color}}-50 hover:bg-{{$color}}-500
 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500
 dark:bg-{{$color}}-500 dark:text-{{$color}}-200 dark:hover:bg-{{$color}}-300 dark:hover:text-{{$color}}-700">
    {{ $slot }}
</a>
