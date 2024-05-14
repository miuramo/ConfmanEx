{{-- <a class="text-white bg-red-500 hover:bg-red-700  focus:ring-red-500">red</a>
<a class="text-white bg-green-500 hover:bg-green-700  focus:ring-green-500">green</a>
<a class="text-white bg-purple-500 hover:bg-purple-700  focus:ring-purple-500">purple</a>
<a class="text-white bg-cyan-500 hover:bg-cyan-700  focus:ring-cyan-500">cyan</a>
<a class="text-white bg-blue-500 hover:bg-blue-700  focus:ring-blue-500">blue</a>
<a class="text-white bg-orange-500 hover:bg-orange-700  focus:ring-orange-500">orange</a>
<a class="text-white bg-yellow-500 hover:bg-yellow-700  focus:ring-yellow-500">yellow</a>
<a class="text-white bg-pink-500 hover:bg-pink-700  focus:ring-pink-500">pink</a>
<a class="text-white bg-lime-500 hover:bg-lime-700  focus:ring-lime-500">lime</a>
<a class="text-white bg-teal-500 hover:bg-teal-700  focus:ring-teal-500">teal</a>
<a class="text-white bg-slate-500 hover:bg-slate-700  focus:ring-slate-500">slate</a>
<a class="text-white bg-gray-500 hover:bg-gray-700  focus:ring-gray-500">gray</a>

<a class="text-red-700 bg-red-300 hover:bg-red-600 hover:text-white  focus:ring-red-300">red</a>
<a class="text-green-700 bg-green-300 hover:bg-green-600 hover:text-white  focus:ring-green-300">green</a>
<a class="text-purple-700 bg-purple-300 hover:bg-purple-600 hover:text-white  focus:ring-purple-300">purple</a>
<a class="text-cyan-700 bg-cyan-300 hover:bg-cyan-600 hover:text-white  focus:ring-cyan-300">cyan</a>
<a class="text-blue-700 bg-blue-300 hover:bg-cyan-600 hover:text-white  focus:ring-blue-300">blue</a>
<a class="text-orange-700 bg-orange-300 hover:bg-orange-600 hover:text-white  focus:ring-orange-300">orange</a>
<a class="text-yellow-700 bg-yellow-300 hover:bg-yellow-600 hover:text-white  focus:ring-yellow-300">yellow</a>
<a class="text-pink-700 bg-pink-300 hover:bg-pink-600 hover:text-white  focus:ring-pink-300">pink</a>
<a class="text-lime-700 bg-lime-300 hover:bg-lime-600 hover:text-white  focus:ring-lime-300">lime</a>
<a class="text-teal-700 bg-teal-300 hover:bg-teal-600 hover:text-white  focus:ring-teal-300">teal</a>
<a class="text-slate-700 bg-slate-300 hover:bg-slate-600 hover:text-white  focus:ring-slate-300">slate</a>
<a class="text-gray-700 bg-gray-300 hover:bg-gray-600 hover:text-white  focus:ring-gray-300">gray</a>


<a class="text-red-500 bg-red-200 hover:bg-red-600 hover:text-white  focus:ring-red-300">red</a>
<a class="text-green-500 bg-green-200 hover:bg-green-600 hover:text-white  focus:ring-green-300">green</a>
<a class="text-purple-500 bg-purple-200 hover:bg-purple-600 hover:text-white  focus:ring-purple-300">purple</a>
<a class="text-cyan-500 bg-cyan-200 hover:bg-cyan-600 hover:text-white  focus:ring-cyan-300">cyan</a>
<a class="text-blue-500 bg-blue-200 hover:bg-blue-600 hover:text-white  focus:ring-blue-300">cyan</a>
<a class="text-orange-500 bg-orange-200 hover:bg-orange-600 hover:text-white  focus:ring-orange-300">orange</a>
<a class="text-yellow-500 bg-yellow-200 hover:bg-yellow-600 hover:text-white  focus:ring-yellow-300">yellow</a>
<a class="text-pink-500 bg-pink-200 hover:bg-pink-600 hover:text-white  focus:ring-pink-300">pink</a>
<a class="text-lime-500 bg-lime-200 hover:bg-lime-600 hover:text-white  focus:ring-lime-300">lime</a>
<a class="text-teal-500 bg-teal-200 hover:bg-teal-600 hover:text-white  focus:ring-teal-300">teal</a>
<a class="text-slate-500 bg-slate-200 hover:bg-slate-600 hover:text-white  focus:ring-slate-300">slate</a>
<a class="text-gray-500 bg-gray-200 hover:bg-gray-600 hover:text-white  focus:ring-gray-300">gray</a>

<span class="text-blue-500 bg-teal-200 text-md p-2 rounded-xl font-bold">cat</span>
<span class="text-green-500 bg-lime-200 text-md p-2 rounded-xl font-bold">cat</span>
<span class="text-orange-500 bg-yellow-200 text-md p-2 rounded-xl font-bold">cat</span>

 --}}



<textarea name="dummy" id="dummy" cols="50" rows="3">
@foreach (['text', 'bg', 'focus:ring', 'hover:text', 'hover:bg', 'dark:text', 'dark:bg', 'dark:hover:text', 'dark:hover:bg'] as $type)
@foreach (['red', 'green', 'purple', 'cyan', 'blue', 'orange', 'yellow', 'pink', 'lime', 'teal', 'slate', 'gray'] as $col)
@foreach ([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950] as $para)
<span class="{{ $type }}-{{ $col }}-{{ $para }}">{{ $col }}</span> @endforeach
@endforeach
@endforeach
</textarea>
