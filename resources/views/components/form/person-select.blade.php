@props([
    'name',
    'options',
    'label' => null,
    'multiple' => false,
    'compact' => false,
    'placeholder' => 'Cari nama / NIK...',
    'required' => false,
    'nullable' => false,
])

@php
    $items = collect($options)->map(fn ($o) => ['id' => (string) $o->id, 'name' => $o->name, 'nik' => $o->nik ?? null])->values();
    if ($nullable) {
        $items->prepend(['id' => '__none__', 'name' => '- Kosongkan (Tidak ada) -', 'nik' => null]);
    }
    $boxClass = $compact
        ? 'w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90'
        : 'dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:text-white/90';

    // "selected.42" -> "$wire.selected['42']", "newUserId" -> "$wire.newUserId" — matches how wire:model already resolves dotted paths.
    $pathSegments = explode('.', $name);
    $jsAccessor = '$wire.'.array_shift($pathSegments);
    foreach ($pathSegments as $segment) {
        $jsAccessor .= "['".addslashes($segment)."']";
    }
@endphp

<div
    x-data="{
        query: '',
        open: false,
        multiMode: false,
        menuStyle: '',
        options: @js($items),
        selected: (() => {
            const v = {!! $jsAccessor !!};
            if (Array.isArray(v)) return v.map(String);
            return v ? [String(v)] : [];
        })(),
        get filtered() {
            const q = this.query.trim().toLowerCase();
            return this.options
                .filter(o => !this.selected.includes(o.id))
                .filter(o => !q || o.name.toLowerCase().includes(q) || (o.nik ?? '').toLowerCase().includes(q))
                .slice(0, 50);
        },
        nameOf(id) {
            return this.options.find(o => o.id === id)?.name ?? '';
        },
        openMenu() {
            const rect = this.$refs.anchor.getBoundingClientRect();
            this.menuStyle = `position:fixed; top:${rect.bottom + 4}px; left:${rect.left}px; width:${rect.width}px;`;
            this.open = true;
        },
        closeIfOutside(event) {
            if (!this.open) return;
            const inMenu = this.$refs.menu && this.$refs.menu.contains(event.target);
            const inRoot = this.$refs.root && this.$refs.root.contains(event.target);
            if (!inMenu && !inRoot) this.open = false;
        },
        pick(id) {
            this.selected = this.multiMode ? [...this.selected, id] : [id];
            this.query = '';
            this.open = this.multiMode;
            this.sync();
        },
        remove(id) {
            this.selected = this.selected.filter(i => i !== id);
            this.sync();
        },
        sync() {
            {!! $jsAccessor !!} = {{ $multiple ? 'true' : 'false' }} ? this.selected : (this.selected[0] ?? '');
        },
    }"
    x-ref="root"
    @click.window="closeIfOutside($event)"
    @scroll.window.capture="if (!($refs.menu && $refs.menu.contains($event.target))) open = false"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    @if ($label)
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
            @if ($required)
                <span class="text-error-500">*</span>
            @endif
        </label>
    @endif

    <div class="flex gap-2">
        <div class="relative flex-1" x-ref="anchor">
            <template x-if="!multiMode && selected.length && !open">
                <div @click="openMenu(); $nextTick(() => $refs.search.focus())"
                    class="{{ $boxClass }} flex cursor-pointer items-center justify-between">
                    <span x-text="nameOf(selected[0])"></span>
                    <button type="button" @click.stop="remove(selected[0])"
                        class="ml-2 shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
                </div>
            </template>

            <input x-ref="search" type="text" x-model="query" x-show="multiMode || !selected.length || open"
                @focus="openMenu()" placeholder="{{ $placeholder }}" autocomplete="off"
                class="{{ $boxClass }}" />

            <template x-teleport="body">
                <div x-ref="menu" x-show="open" x-cloak :style="menuStyle" @scroll.stop
                    class="z-50 max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                    <template x-for="option in filtered" :key="option.id">
                        <div @click="pick(option.id)"
                            class="cursor-pointer border-b border-gray-100 px-3 py-2 text-sm text-gray-800 last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:text-white/90 dark:hover:bg-gray-800">
                            <span x-text="option.name"></span>
                            <span class="text-gray-400" x-text="option.nik ? ' (' + option.nik + ')' : ''"></span>
                        </div>
                    </template>
                    <div x-show="!filtered.length" class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan.</div>
                </div>
            </template>
        </div>

        @if ($multiple)
            <button type="button" x-show="!multiMode" x-cloak
                @click="multiMode = true; openMenu(); $nextTick(() => $refs.search.focus())"
                class="h-11 shrink-0 rounded-lg border border-gray-300 px-3 text-sm whitespace-nowrap text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                + Pilih lebih dari satu
            </button>
        @endif
    </div>

    @if ($multiple)
        <div x-show="multiMode && selected.length" x-cloak class="mt-2 flex flex-wrap gap-2">
            <template x-for="id in selected" :key="id">
                <div
                    class="group flex items-center gap-1 rounded-full border border-gray-200 bg-gray-100 py-1 pr-2 pl-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                    <span x-text="nameOf(id)"></span>
                    <button type="button" @click="remove(id)"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
                </div>
            </template>
        </div>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
