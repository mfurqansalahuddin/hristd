<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-7 flex items-center justify-between gap-2">
        <div>
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">
                Sales by Country
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Country-wise sales overview
            </p>
        </div>
        <div x-data="{ openDropDown: false }" class="relative">
            <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" fill="" />
                </svg>
            </button>
            <div x-show="openDropDown" @click.outside="openDropDown = false" class="shadow-theme-lg dark:bg-gray-dark absolute top-full right-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                    View More
                </button>
                <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                    Delete
                </button>
            </div>
        </div>
    </div>
    <div id="mapTwo" class="mapTwo -mt-6! h-[180px]! w-full! bg-transparent! p-3"></div>
    <ul class="space-y-4 pt-3">
        <li class="grid grid-cols-4 justify-between gap-4">
            <div class="col-span-2">
                <div class="flex items-center gap-2.5">
                    <img src="./images/flag/flag-01.png" class="size-4 rounded-full" alt="flag" />
                    <span class="text-sm text-gray-800 dark:text-white/90">USA</span>
                </div>
            </div>
            <div class="col-span-1">
                <span class="text-sm text-gray-700 dark:text-gray-400">$20.594</span>
            </div>
            <div class="col-span-1 text-right">
                <span class="text-sm text-gray-700 dark:text-gray-400">79%</span>
            </div>
        </li>
        <li class="grid grid-cols-4 justify-between gap-4">
            <div class="col-span-2">
                <div class="flex items-center gap-2.5">
                    <img src="./images/flag/flag-02.png" class="size-4 rounded-full" alt="flag" />
                    <span class="text-sm text-gray-800 dark:text-white/90">France</span>
                </div>
            </div>
            <div class="col-span-1">
                <span class="text-sm text-gray-700 dark:text-gray-400">$20.594</span>
            </div>
            <div class="col-span-1 text-right">
                <span class="text-sm text-gray-700 dark:text-gray-400">779%</span>
            </div>
        </li>
        <li class="grid grid-cols-4 justify-between gap-4">
            <div class="col-span-2">
                <div class="flex items-center gap-2.5">
                    <img src="./images/flag/flag-03.png" class="size-4 rounded-full" alt="flag" />
                    <span class="text-sm text-gray-800 dark:text-white/90">Japan</span>
                </div>
            </div>
            <div class="col-span-1">
                <span class="text-sm text-gray-700 dark:text-gray-400">$15.230</span>
            </div>
            <div class="col-span-1 text-right">
                <span class="text-sm text-gray-700 dark:text-gray-400">68%</span>
            </div>
        </li>
        <li class="grid grid-cols-4 justify-between gap-4">
            <div class="col-span-2">
                <div class="flex items-center gap-2.5">
                    <img src="./images/flag/flag-04.png" class="size-4 rounded-full" alt="flag" />
                    <span class="text-sm text-gray-800 dark:text-white/90">Germany</span>
                </div>
            </div>
            <div class="col-span-1">
                <span class="text-sm text-gray-700 dark:text-gray-400">$16.450</span>
            </div>
            <div class="col-span-1 text-right">
                <span class="text-sm text-gray-700 dark:text-gray-400">72%</span>
            </div>
        </li>
    </ul>
</div>
