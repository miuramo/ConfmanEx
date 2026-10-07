    <div
    @if($inline) class="inline-block" @endif
    @if($setting->valid)
        data-scheduled-update-cell="1"
        data-target-type="App\Models\Setting"
        data-target-id="{{ $setting->id }}"
        data-field="value"
        data-scheduled-update-url="{{ route('scheduled_update.create') }}"
    @endif
    >
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
                    @else style="width: {{ $this->textsize }}ch" @endif
                    value="{{ $this->setting->value }}" x-init="$el.focus()" /> 
                @if ($setting->isnumber)
                    @if (is_numeric($this->inputtext))
                        <b>{{ $this->inputtext }}</b>
                        <span class="text-blue-500 text-sm">（←表示内容で保存済）</span>
                    @else
                        <span class="text-red-600">数値を入力してください</span>
                    @endif
                @else
                    @if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->inputtext))
                        <b>{{ $this->inputtext }}</b>
                        <span class="text-blue-500 text-sm">（←表示内容で保存済）</span>
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

        @if ($setting->valid)
            <div data-scheduled-update-menu class="hidden fixed z-50 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 shadow-lg rounded-md py-1 text-sm">
                <a href="#" class="block px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-pink-100 dark:hover:bg-slate-700">
                    このセルを予約更新
                </a>
            </div>
        @endif
    </div>

    @once
        @push('localjs')
            <script>
                (() => {
                    document.addEventListener('contextmenu', (event) => {
                        const cell = event.target.closest('[data-scheduled-update-cell="1"]');
                        if (!cell) {
                            return;
                        }

                        event.preventDefault();
                        const menu = cell.querySelector('[data-scheduled-update-menu]');
                        const link = menu.querySelector('a');
                        const params = new URLSearchParams({
                            target_type: cell.dataset.targetType,
                            target_id: cell.dataset.targetId,
                            field_name: cell.dataset.field,
                        });
                        link.href = `${cell.dataset.scheduledUpdateUrl}?${params.toString()}`;
                        menu.style.left = `${event.clientX}px`;
                        menu.style.top = `${event.clientY}px`;
                        menu.classList.remove('hidden');
                    });

                    document.addEventListener('click', () => {
                        document.querySelectorAll('[data-scheduled-update-menu]').forEach((menu) => {
                            menu.classList.add('hidden');
                        });
                    });

                    document.addEventListener('mouseout', (event) => {
                        const menu = event.target.closest('[data-scheduled-update-menu]');
                        if (menu && !menu.contains(event.relatedTarget)) {
                            menu.classList.add('hidden');
                        }
                    });

                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            document.querySelectorAll('[data-scheduled-update-menu]').forEach((menu) => {
                                menu.classList.add('hidden');
                            });
                        }
                    });
                })();
            </script>
        @endpush
    @endonce
