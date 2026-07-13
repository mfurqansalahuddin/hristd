@props([
    'models' => null,
    'type' => 'code',
    'placeholder' => 'Ask anything to build'
])

@php
if (!$models) {
    $models = [
        [
            'name' => 'Auto',
            'value' => 'Auto',
            'icon_type' => 'svg',
            'is_new' => false,
        ],
        [
            'name' => 'GPT 4.5',
            'value' => 'GPT 4.5',
            'icon_type' => 'gpt',
            'is_new' => true,
        ],
        [
            'name' => 'GPT 5.5',
            'value' => 'GPT 5.5',
            'icon_type' => 'gpt',
            'is_new' => false,
        ],
        [
            'name' => 'Claude Sonnet 4.5',
            'value' => 'Claude Sonnet 4.5',
            'icon_type' => 'claude',
            'is_new' => false,
        ],
        [
            'name' => 'Claude Sonnet 4.6',
            'value' => 'Claude Sonnet 4.6',
            'icon_type' => 'claude',
            'is_new' => false,
        ],
        [
            'name' => 'Grok 3.0',
            'value' => 'Grok 3.0',
            'icon_type' => 'grok',
            'is_new' => false,
        ],
        [
            'name' => 'Grok 2.0',
            'value' => 'Grok 2.0',
            'icon_type' => 'grok',
            'is_new' => false,
        ],
    ];
}
@endphp

<div class="lg:bottom-10 fixed bottom-5 left-1/2 z-20 w-full -translate-x-1/2 transform px-4 sm:px-6 lg:px-8">
    <!-- Container with max width -->
    <div
        class="mx-auto w-full max-w-[720px] rounded-2xl border border-gray-200 bg-white p-3 shadow-xs dark:border-gray-700 dark:bg-white/5">
        <!-- Textarea -->
        <textarea placeholder="{{ $placeholder }}"
            class="h-20 w-full resize-none border-none bg-transparent p-0 px-2 font-normal text-gray-800 outline-none placeholder:text-sm placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>

        <!-- Bottom Section -->
        <div class="flex items-center justify-between pt-2">
            @if ($type === 'text')
                <div class="flex items-center gap-1" x-data="{ open: false, selected: '' }" @click.away="open = false"
                    @keydown.escape.window="open = false">
                    <!-- Model Power -->
                    <div class="relative">
                        <button @click="open = !open" :aria-expanded="open"
                            class="flex size-9 items-center justify-center gap-1.5 rounded-lg border border-gray-100 bg-transparent text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-300">
                            <!-- Plus Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path d="M5 10.0002H15.0006M10.0002 5V15.0006" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <!-- Dropdown Menu -->
                        <ul x-show="open" x-transition.origin.bottom.left x-cloak role="menu"
                            class="absolute bottom-full left-0 mb-2 min-w-[200px] space-y-0.5 rounded-xl bg-white p-1.5 shadow-md dark:bg-gray-900">
                            <li>
                                <input type="file" x-ref="fileInput" class="hidden" />
                                <button @click="$refs.fileInput.click(); open = false"
                                    class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                    role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                        fill="none">
                                        <path
                                            d="M15.0005 11.25C15.4146 11.2502 15.7505 11.5859 15.7505 12V13.5C15.7505 14.7425 14.743 15.7498 13.5005 15.75H4.49957C3.25698 15.7499 2.24957 14.7426 2.24957 13.5V12C2.24957 11.5858 2.5854 11.2501 2.99957 11.25C3.41378 11.25 3.74957 11.5858 3.74957 12V13.5C3.74957 13.9142 4.0854 14.2499 4.49957 14.25H13.5005C13.9146 14.2498 14.2505 13.9141 14.2505 13.5V12C14.2505 11.5858 14.5864 11.2501 15.0005 11.25ZM9.00152 2.25C9.17379 2.25008 9.33098 2.31047 9.45757 2.4082C9.46846 2.41662 9.47928 2.42548 9.4898 2.43457C9.51057 2.45253 9.53069 2.4711 9.54937 2.49121L12.9986 5.9375C13.2914 6.23022 13.2911 6.70507 12.9986 6.99805C12.7058 7.29087 12.231 7.29166 11.938 6.99902L9.75152 4.8125V12C9.75152 12.4141 9.41557 12.7498 9.00152 12.75C8.5873 12.75 8.25152 12.4142 8.25152 12V4.80859L6.06109 6.99902C5.76824 7.29152 5.29333 7.2915 5.00054 6.99902C4.70799 6.70607 4.70776 6.23024 5.00054 5.9375L8.46929 2.47168C8.49733 2.44367 8.52808 2.41913 8.55914 2.39648C8.6833 2.30539 8.83572 2.25 9.00152 2.25ZM9.00002 9.00024C9.41423 9.00024 9.75002 9.33603 9.75002 9.75024V11.2502C9.75002 11.6645 9.41423 12.0002 9.00002 12.0002C8.58581 12.0002 8.25002 11.6645 8.25002 11.2502V9.75024C8.25002 9.33603 8.58581 9.00024 9.00002 9.00024Z"
                                            fill="currentColor" />
                                    </svg>
                                    Upload File
                                </button>
                            </li>
                            <hr class="my-1 border-gray-200 dark:border-white/10" />
                            <li>
                                <button @click="selected = 'Web Search'; open = false"
                                    :class="selected === 'Web Search' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white' : 'text-gray-700 dark:text-gray-400'"
                                    class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                    role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                        fill="none">
                                        <path
                                            d="M9 1.31299C12.351 1.31309 15.1999 3.45834 16.252 6.44971C16.2604 6.46913 16.2656 6.49002 16.2725 6.51025C16.5398 7.29138 16.6875 8.1288 16.6875 9.00049C16.6875 9.87016 16.5396 10.7053 16.2734 11.4849C16.266 11.5072 16.2604 11.5299 16.251 11.5513C15.1988 14.5424 12.3508 16.6879 9 16.688C5.65294 16.6879 2.80794 14.547 1.75293 11.561C1.73348 11.5185 1.71928 11.4736 1.70801 11.4272C1.45419 10.6641 1.3135 9.8488 1.31348 9.00049C1.31348 8.15043 1.45419 7.33332 1.70898 6.56885C1.72014 6.52414 1.73415 6.48107 1.75293 6.43994C2.80784 3.45383 5.6528 1.3131 9 1.31299ZM7.06543 12.0005C7.19926 12.6035 7.36778 13.1461 7.56738 13.6079C7.81454 14.1796 8.0902 14.5954 8.35742 14.8569C8.58799 15.0824 8.7826 15.1665 8.93652 15.1841L9 15.188C9.16277 15.1879 9.37978 15.114 9.64355 14.856C9.9107 14.5944 10.1855 14.1793 10.4326 13.6079C10.6323 13.1461 10.8017 12.6036 10.9355 12.0005H7.06543ZM3.58984 12.0005C4.22079 13.136 5.1983 14.0514 6.37988 14.605C6.31422 14.4747 6.25091 14.3403 6.19141 14.2026C5.91403 13.5609 5.69075 12.8159 5.53125 12.0005H3.58984ZM12.4697 12.0005C12.3102 12.816 12.0871 13.5618 11.8096 14.2036C11.7501 14.3412 11.6858 14.4747 11.6201 14.605C12.802 14.0514 13.7801 13.1363 14.4111 12.0005H12.4697ZM2.99707 7.50049C2.87748 7.98075 2.81348 8.48323 2.81348 9.00049C2.8135 9.51775 2.87746 10.0202 2.99707 10.5005H5.31836C5.27421 10.0142 5.251 9.51231 5.25098 9.00049C5.25098 8.48881 5.27426 7.98664 5.31836 7.50049H2.99707ZM6.82812 7.50049C6.78005 7.97954 6.75098 8.48181 6.75098 9.00049C6.751 9.51932 6.78 10.0213 6.82812 10.5005H11.1729C11.221 10.0213 11.25 9.51931 11.25 9.00049C11.25 8.48183 11.2209 7.97952 11.1729 7.50049H6.82812ZM12.6826 7.50049C12.7267 7.98646 12.75 8.48803 12.75 8.99951C12.75 9.5115 12.7268 10.014 12.6826 10.5005H15.0029C15.1226 10.0202 15.1875 9.51779 15.1875 9.00049C15.1875 8.48323 15.1225 7.98075 15.0029 7.50049H12.6826ZM6.37891 3.39502C5.19743 3.94864 4.2207 4.86499 3.58984 6.00049H5.53125C5.69078 5.18469 5.91387 4.43931 6.19141 3.79736C6.25091 3.65972 6.31323 3.52531 6.37891 3.39502ZM9 2.81299C8.83723 2.81307 8.62101 2.88626 8.35742 3.14404C8.09021 3.40546 7.81453 3.82049 7.56738 4.39209C7.36766 4.85405 7.1993 5.39711 7.06543 6.00049H10.9355C10.8017 5.39719 10.6323 4.85402 10.4326 4.39209C10.1855 3.82079 9.91069 3.40544 9.64355 3.14404C9.37981 2.88606 9.16283 2.81306 9 2.81299ZM11.8096 3.79736C12.0871 4.43927 12.3102 5.18477 12.4697 6.00049H14.4111C13.7801 4.86459 12.802 3.94863 11.6201 3.39502C11.6859 3.52546 11.75 3.65955 11.8096 3.79736Z"
                                            fill="currentColor" />
                                    </svg>
                                    Web Search
                                </button>
                            </li>
                            <li>
                                <button @click="selected = 'Deep Search'; open = false"
                                    :class="selected === 'Deep Search' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white' : 'text-gray-700 dark:text-gray-400'"
                                    class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                    role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                        fill="none">
                                        <path
                                            d="M7.54877 9.3698L2.91377 10.3583C2.73386 10.3973 2.54581 10.364 2.3903 10.2655C2.23479 10.1669 2.12431 10.0112 2.08277 9.8318L1.68002 8.2193C1.63267 8.02502 1.65956 7.82006 1.75542 7.64457C1.85128 7.46907 2.00921 7.33569 2.19827 7.27055L12.3263 3.94055M10.17 8.81055L13.419 8.11755M12 15.7502L9.67127 11.0927M4.61847 6.47473L5.45397 9.81673M6 15.7502L8.32875 11.0927M12.3637 4.45521C12.2673 4.06932 12.3281 3.66094 12.5327 3.31988C12.7374 2.97881 13.0691 2.73298 13.455 2.63646L14.2725 2.43246C14.4653 2.38435 14.6693 2.41476 14.8398 2.51699C15.0102 2.61923 15.1331 2.78493 15.1815 2.97771L16.3177 7.52271C16.366 7.71565 16.3357 7.91989 16.2335 8.0905C16.1312 8.26111 15.9654 8.38412 15.7725 8.43246L14.955 8.63646C14.5691 8.73287 14.1607 8.67207 13.8196 8.46743C13.4786 8.26279 13.2327 7.93107 13.1362 7.54521L12.3637 4.45521ZM10.5 9.75012C10.5 10.5785 9.82843 11.2501 9 11.2501C8.17157 11.2501 7.5 10.5785 7.5 9.75012C7.5 8.92169 8.17157 8.25012 9 8.25012C9.82843 8.25012 10.5 8.92169 10.5 9.75012Z"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    Deep Search
                                </button>
                            </li>
                        </ul>
                    </div>
                    <!-- After Select -->
                    <div x-show="selected !== ''" x-cloak>
                        <button @click="selected = ''"
                            class="text-brand-500 group hover:bg-brand-500/10 flex h-9 items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium">
                            <!-- X icon (shown on hover) -->
                            <svg class="bg-brand-500/20 hidden size-5 shrink-0 rounded-full group-hover:block"
                                xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M5.06323 12.937L12.9367 5.06358M5.06323 5.06348L12.9367 12.9369" stroke="#465FFF"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <!-- Upload File icon -->
                            <svg x-show="selected === 'Upload File'" class="size-5 shrink-0 group-hover:hidden"
                                xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path
                                    d="M15.0005 11.25C15.4146 11.2502 15.7505 11.5859 15.7505 12V13.5C15.7505 14.7425 14.743 15.7498 13.5005 15.75H4.49957C3.25698 15.7499 2.24957 14.7426 2.24957 13.5V12C2.24957 11.5858 2.5854 11.2501 2.99957 11.25C3.41378 11.25 3.74957 11.5858 3.74957 12V13.5C3.74957 13.9142 4.0854 14.2499 4.49957 14.25H13.5005C13.9146 14.2498 14.2505 13.9141 14.2505 13.5V12C14.2505 11.5858 14.5864 11.2501 15.0005 11.25ZM9.00152 2.25C9.17379 2.25008 9.33098 2.31047 9.45757 2.4082C9.46846 2.41662 9.47928 2.42548 9.4898 2.43457C9.51057 2.45253 9.53069 2.4711 9.54937 2.49121L12.9986 5.9375C13.2914 6.23022 13.2911 6.70507 12.9986 6.99805C12.7058 7.29087 12.231 7.29166 11.938 6.99902L9.75152 4.8125V12C9.75152 12.4141 9.41557 12.7498 9.00152 12.75C8.5873 12.75 8.25152 12.4142 8.25152 12V4.80859L6.06109 6.99902C5.76824 7.29152 5.29333 7.2915 5.00054 6.99902C4.70799 6.70607 4.70776 6.23024 5.00054 5.9375L8.46929 2.47168C8.49733 2.44367 8.52808 2.41913 8.55914 2.39648C8.6833 2.30539 8.83572 2.25 9.00152 2.25ZM9.00002 9.00024C9.41423 9.00024 9.75002 9.33603 9.75002 9.75024V11.2502C9.75002 11.6645 9.41423 12.0002 9.00002 12.0002C8.58581 12.0002 8.25002 11.6645 8.25002 11.2502V9.75024C8.25002 9.33603 8.58581 9.00024 9.00002 9.00024Z"
                                    fill="#465FFF" />
                            </svg>
                            <!-- Web Search icon -->
                            <svg x-show="selected === 'Web Search'" class="size-5 shrink-0 group-hover:hidden"
                                xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path
                                    d="M9 1.31299C12.351 1.31309 15.1999 3.45834 16.252 6.44971C16.2604 6.46913 16.2656 6.49002 16.2725 6.51025C16.5398 7.29138 16.6875 8.1288 16.6875 9.00049C16.6875 9.87016 16.5396 10.7053 16.2734 11.4849C16.266 11.5072 16.2604 11.5299 16.251 11.5513C15.1988 14.5424 12.3508 16.6879 9 16.688C5.65294 16.6879 2.80794 14.547 1.75293 11.561C1.73348 11.5185 1.71928 11.4736 1.70801 11.4272C1.45419 10.6641 1.3135 9.8488 1.31348 9.00049C1.31348 8.15043 1.45419 7.33332 1.70898 6.56885C1.72014 6.52414 1.73415 6.48107 1.75293 6.43994C2.80784 3.45383 5.6528 1.3131 9 1.31299ZM7.06543 12.0005C7.19926 12.6035 7.36778 13.1461 7.56738 13.6079C7.81454 14.1796 8.0902 14.5954 8.35742 14.8569C8.58799 15.0824 8.7826 15.1665 8.93652 15.1841L9 15.188C9.16277 15.1879 9.37978 15.114 9.64355 14.856C9.9107 14.5944 10.1855 14.1793 10.4326 13.6079C10.6323 13.1461 10.8017 12.6036 10.9355 12.0005H7.06543ZM3.58984 12.0005C4.22079 13.136 5.1983 14.0514 6.37988 14.605C6.31422 14.4747 6.25091 14.3403 6.19141 14.2026C5.91403 13.5609 5.69075 12.8159 5.53125 12.0005H3.58984ZM12.4697 12.0005C12.3102 12.816 12.0871 13.5618 11.8096 14.2036C11.7501 14.3412 11.6858 14.4747 11.6201 14.605C12.802 14.0514 13.7801 13.1363 14.4111 12.0005H12.4697ZM2.99707 7.50049C2.87748 7.98075 2.81348 8.48323 2.81348 9.00049C2.8135 9.51775 2.87746 10.0202 2.99707 10.5005H5.31836C5.27421 10.0142 5.251 9.51231 5.25098 9.00049C5.25098 8.48881 5.27426 7.98664 5.31836 7.50049H2.99707ZM6.82812 7.50049C6.78005 7.97954 6.75098 8.48181 6.75098 9.00049C6.751 9.51932 6.78 10.0213 6.82812 10.5005H11.1729C11.221 10.0213 11.25 9.51931 11.25 9.00049C11.25 8.48183 11.2209 7.97952 11.1729 7.50049H6.82812ZM12.6826 7.50049C12.7267 7.98646 12.75 8.48803 12.75 8.99951C12.75 9.5115 12.7268 10.014 12.6826 10.5005H15.0029C15.1226 10.0202 15.1875 9.51779 15.1875 9.00049C15.1875 8.48323 15.1225 7.98075 15.0029 7.50049H12.6826ZM6.37891 3.39502C5.19743 3.94864 4.2207 4.86499 3.58984 6.00049H5.53125C5.69078 5.18469 5.91387 4.43931 6.19141 3.79736C6.25091 3.65972 6.31323 3.52531 6.37891 3.39502ZM9 2.81299C8.83723 2.81307 8.62101 2.88626 8.35742 3.14404C8.09021 3.40546 7.81453 3.82049 7.56738 4.39209C7.36766 4.85405 7.1993 5.39711 7.06543 6.00049H10.9355C10.8017 5.39719 10.6323 4.85402 10.4326 4.39209C10.1855 3.82079 9.91069 3.40544 9.64355 3.14404C9.37981 2.88606 9.16283 2.81306 9 2.81299ZM11.8096 3.79736C12.0871 4.43927 12.3102 5.18477 12.4697 6.00049H14.4111C13.7801 4.86459 12.802 3.94863 11.6201 3.39502C11.6859 3.52546 11.75 3.65955 11.8096 3.79736Z"
                                    fill="#465FFF" />
                            </svg>
                            <!-- Deep Search icon -->
                            <svg x-show="selected === 'Deep Search'" class="size-5 shrink-0 group-hover:hidden"
                                xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path
                                    d="M7.54877 9.3698L2.91377 10.3583C2.73386 10.3973 2.54581 10.364 2.3903 10.2655C2.23479 10.1669 2.12431 10.0112 2.08277 9.8318L1.68002 8.2193C1.63267 8.02502 1.65956 7.82006 1.75542 7.64457C1.85128 7.46907 2.00921 7.33569 2.19827 7.27055L12.3263 3.94055M10.17 8.81055L13.419 8.11755M12 15.7502L9.67127 11.0927M4.61847 6.47473L5.45397 9.81673M6 15.7502L8.32875 11.0927M12.3637 4.45521C12.2673 4.06932 12.3281 3.66094 12.5327 3.31988C12.7374 2.97881 13.0691 2.73298 13.455 2.63646L14.2725 2.43246C14.4653 2.38435 14.6693 2.41476 14.8398 2.51699C15.0102 2.61923 15.1331 2.78493 15.1815 2.97771L16.3177 7.52271C16.366 7.71565 16.3357 7.91989 16.2335 8.0905C16.1312 8.26111 15.9654 8.38412 15.7725 8.43246L14.955 8.63646C14.5691 8.73287 14.1607 8.67207 13.8196 8.46743C13.4786 8.26279 13.2327 7.93107 13.1362 7.54521L12.3637 4.45521ZM10.5 9.75012C10.5 10.5785 9.82843 11.2501 9 11.2501C8.17157 11.2501 7.5 10.5785 7.5 9.75012C7.5 8.92169 8.17157 8.25012 9 8.25012C9.82843 8.25012 10.5 8.92169 10.5 9.75012Z"
                                    stroke="#465FFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span x-text="selected" class="hidden sm:inline"></span>
                        </button>
                    </div>
                </div>
            @else
                <label
                    class="flex size-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-gray-200 text-sm text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-300">
                    <input type="file" class="sr-only" />
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M8.58191 1.54199C10.5624 1.54218 12.1688 3.14806 12.1688 5.12891V13.4541C12.1685 14.6513 11.1972 15.6221 9.99988 15.6221C8.80293 15.6217 7.83224 14.6511 7.83191 13.4541V5.12891C7.83191 4.71484 8.1679 4.37915 8.58191 4.37891C8.99612 4.37891 9.33191 4.71469 9.33191 5.12891V13.4541C9.33224 13.8226 9.63135 14.1217 9.99988 14.1221C10.3688 14.1221 10.6685 13.8229 10.6688 13.4541V12.0537C10.6687 12.0478 10.6679 12.0412 10.6678 12.0352L10.6688 5.12891C10.6688 3.97681 9.73429 3.04218 8.58191 3.04199C7.42969 3.04217 6.495 3.97664 6.495 5.12891V13.4541C6.49533 15.3893 8.06465 16.9587 9.99988 16.959C11.9353 16.959 13.5044 15.3895 13.5048 13.4541V7.96484C13.5049 7.55092 13.8409 7.21511 14.2548 7.21484C14.6689 7.21484 15.0046 7.55076 15.0048 7.96484V13.4541C15.0044 16.2179 12.7638 18.459 9.99988 18.459C7.23623 18.4587 4.99533 16.2177 4.995 13.4541V5.12891C4.995 3.14821 6.60126 1.54217 8.58191 1.54199Z"
                            fill="currentColor" />
                    </svg>
                </label>
            @endif

            <div class="flex items-center gap-2">
                <!-- Model Dropdown here -->
                <div class="relative" x-data="{ open: false, selected: 'Claude Sonnet 4.6' }" @click.away="open = false"
                    @keydown.escape.window="open = false">
                    <button @click="open = !open" :aria-expanded="open"
                        class="flex h-9 items-center gap-1.5 rounded-lg bg-transparent px-2.5 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-900">
                        <!-- Auto Select icon -->
                        <svg x-show="selected === 'Auto'"
                            xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 18 18" fill="none">
                            <path
                                d="M9.61054 2.0625L3.58887 10.5264H8.38943L8.38943 15.9375L14.4111 7.47361L9.61054 7.47361V2.0625Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        <!-- GPT icon -->
                        <img x-show="selected === 'GPT 4.5' || selected === 'GPT 5.5'"
                            src="./images/model/gpt-light.svg" width="18" height="18"
                            class="block dark:hidden" alt="gpt" />
                        <img x-show="selected === 'GPT 4.5' || selected === 'GPT 5.5'"
                            src="./images/model/gpt-dark.svg" width="18" height="18"
                            class="hidden dark:block" alt="gpt" />
                        <!-- Claude icon -->
                        <img x-show="selected === 'Claude Sonnet 4.5' || selected === 'Claude Sonnet 4.6'"
                            src="./images/model/claude.svg" width="18" height="18" alt="claude" />
                        <!-- Grok icon -->
                        <img x-show="selected === 'Grok 3.0' || selected === 'Grok 2.0'"
                            src="./images/model/grok-light.svg" width="18" height="18"
                            class="block dark:hidden" alt="grok" />
                        <img x-show="selected === 'Grok 3.0' || selected === 'Grok 2.0'"
                            src="./images/model/grok-dark.svg" width="18" height="18"
                            class="hidden dark:block" alt="grok" />
                        <span x-text="selected"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 18 18" fill="none" :class="open ? 'rotate-180' : ''"
                            class="transition-transform duration-150">
                            <path d="M4.3125 7.21875L9 11.9063L13.6875 7.21875" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <ul x-show="open" x-transition.origin.bottom.right x-cloak role="menu"
                        class="absolute right-0 bottom-full mb-2 min-w-[220px] space-y-0.5 rounded-xl bg-white p-1.5 shadow-md dark:bg-gray-900">
                        @foreach ($models as $model)
                            <li>
                                <button @click="selected = '{{ $model['value'] }}'; open = false"
                                    :class="selected === '{{ $model['value'] }}' ?
                                        'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-white' :
                                        'text-gray-700 dark:text-gray-400'"
                                    class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                    role="menuitem">
                                    @if ($model['icon_type'] === 'svg')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 18 18" fill="none">
                                            <path
                                                d="M9.61054 2.0625L3.58887 10.5264H8.38943L8.38943 15.9375L14.4111 7.47361L9.61054 7.47361V2.0625Z"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    @elseif ($model['icon_type'] === 'gpt')
                                        <img src="./images/model/gpt-light.svg" class="block dark:hidden"
                                            alt="gpt light" />
                                        <img src="./images/model/gpt-dark.svg" class="hidden dark:block"
                                            alt="gpt dark" />
                                    @elseif ($model['icon_type'] === 'claude')
                                        <img src="./images/model/claude.svg" alt="claude light" />
                                    @elseif ($model['icon_type'] === 'grok')
                                        <img src="./images/model/grok-light.svg" class="block dark:hidden"
                                            alt="grok dark" />
                                        <img src="./images/model/grok-dark.svg" class="hidden dark:block"
                                            alt="grok light" />
                                    @endif

                                    {{ $model['name'] }}

                                    @if ($model['is_new'])
                                        <span
                                            class="bg-success-50 dark:bg-success-500/10 dark:text-success-500 text-success-600 inline-flex h-5 items-center justify-center rounded-full px-2 text-xs">New</span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <!-- Send Button -->
                <button
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900 text-white transition hover:bg-gray-800 dark:bg-white/90 dark:text-gray-800 dark:hover:bg-gray-900 dark:hover:text-white/90">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 20 20" fill="none">
                        <path
                            d="M9.99996 15.2087C6.7783 15.2087 4.16663 12.597 4.16663 9.37533M9.99996 15.2087C13.2216 15.2087 15.8333 12.597 15.8333 9.37533M9.99996 15.2087V17.7087M8.33329 17.7087H11.6666M9.99999 12.7087C8.15905 12.7087 6.66668 11.2163 6.66668 9.37535V5.6253C6.66668 3.78437 8.15905 2.29199 9.99999 2.29199C11.8409 2.29199 13.3333 3.78437 13.3333 5.6253V9.37535C13.3333 11.2163 11.8409 12.7087 9.99999 12.7087Z"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
