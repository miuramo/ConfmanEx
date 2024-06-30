@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-md text-red-600 dark:text-red-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
