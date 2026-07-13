<!-- ===== Backdrop ===== -->
<div
  x-show="isSidebarOpen"
  x-cloak
  @click="isSidebarOpen = false"
  class="fixed inset-0 z-40 bg-black/60 xl:hidden"
></div>

<!-- ===== Close Button over Backdrop (below xl) ===== -->
<div
  x-show="isSidebarOpen"
  x-cloak
  class="fixed top-4 right-72 z-[999999] xl:hidden"
>
  <button
    @click="isSidebarOpen = false"
    class="flex size-9 items-center justify-center rounded-full bg-white text-gray-500 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="18"
      height="18"
      viewBox="0 0 24 24"
      fill="none"
    >
      <path
        d="M18 6L6 18M6 6l12 12"
        stroke="currentColor"
        stroke-width="1.5"
        stroke-linecap="round"
      />
    </svg>
  </button>
</div>

<!-- ===== Right Drawer (below xl) ===== -->
<div
  :class="isSidebarOpen ? 'translate-x-0' : 'translate-x-full'"
  class="fixed top-0 right-0 z-[99999] h-full w-72 overflow-y-auto bg-white p-3 shadow-xl transition-transform duration-300 ease-in-out xl:hidden dark:bg-gray-900"
>
  <x-ai.settings.tab-menu-inner />
</div>

<!-- ===== Desktop Sidebar (xl+) ===== -->
<div
  class="hidden h-full overflow-y-auto border-gray-200 bg-white p-3 xl:block xl:w-62.5 xl:border-r dark:border-gray-800 dark:bg-gray-900"
>
  <x-ai.settings.tab-menu-inner />
</div>
