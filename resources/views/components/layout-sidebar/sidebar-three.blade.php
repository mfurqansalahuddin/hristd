@php
    $menuGroups = [
        [
            'title' => 'Get Started',
            'items' => [
                ['label' => 'Quick Start', 'route' => '#', 'activePages' => ['quick-start']],
                ['label' => 'Installation Guide', 'route' => '#', 'activePages' => ['installation-guide']],
                ['label' => 'Tutorials', 'route' => '#', 'activePages' => ['tutorials']],
                ['label' => 'API Reference', 'route' => '#', 'activePages' => ['api-reference']],
                ['label' => 'Best Practices', 'route' => '#', 'activePages' => ['best-practices']],
                ['label' => 'FAQ', 'route' => '#', 'activePages' => ['faq']],
            ]
        ],
        [
            'title' => 'Components',
            'items' => [
                ['label' => 'Accordion', 'route' => '#', 'activePages' => ['accordion']],
                ['label' => 'Alert', 'route' => '#', 'activePages' => ['alert']],
                ['label' => 'Alert Dialog', 'route' => '#', 'activePages' => ['alert-dialog']],
                ['label' => 'Aspect Ratio', 'route' => '#', 'activePages' => ['aspect-ratio']],
                ['label' => 'Avatar', 'route' => '#', 'activePages' => ['avatar']],
                ['label' => 'Badge', 'route' => '#', 'activePages' => ['badge']],
                ['label' => 'Breadcrumbs', 'route' => '#', 'activePages' => ['breadcrumbs']],
                ['label' => 'Button', 'route' => '#', 'activePages' => ['button']],
                ['label' => 'Button Group', 'route' => '#', 'activePages' => ['button-group']],
                ['label' => 'Card', 'route' => '#', 'activePages' => ['card']],
                ['label' => 'Checkbox', 'route' => '#', 'activePages' => ['checkbox']],
                ['label' => 'Circular Progress', 'route' => '#', 'activePages' => ['circular-progress']],
                ['label' => 'Date Picker', 'route' => '#', 'activePages' => ['date-picker']],
                ['label' => 'Dialog', 'route' => '#', 'activePages' => ['dialog']],
            ]
        ],
        [
            'title' => 'API Reference',
            'items' => [
                ['label' => 'File Conventions', 'route' => '#', 'activePages' => ['file-conventions']],
                ['label' => 'Version Control', 'route' => '#', 'activePages' => ['version-control']],
                ['label' => 'File Organization', 'route' => '#', 'activePages' => ['file-organization']],
                ['label' => 'Backup Procedures', 'route' => '#', 'activePages' => ['backup-procedures']],
            ]
        ]
    ];
@endphp

<aside x-data="{
    page: window.location.pathname.split('/').pop() || '',
    versionOpen: false,
    activeVersion: 'v2.0.8-alpha',
    versions: ['v1.0.1', 'v2.0.8-alpha', 'V3.0.9-beta1'],
    isActive(pageName) {
        return this.page === pageName;
    },
    isGroupActive(activePages) {
        if (!activePages) return false;
        return activePages.some(p => this.isActive(p));
    },
    toggleVersion() {
        this.versionOpen = !this.versionOpen;
    },
    setVersion(v) {
        this.activeVersion = v;
        this.versionOpen = false;
    }
  }"
  :class="{
    'translate-x-0': $store.sidebar.isMobileOpen,
    '-translate-x-full': !$store.sidebar.isMobileOpen
  }"
  :style="{
    width: (window.innerWidth >= 1280) ? ($store.sidebar.isExpanded ? '290px' : '0') : '290px',
    overflow: (window.innerWidth >= 1280 && !$store.sidebar.isExpanded) ? 'hidden' : '',
    borderRightWidth: (window.innerWidth >= 1280 && !$store.sidebar.isExpanded) ? '0' : '1px',
    minWidth: (window.innerWidth >= 1280) ? ($store.sidebar.isExpanded ? '290px' : '0') : '290px'
  }"
  class="sidebar fixed top-16 left-0 z-[9999] flex h-screen flex-col border-r border-gray-200 bg-white transition-all duration-300 xl:static xl:translate-x-0 dark:border-gray-800 dark:bg-gray-900"
  @click.outside="if (window.innerWidth < 1280) $store.sidebar.isMobileOpen = false">
  
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
          <span x-text="activeVersion" class="flex items-center gap-1 rounded text-xs font-normal text-gray-500 dark:text-gray-400">
          </span>
        </div>
      </div>
      <div class="ml-auto">
        <button @click="toggleVersion()" class="inline-flex size-5 items-center justify-center rounded-md border border-gray-200 text-gray-800 dark:border-gray-800 dark:text-gray-400">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.1668 6.24235L8.00016 2.07568L3.8335 6.24235M3.8335 9.75736L8.00016 13.924L12.1668 9.75736" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>

      <!-- Version Dropdown -->
      <div x-show="versionOpen" x-cloak class="absolute top-full right-0 left-0 z-50 mt-2 overflow-hidden rounded-lg bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-800">
        <template x-for="version in versions" :key="version">
          <button @click="setVersion(version)" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-normal text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700" :class="{ 'bg-gray-100 dark:bg-gray-700': activeVersion === version }">
            <span x-text="version"></span>
            <template x-if="activeVersion === version">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-700 dark:text-gray-300">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </template>
          </button>
        </template>
      </div>
    </div>
  </div>
  <!-- SIDEBAR HEADER -->

  <!-- SEARCH -->
  <div class="px-5 pb-5">
    <div class="relative">
      <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 dark:text-gray-400">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12.944 12.945L15.9366 15.9376M14.8125 8.43695C14.8125 11.9569 11.9583 14.8104 8.4375 14.8104C4.91669 14.8104 2.0625 11.9569 2.0625 8.43695C2.0625 4.91698 4.91669 2.06348 8.4375 2.06348C11.9583 2.06348 14.8125 4.91698 14.8125 8.43695Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </span>
      <input type="text" placeholder="Search the docs" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-800 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30" />
    </div>
  </div>
  <!-- SEARCH -->

  <!-- NAV -->
  <div class="no-scrollbar flex flex-col overflow-y-auto px-5 pb-10">
    <nav>
      @foreach ($menuGroups as $group)
        <div class="mb-7">
          <h3 class="mb-2 px-3 text-xs font-medium text-gray-800 dark:text-white/90">
            {{ $group['title'] }}
          </h3>
          <ul class="flex flex-col gap-0.5">
            @foreach ($group['items'] as $item)
              @php
                  $activePagesStr = isset($item['activePages']) ? json_encode($item['activePages']) : '[]';
              @endphp
              <li>
                <a href="{{ $item['route'] ?? 'javascript:void(0)' }}"
                  @click="page = '{{ isset($item['activePages']) && count($item['activePages']) > 0 ? $item['activePages'][0] : '' }}'"
                  class="docs-menu-item"
                  :class="isGroupActive({{ $activePagesStr }}) ? 'docs-menu-item-active' : 'docs-menu-item-inactive'">
                  {{ $item['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </nav>
  </div>
  <!-- NAV -->
</aside>
