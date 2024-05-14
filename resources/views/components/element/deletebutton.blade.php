@props([
    'action' => '',
    'color' => 'red',
    'confirm' => '削除してよいですか？',
    'align' => 'left',
])
<!-- components.element.deletebutton -->
<form action="{{ $action }}" method="post" class="float-{{ $align }}">
    @method('DELETE')
    @csrf
    <button type="submit" onclick="return confirm('{{ $confirm }}')" class="inline-flex justify-center py-1 px-2 border border-transparent shadow-md text-md font-medium rounded-md text-white bg-{{$color}}-500 hover:bg-{{$color}}-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{$color}}-500">
        {{ $slot }}
    </button>
</form>
