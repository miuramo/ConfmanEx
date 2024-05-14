@props([
    'name' => 'action',
    'value' => '',
    'color' => 'blue',
    'size' => 'md',
])
<!-- components.element.submitbutton -->
<button
        type="submit" name="{{ $name }}" value="{{ $value }}"
        class="inline-flex justify-center py-1 px-2 border border-transparent shadow-sm text-{{$size}} font-medium rounded-md text-{{$color}}-700 bg-{{$color}}-300 hover:bg-{{$color}}-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500"
>
    {{ $slot }}
</button>
