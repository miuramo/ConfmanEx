    <style>
        /* CHECKBOX TOGGLE SWITCH */
        /* @apply rules for documentation, these do not work as inline style */
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
    <div>
        @if (session()->has('message'))
            <div class="text-green-600 text-sm mb-2">{{ session('message') }}</div>
        @endif
        @if ($message)
            <div class="text-blue-600 text-sm mb-2">{{ $message }}</div>
        @endif
        <!-- デバッグ情報 -->
        <div class="text-xs text-gray-500 mb-2">
            Component: {{ get_class($this) }} | Name: {{ $this->name }} | Setting ID: {{ $this->setting?->id }}
        </div>
        
        <span title="{{ $this->setting->name }}">{{ $this->setting->misc }}</span> → 
        @if ($this->setting->isbool)
            <!-- デバッグ用テストボタン -->
            <button wire:click="testMethod" class="bg-red-500 text-white px-2 py-1 rounded mr-2">Test Method</button>
            <button wire:click="toggleSetting" class="bg-blue-500 text-white px-2 py-1 rounded mr-2">Test Toggle</button>
            
            <div wire:click="toggleSetting" class="cursor-pointer">TOGGLE
            </div>
                <x-toggle-livewire formid="admincrudpost" name="name_setting__{{ $setting->id }}__tinyint"
                    id="setting__{{ $setting->id }}__tinyint" :checked="$setting->value"></x-toggle-livewire>
        @else
            {{ $this->setting->value }}
        @endif
        {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    </div>
