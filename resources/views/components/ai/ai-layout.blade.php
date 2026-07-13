
<div x-data="{
    isSidebarOpen: false,
    currentMessage: '',
    isLoading: false,
    toggleSidebar() {
        this.isSidebarOpen = !this.isSidebarOpen
    },
    closeSidebar() {
        this.isSidebarOpen = false
    },
    async handleSubmit() {
        if (!this.currentMessage.trim() || this.isLoading) return

        const message = this.currentMessage.trim()
    }
}" class="relative h-[calc(100vh-77px)] px-4 xl:flex xl:px-0">

    <!-- Mobile Chat Header -->
    <div class="my-6 flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-3 xl:hidden dark:border-gray-800 dark:bg-gray-900">
        <h4 class="pl-2 text-lg font-medium text-gray-800 dark:text-white/90">Chats History</h4>
        <button
            @click="toggleSidebar"
            class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path
                    d="M4 6L20 6M4 18L20 18M4 12L20 12"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 xl:pb-10">
        {{-- Main content slot --}}
        {{ $slot }}
    </div>

    <!-- AI History Sidebar -->
    <x-ai.ai-sidebar-history />
</div>
