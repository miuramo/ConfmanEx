@props([
    'color' => "blue",
])
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-'.$color.'-500 dark:bg-'.$color.'-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-50 dark:text-gray-300 hover:text-gray-800 uppercase tracking-widest shadow-sm hover:bg-'.$color.'-100 dark:hover:bg-'.$color.'-700 focus:outline-none focus:ring-2 focus:ring-'.$color.'-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
