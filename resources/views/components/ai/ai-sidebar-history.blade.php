@props([
    'isSidebarOpen' => false,
    'starredChats' => null,
    'recentChats' => null,
    'lastWeekChats' => null,
    'todayChats' => null,
    'yesterdayChats' => null,
])

@php
    // If todayChats or yesterdayChats are passed from a controller/layout, map them to recentChats
    if ($recentChats === null) {
        if ($todayChats !== null || $yesterdayChats !== null) {
            $recentChats = [];
            foreach ($todayChats ?? [] as $chat) {
                $recentChats[] = [
                    'id' => $chat['id'] ?? uniqid(),
                    'title' => $chat['title'],
                    'isStarred' => false,
                ];
            }
            foreach ($yesterdayChats ?? [] as $chat) {
                $recentChats[] = [
                    'id' => $chat['id'] ?? uniqid(),
                    'title' => $chat['title'],
                    'isStarred' => false,
                ];
            }
        } else {
            $recentChats = [
                ['id' => 'r1', 'title' => 'Address login page contrast issues', 'isStarred' => false],
                ['id' => 'r2', 'title' => 'Animate an error state modal', 'isStarred' => false],
                ['id' => 'r3', 'title' => 'Implement show/hide password feature', 'isStarred' => false],
                ['id' => 'r4', 'title' => 'Propose a dark theme color scheme', 'isStarred' => false],
            ];
        }
    }

    if ($starredChats === null) {
        $starredChats = [
            ['id' => 's1', 'title' => 'Design login form with input validation', 'isStarred' => true],
            ['id' => 's2', 'title' => 'Design an error state modal', 'isStarred' => true],
            ['id' => 's3', 'title' => 'Propose a dark theme color schemel', 'isStarred' => true],
            
        ];
    }

    if ($lastWeekChats === null) {
        $lastWeekChats = [
            ['id' => 'lw1', 'title' => 'Improve login page accessibility features', 'isStarred' => false],
            ['id' => 'lw2', 'title' => 'Improve login page accessibility features', 'isStarred' => false],
        ];
    }

    $chatGroups = [
        [
            'key' => 'starred',
            'title' => 'Starred',
            'chats' => $starredChats,
            'isLastWeek' => false,
            'isRecent' => false,
        ],
        [
            'key' => 'recent',
            'title' => 'Recent',
            'chats' => $recentChats,
            'isLastWeek' => false,
            'isRecent' => true,
        ],
        [
            'key' => 'last_week',
            'title' => 'Last Week',
            'chats' => $lastWeekChats,
            'isLastWeek' => true,
            'isRecent' => false,
        ],
    ];
@endphp

{{-- backdropd --}}
<div x-show="isSidebarOpen" @click="isSidebarOpen = false" x-transition.opacity
    class="fixed inset-0 z-[99999] bg-black/50 xl:hidden dark:bg-black/80">
    <div class="absolute top-4 right-[300px]">
        <button @click.stop="isSidebarOpen = false"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-800 transition hover:bg-gray-100 dark:bg-gray-800 dark:text-white/90 dark:hover:bg-white/3 hover:dark:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M6.75104 17.249L17.249 6.75111M6.75104 6.75098L17.249 17.2489" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>
</div>
{{-- backdropd --}}

<aside
    x-data="{ showMore: false, searchQuery: '' }"
    :class="isSidebarOpen ? 'flex fixed xl:static top-0 right-0 z-999999 h-screen bg-white dark:bg-gray-900' :
        'hidden xl:flex'"
    class="z-50 w-[280px] flex-col border-l border-gray-200 bg-white p-6 ease-in-out dark:border-gray-800 dark:bg-gray-900">
    <button
        class="bg-brand-500 hover:bg-brand-600 flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M5 10.0002H15.0006M10.0002 5V15.0006" stroke="white" stroke-width="1.5" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        New Chat
    </button>
    <div class="mt-5">
        <form>
            <div class="relative">
                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z"
                            fill="" />
                    </svg>
                </span>
                <input type="text" placeholder="Search..." x-model="searchQuery"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-3.5 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>
        </form>
    </div>
    <!-- Chat Items -->
    <div class="custom-scrollbar mt-6 h-full flex-1 space-y-3 overflow-y-auto text-sm">
        @foreach ($chatGroups as $group)
            <div
                @if($group['isLastWeek']) x-show="showMore" class="pl-3" @elseif($group['isRecent']) class="relative" @endif
            >
                <div class="relative">
                    <p class="mb-3 pl-3 text-xs text-gray-400 {{ $group['isLastWeek'] ? 'uppercase pl-0' : '' }}">
                        {{ $group['title'] }}
                    </p>
                    <ul class="space-y-1">
                        @foreach ($group['chats'] as $chat)
                            <li x-data="{ open: false, isDeleted: false, isRenaming: false, chatTitle: '{{ addslashes($chat['title']) }}' }"
                                x-show="!isDeleted && (!searchQuery || chatTitle.toLowerCase().includes(searchQuery.toLowerCase()))"
                                class="group relative rounded-full px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-950">
                                <div class="flex cursor-pointer items-center justify-between">
                                    <div class="flex-1 min-w-0 pr-2">
                                        <a x-show="!isRenaming" href="#" class="block truncate text-sm text-gray-700 dark:text-gray-400" x-text="chatTitle"></a>
                                        <input x-show="isRenaming" type="text" x-model="chatTitle" @keydown.enter="isRenaming = false" @click.stop @keydown.escape="isRenaming = false" @click.outside="isRenaming = false" x-effect="isRenaming && $nextTick(() => $el.focus())"
                                            class="w-full bg-transparent border-0 border-b border-brand-500 p-0 text-sm text-gray-800 focus:ring-0 focus:outline-hidden dark:text-white" />
                                    </div>

                                    <!-- 3-dot menu button -->
                                    <button @click="open = !open"
                                        class="invisible ml-2 rounded-full p-1 text-gray-700 group-hover:visible hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 18 18" fill="none">
                                            <path d="M4.5 9.00384L4.5 8.99634M13.5 9.00384V8.99634M9 9.00384V8.99634"
                                                stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Dropdown menu -->
                                <div x-show="open" @click.outside="open = false"
                                    class="shadow-theme-md absolute right-0 z-10 mt-1 w-44 rounded-lg bg-white p-1.5 dark:bg-gray-900"
                                    x-transition>
                                    <ul class="text-sm text-gray-700 dark:text-gray-400">
                                        <li>
                                            <button x-data="{ selected: '{{ $chat['isStarred'] ? 'Remove Starred' : 'Add Starred' }}' }"
                                                @click="selected = selected === 'Remove Starred' ? 'Add Starred' : 'Remove Starred'"
                                                class="flex w-full items-center gap-2 rounded-lg bg-transparent px-1.5 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-white/90">
                                                <svg x-show="selected === 'Remove Starred'" xmlns="http://www.w3.org/2000/svg"
                                                    width="17" height="16" viewBox="0 0 17 16" fill="none">
                                                    <path
                                                        d="M8.37827 0.75L10.7355 5.52634L16.0065 6.29226L12.1924 10.0101L13.0928 15.2598L8.37827 12.7812L3.66374 15.2598L4.56413 10.0101L0.75 6.29226L6.021 5.52634L8.37827 0.75Z"
                                                        fill="currentColor" stroke="currentColor" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <svg x-show="selected === 'Add Starred'" xmlns="http://www.w3.org/2000/svg"
                                                    width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                    <path
                                                        d="M10.0013 2.3374L12.3586 7.11374L17.6296 7.87966L13.8154 11.5975L14.7158 16.8472L10.0013 14.3687L5.28679 16.8472L6.18718 11.5975L2.37305 7.87966L7.64405 7.11374L10.0013 2.3374Z"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <span x-text="selected"></span>
                                            </button>
                                        </li>
                                        <li>
                                            <button @click="isRenaming = true; open = false"
                                                class="flex w-full items-center gap-2 rounded-lg bg-transparent px-1.5 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-white/90">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 20 20" fill="none">
                                                    <path
                                                        d="M12.3861 5.08135L14.9182 7.61345M15.6437 3.59219L16.408 4.35652C16.8962 4.84468 16.8962 5.63613 16.408 6.12429L7.83547 14.6968C7.69039 14.8419 7.51182 14.9491 7.31554 15.0088L3.97461 16.0256L4.99141 12.6847C5.05115 12.4884 5.15829 12.3098 5.30337 12.1647L13.8759 3.59219C14.3641 3.10404 15.1555 3.10404 15.6437 3.59219Z"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                Rename
                                            </button>
                                        </li>
                                        <hr class="my-1 border-gray-200 dark:border-white/10" />
                                        <li>
                                            <button @click="isDeleted = true; open = false"
                                                class="flex w-full items-center gap-2 rounded-lg bg-transparent px-1.5 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-white/90">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 20 20" fill="none">
                                                    <path
                                                        d="M4.37504 4.7915V16.4582C4.37504 17.1485 4.93468 17.7082 5.62504 17.7082H14.375C15.0654 17.7082 15.625 17.1485 15.625 16.4582V4.7915M3.33337 4.7915H16.6659M4.37504 13.2461V8.24609M15.625 13.2461V8.24609M8.33337 13.7498V8.74984M11.6667 13.7498V8.74984M12.708 4.7915V3.5415C12.708 2.85115 12.1483 2.2915 11.458 2.2915H8.5413C7.85094 2.2915 7.2913 2.85115 7.2913 3.5415V4.7915H12.708Z"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if ($group['isRecent'])
                        <div :class="showMore ? 'hidden' : 'block'"
                            class="pointer-events-none absolute bottom-0 left-0 z-10 h-8 w-full bg-gradient-to-t from-white to-transparent dark:from-gray-900">
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Show more toggle -->
        <div class="mt-4 pl-3">
            <button x-on:click="showMore = !showMore"
                class="text-primary-500 flex w-full items-center justify-between text-xs font-medium text-gray-400">
                <!-- Text on the left -->
                <span x-text="showMore ? 'Show less...' : 'Show more...'"></span>

                <!-- Arrow icon on the right -->
                <svg :class="{ 'rotate-180': showMore }" class="ml-2" xmlns="http://www.w3.org/2000/svg"
                    width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M3.83331 6.41669L7.99998 10.5834L12.1666 6.41669" stroke="currentColor"
                        stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </div>
</aside>
