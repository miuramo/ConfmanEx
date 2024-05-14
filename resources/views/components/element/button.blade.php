@props([
    'onclick' => '',
    'color' => 'blue',
    'size' => 'md',
    'value' => 'click',
    'confirm' => null,
])
<!-- components.element.button -->
@isset($confirm)
<button onclick="if(confirm('{{ $confirm }}')) {{ $onclick }}; return false;" class="inline-flex justify-center
 py-1 px-2 border border-transparent shadow-sm
 text-{{$size}} font-medium rounded-md text-white bg-{{$color}}-500 hover:bg-{{$color}}-600
 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500
 dark:bg-{{$color}}-500">
    {{$value}}
    </button>
@else
<button onclick="{{ $onclick }};return false;" class="inline-flex justify-center
 py-1 px-2 border border-transparent shadow-sm
  text-{{$size}} font-medium rounded-md text-white bg-{{$color}}-500 hover:bg-{{$color}}-600
   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500
   dark:bg-{{$color}}-600 dark:hover:bg-{{$color}}-700">
{{$value}}
</button>
@endisset
