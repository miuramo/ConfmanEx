@props([
    'href' => '',
    'confirm' => null,
    'custom_confirm' => null,
    'color' => 'blue',
    'target' => '_self',
    'size' => 'md',
])
@php
// custom_confirmが無いと、うまくいかない。
    if (!function_exists('confirm_func2')){
        function confirm_func2($conf){
            if ($conf == null) return "return true";
            else if (isset($custom_confirm) && $custom_confirm != null) return "if (typeof {$custom_confirm} !== 'undefined') return {$custom_confirm};";
            else "return confirm('{$conf}');";
        }
    }
@endphp
<!-- components.element.linkbutton -->
<a href="{!! $href !!}" target="{{ $target}}" onclick="{{ confirm_func2($confirm) }}"
class="inline-flex justify-center py-1 px-2 border border-transparent shadow-sm text-{{$size}}
mb-0.5
 font-medium rounded-md text-white bg-{{$color}}-500 hover:bg-{{$color}}-700
 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500
 dark:bg-{{$color}}-600 dark:text-{{$color}}-200 dark:hover:bg-{{$color}}-300 dark:hover:text-{{$color}}-700">
    {{ $slot }}
</a>
