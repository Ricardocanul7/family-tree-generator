<x-filament::dropdown
    placement="bottom-end"
    flip
    shift
    teleport
>
    <x-slot name="trigger">
        <button
            class="flex items-center gap-1 px-2 py-1 text-sm font-medium rounded-lg transition-colors
                text-gray-600 hover:bg-gray-100
                dark:text-gray-400 dark:hover:bg-gray-700"
            title="{{ $locales[$current] ?? strtoupper($current) }}"
        >
            <x-filament::icon
                icon="heroicon-o-language"
                class="w-4 h-4"
            />
            <span class="text-xs font-semibold">{{ strtoupper($current) }}</span>
            <x-filament::icon
                icon="heroicon-o-chevron-down"
                class="w-3 h-3"
            />
        </button>
    </x-slot>

    <x-filament::dropdown.list>
        @foreach ($locales as $code => $name)
            <x-filament::dropdown.list.item
                tag="a"
                :href="'/lang/' . $code"
                :color="$code === $current ? 'primary' : 'gray'"
                :icon="$code === $current ? 'heroicon-o-check' : null"
            >
                <span>{{ $name }}</span>
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
