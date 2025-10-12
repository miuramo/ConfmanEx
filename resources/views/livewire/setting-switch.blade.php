    <div>
        @if (session()->has('message'))
            <div class="text-green-600 text-sm mb-2">{{ session('message') }}</div>
        @endif

        @if ($setting->valid)
            @if ($this->setting->isbool)
                {{-- <input type="checkbox" wire:click="toggleSetting" class="cursor-pointer" {{ $this->setting->value ? 'checked' : '' }}> --}}
                <x-toggle-livewire wire:click="toggleSetting" :checked="$setting->value == 'true'"></x-toggle-livewire>
                <span title="{{ $this->setting->name }}">{{ $this->setting->misc }}</span>
            @else
                <span title="{{ $this->setting->name }}">{{ $this->setting->misc }}</span>
                <input type="{{ $setting->isnumber ? 'number' : 'text' }}"
                    wire:model.live.debounce.500ms="inputtext" 
                    @if($setting->isnumber) min="0" max="999"
                    @else size="{{ $this->textsize }}" @endif
                    value="{{ $this->setting->value }}" x-init="$el.focus()" /> 
                @if ($setting->isnumber)
                    @if (is_numeric($this->inputtext))
                        <b>{{ $this->inputtext }}</b>
                        <span class="text-blue-500 text-sm">（設定は自動保存します）</span>
                    @else
                        <span class="text-red-600">数値を入力してください</span>
                    @endif
                @else
                    @if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->inputtext))
                        <b>{{ $this->inputtext }}</b>
                        <span class="text-blue-500 text-sm">（設定は自動保存します）</span>
                    @else
                        <span class="text-red-600">YYYY-MM-DDの形式で入力してください</span>
                    @endif
                @endif
            @endif
        @else
            <span class="text-red-600">無効 (invalid)</span>
        @endif

        <style>
            .toggle-checkbox:checked {
                @apply: right-0 border-green-400;
                right: 0;
                border-color: #68D391;
            }

            .toggle-checkbox:checked+.toggle-label {
                @apply: bg-green-400;
                background-color: #68D391;
            }
        </style>
    </div>
