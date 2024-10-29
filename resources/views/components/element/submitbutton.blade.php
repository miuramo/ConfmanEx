@props([
    'name' => 'action',
    'value' => '',
    'color' => 'blue',
    'id' => 'id_submit_button',
])
@php
    $id = ($id == 'id_submit_button') ? $id.'_'.$value : $id;
@endphp
<!-- components.element.submitbutton -->
<button
        type="submit" name="{{ $name }}" value="{{ $value }}" id = "{{ $id }}" 
        class="inline-flex justify-center py-1 px-2 mb-0.5 border border-transparent shadow-sm text-md font-medium
         rounded-md text-white bg-{{$color}}-500 hover:bg-{{$color}}-600 focus:outline-none
         focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500
         dark:bg-{{$color}}-700 dark:hover:bg-{{$color}}-500 dark:hover:text-{{$color}}-700" >
    {{ $slot }}
</button>
