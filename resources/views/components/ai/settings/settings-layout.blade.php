<div x-data="{ isSidebarOpen: false, activeTab: 'account' }" class="relative h-[calc(100vh-77px)] px-4 xl:flex xl:px-0">
    <div class="flex flex-1 flex-col xl:flex-row">
        <!-- ===== Mobile/Tablet Trigger Bar (hidden on xl+) ===== -->
        <div class="mt-4 flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-3 pl-5 xl:hidden dark:border-gray-800 dark:bg-white/3">
            <span
                class="text-lg font-medium text-gray-800 dark:text-white/90"
                x-text="({'account':'Account','general':'General','billing':'Credit and Billing','personalization':'Personalization','memory':'Memory','file-media':'File & Media','model':'Models','connector':'Connector','data-control':'Data Control'})[activeTab] || activeTab"
            ></span>
            <button
                @click="isSidebarOpen = true"
                class="flex size-10 items-center justify-center rounded-lg border border-gray-300 text-gray-800 dark:border-gray-800 dark:text-white/90"
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

        <x-ai.settings.tab-menu />

        <div class="flex-1 overflow-y-auto xl:py-4">
            <!-- Account Tab Panel -->
            <x-ai.settings.account />

            <!-- General Tab Panel -->
            <x-ai.settings.general />

            <!-- Credit and Billing Tab Panel -->
            <x-ai.settings.credit-and-billing />

            <!-- Personalization Tab Panel -->
            <x-ai.settings.personalization />

            <!-- Memory Tab Panel -->
            <x-ai.settings.memory />

            <!-- File & Media Tab Panel -->
            <x-ai.settings.file-and-media />

            <!-- Model Tab Panel -->
            <x-ai.settings.models />

            <!-- Connector Tab Panel -->
            <x-ai.settings.connector />

            <!-- Data Control Tab Panel -->
            <x-ai.settings.data-control />
        </div>
    </div>
</div>
