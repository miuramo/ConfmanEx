@props([
    'active' => false,
    'size' => 'md',
    'weight' => 'medium',
    'bgcolor' => null,
])
<!-- nav-link -->
@php
    if (isset($bgcolor)) {
        $classes =
            $active ?? false
                ? "bg-{$bgcolor}-50 inline-flex items-center px-3 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-{$size} font-{$weight} leading-5 text-gray-700 dark:text-gray-400 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                : "bg-{$bgcolor}-50 inline-flex items-center px-3 pt-1 border-b-2 border-transparent text-{$size} font-{$weight} leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out";
    } else {
        $classes =
            $active ?? false
                ? "inline-flex items-center px-3 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-{$size} font-{$weight} leading-5 text-gray-700 dark:text-gray-400 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                : "inline-flex items-center px-3 pt-1 border-b-2 border-transparent text-{$size} font-{$weight} leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out";
    }
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
