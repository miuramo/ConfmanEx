@props([
    'confirm' => null,
    'color' => 'gray',
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
<span onclick="return {{ confirm_func($confirm) }}"
class="inline-flex justify-center py-1 px-2 border border-transparent shadow-sm text-{{$size}}
mb-0.5
 font-medium rounded-md text-white bg-{{$color}}-500 
 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500
 dark:bg-{{$color}}-600 dark:text-{{$color}}-200 dark:hover:bg-{{$color}}-300 ">
    {{ $slot }}
</span>
