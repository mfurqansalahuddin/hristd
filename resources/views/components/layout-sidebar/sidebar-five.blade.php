@php
$menuGroups = [
    [
        'id'    => 'GetStarted',
        'title' => 'Get Started',
        'items' => [
            ['label' => 'Introduction',     'key' => 'Introduction'],
            ['label' => 'Quick Start',      'key' => 'QuickStart'],
            ['label' => 'Framework guides', 'key' => 'FrameworkGuides'],
            ['label' => 'Usage',            'key' => 'Usage'],
            ['label' => 'Javascript',       'key' => 'Javascript'],
            ['label' => 'Accessibility',    'key' => 'Accessibility'],
            ['label' => 'Upgrade Guide',    'key' => 'UpgradeGuide'],
            ['label' => 'License',          'key' => 'License'],
        ],
    ],
    [
        'id'    => 'Components',
        'title' => 'Components',
        'items' => [
            ['label' => 'Accordion',       'key' => 'Accordion'],
            ['label' => 'Alert',           'key' => 'Alert'],
            ['label' => 'Avatar',          'key' => 'Avatar'],
            ['label' => 'Badge',           'key' => 'Badge'],
            ['label' => 'Button',          'key' => 'Button'],
            ['label' => 'Card',            'key' => 'Card'],
            ['label' => 'Carousel',        'key' => 'Carousel'],
            ['label' => 'Chat Bubble',     'key' => 'ChatBubble'],
            ['label' => 'Collapse',        'key' => 'Collapse'],
            ['label' => 'Indicator',       'key' => 'Indicator'],
            ['label' => 'List Group',      'key' => 'ListGroup'],
            ['label' => 'Loading',         'key' => 'Loading'],
            ['label' => 'Progress',        'key' => 'Progress'],
            ['label' => 'Radial progress', 'key' => 'RadialProgress'],
            ['label' => 'Skeleton',        'key' => 'Skeleton'],
            ['label' => 'Stack',           'key' => 'Stack'],
        ],
    ],
];
@endphp

<aside
    x-data="{
        versionOpen: false,
        activeVersion: 'v2.0.8-alpha',
        versions: ['v1.0.1', 'v2.0.8-alpha', 'V3.0.9-beta1'],
        selected: '',
        openSections: ['GetStarted', 'Components'],
        toggleSection(id) {
            const idx = this.openSections.indexOf(id);
            if (idx > -1) {
                this.openSections.splice(idx, 1);
            } else {
                this.openSections.push(id);
            }
        },
        isSectionOpen(id) {
            return this.openSections.includes(id);
        }
    }"
    :class="$store.sidebar.isMobileOpen ? 'translate-x-0' : '-translate-x-full'"
    :style="(window.innerWidth >= 1280) ? (!$store.sidebar.isExpanded ? 'width: 0; overflow: hidden; border-right-width: 0; min-width: 0;' : '') : 'width: 290px;'"
    class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-[290px] flex-col border-r border-gray-200 bg-gray-50 transition-all duration-300 xl:static xl:translate-x-0 dark:border-gray-800 dark:bg-gray-900"
    @click.outside="if (window.innerWidth < 1280) $store.sidebar.isMobileOpen = false"
>
    <!-- SIDEBAR HEADER -->
    <div class="px-5 pt-5 pb-7">
        <div class="relative flex items-center justify-between gap-2.5" @click.outside="versionOpen = false">
            <div class="flex items-center gap-3">
                <a href="/" class="shrink-0">
                    <img src="/images/logo/logo-icon.svg" alt="Logo" class="h-8 w-8" />
                </a>
                <div>
                    <span class="text-sm font-normal text-gray-800 dark:text-white/90">
                        TailAdmin Docs
                    </span>
                    <span class="flex items-center gap-1 rounded text-xs font-medium text-gray-500 dark:text-gray-400">
                        V2.0.0
                    </span>
                </div>
            </div>
            <div class="ml-auto">
                <button
                    @click="versionOpen = !versionOpen"
                    class="inline-flex size-5 items-center justify-center rounded-md border border-gray-200 text-gray-800 dark:border-gray-800 dark:text-gray-400">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.1668 6.24235L8.00016 2.07568L3.8335 6.24235M3.8335 9.75736L8.00016 13.924L12.1668 9.75736" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <!-- Version Dropdown -->
            <div
                x-show="versionOpen"
                x-cloak
                class="absolute top-full right-0 left-0 z-50 mt-2 overflow-hidden rounded-lg bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <template x-for="version in versions" :key="version">
                    <button
                        @click="activeVersion = version; versionOpen = false"
                        class="mb-0.5 flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-white/90"
                        :class="activeVersion === version ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700'">
                        <span x-text="version"></span>
                        <svg x-show="activeVersion === version" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-700 dark:text-gray-300">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </button>
                </template>
            </div>
        </div>
    </div>
    <!-- SIDEBAR HEADER -->

    <!-- NAV -->
    <div class="no-scrollbar flex flex-col overflow-y-auto pb-10">
        <nav>
            @foreach ($menuGroups as $group)
                <div>
                    <!-- Section Toggle Button -->
                    <button
                        @click="toggleSection('{{ $group['id'] }}')"
                        class="flex w-full items-center justify-between px-5 py-3 text-sm font-normal text-gray-700 dark:text-gray-300">
                        {{ $group['title'] }}
                        <svg :class="isSectionOpen('{{ $group['id'] }}') ? 'rotate-180' : ''" class="text-gray-400 transition-transform duration-200" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.83325 6.41675L7.99992 10.5834L12.1666 6.41675" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <!-- Section Items -->
                    <div x-show="isSectionOpen('{{ $group['id'] }}')">
                        <ul class="ml-5 flex flex-col gap-3 border-l border-gray-200 dark:border-gray-700">
                            @foreach ($group['items'] as $item)
                                <li>
                                    <a href="#"
                                        @click.prevent="selected = '{{ $item['key'] }}'"
                                        class="docs-border-item"
                                        :class="selected === '{{ $item['key'] }}' ? 'docs-border-item-active' : 'docs-border-item-inactive'">
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </nav>
    </div>
    <!-- NAV -->
</aside>
