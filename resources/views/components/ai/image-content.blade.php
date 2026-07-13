@php
    $messages = [
        [
            'type' => 'user',
            'text' => 'Minimalist building facade with vertical panels and greenery in a planter, set against a clear blue sky for a modern aesthetic.',
        ],
        [
            'type' => 'ai',
            'model' => 'Nano Banana 2.0',
            'model_icon' => './images/model/nanobanana.svg',
            'text' => 'I have generated Minimalist building facade with vertical panels and greenery in a planter, set against a clear blue sky for a modern aesthetic.',
            'images' => [
                './images/ai/img-1.png'
            ]
        ]
    ];

    $aspectRatios = [
        [
            'value' => '16:9',
            'svg' => '<svg class="shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M14.25 5.25H3.75C2.92157 5.25 2.25 5.92157 2.25 6.75V11.25C2.25 12.0784 2.92157 12.75 3.75 12.75H14.25C15.0784 12.75 15.75 12.0784 15.75 11.25V6.75C15.75 5.92157 15.0784 5.25 14.25 5.25Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        ],
        [
            'value' => '4:3',
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M13.5 4.5H4.5C3.67157 4.5 3 5.17157 3 6V12C3 12.8284 3.67157 13.5 4.5 13.5H13.5C14.3284 13.5 15 12.8284 15 12V6C15 5.17157 14.3284 4.5 13.5 4.5Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        ],
        [
            'value' => '1:1',
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M12.75 3.75H5.25C4.42157 3.75 3.75 4.42157 3.75 5.25V12.75C3.75 13.5784 4.42157 14.25 5.25 14.25H12.75C13.5784 14.25 14.25 13.5784 14.25 12.75V5.25C14.25 4.42157 13.5784 3.75 12.75 3.75Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        ],
        [
            'value' => '3:4',
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M13.5001 13.5L13.5001 4.5C13.5001 3.67157 12.8285 3 12.0001 3L6.00012 3C5.17169 3 4.50012 3.67157 4.50012 4.5L4.50012 13.5C4.50012 14.3284 5.17169 15 6.00012 15L12.0001 15C12.8285 15 13.5001 14.3284 13.5001 13.5Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        ],
        [
            'value' => '9:16',
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M12.75 14.249L12.75 3.74902C12.75 2.9206 12.0784 2.24902 11.25 2.24902L6.75 2.24902C5.92157 2.24902 5.25 2.9206 5.25 3.74902L5.25 14.249C5.25 15.0775 5.92157 15.749 6.75 15.749L11.25 15.749C12.0784 15.749 12.75 15.0775 12.75 14.249Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        ]
    ];

    $variants = ['1', '2', '3', '4'];

    $resolutions = ['2K', '4K', '8K', '16K'];

    $models = [
        [
            'name' => 'Auto',
            'type' => 'svg',
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9.61054 2.0625L3.58887 10.5264H8.38943L8.38943 15.9375L14.4111 7.47361L9.61054 7.47361V2.0625Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>',
        ],
        [
            'name' => 'Nano Banana Pro',
            'type' => 'img',
            'src' => './images/model/nanobanana.svg',
            'alt' => 'nano banana',
        ],
        [
            'name' => 'GPT 4.5 Image',
            'type' => 'img-themed',
            'src_light' => './images/model/gpt-light.svg',
            'src_dark' => './images/model/gpt-dark.svg',
            'alt' => 'gpt',
        ],
        [
            'name' => 'Seedream 5.0',
            'type' => 'img',
            'src' => './images/model/seedream.svg',
            'alt' => 'seedream',
        ],
        [
            'name' => 'FLUX.2 Pro',
            'type' => 'img-themed',
            'src_light' => './images/model/flux.svg',
            'src_dark' => './images/model/flux-dark.svg',
            'alt' => 'flux',
        ],
        [
            'name' => 'Grok Imagine',
            'type' => 'img-themed',
            'src_light' => './images/model/grok-light.svg',
            'src_dark' => './images/model/grok-dark.svg',
            'alt' => 'grok',
        ],
        [
            'name' => 'Ideogram',
            'type' => 'img-themed',
            'src_light' => './images/model/ideogram.svg',
            'src_dark' => './images/model/ideogram-dark.svg',
            'alt' => 'ideogram',
        ],
    ];
@endphp

<!-- Top  -->
<x-ai.ai-top-header text="Minimalist building facade" />

<!-- Msg Area -->
<div class="relative mx-auto flex max-w-[720px] flex-col">
    <div class="custom-scrollbar relative z-20 max-h-[55vh] space-y-7 overflow-y-auto pb-10 lg:pb-7">
        @foreach ($messages as $message)
            @if ($message['type'] === 'user')
                <!-- User Message -->
                <div class="flex justify-end" x-data="{ editing: false, text: @js($message['text']), draft: '' }">
                    <div :class="editing ? 'w-full' : ''">
                        <div class="ml-auto w-full max-w-[480px]" x-show="!editing">
                            <div class="shadow-theme-xs rounded-xl rounded-tr-xs bg-gray-100 px-4 py-3 dark:bg-gray-800">
                                <p class="text-left text-base leading-6 font-normal text-gray-800 dark:text-white/90"
                                    x-text="text"></p>
                            </div>
                            <div class="mt-2 flex justify-end">
                                <!-- Edit -->
                                <button @click="draft = text; editing = true" data-tooltip="Edit" data-tooltip-placement="top"
                                    data-tooltip-variant="plain"
                                    class="group flex size-8 items-center justify-center rounded-lg p-2 text-sm font-medium text-gray-800 hover:bg-gray-100 hover:text-gray-900 dark:border-white/5 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white/90">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                        fill="none">
                                        <path
                                            d="M9.90891 4.06479L11.9346 6.09047M12.5149 2.87346L13.1264 3.48492C13.5169 3.87545 13.5169 4.50861 13.1264 4.89914L6.26837 11.7572C6.15231 11.8732 6.00946 11.9589 5.85243 12.0067L3.17969 12.8202L3.99313 10.1474C4.04092 9.99041 4.12663 9.84756 4.2427 9.7315L11.1007 2.87346C11.4913 2.48294 12.1244 2.48294 12.5149 2.87346Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <!-- Copy Button -->
                                <button x-data="{ copied: false }" @click="copied = true; setTimeout(() => copied = false, 2000);"
                                    data-tooltip="Copy" data-tooltip-placement="top" data-tooltip-variant="plain"
                                    class="group flex size-8 items-center justify-center rounded-lg p-2 text-sm font-medium text-gray-800 hover:bg-gray-100 hover:text-gray-900 dark:border-white/5 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white/90">
                                    <!-- Copy Icon -->
                                    <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M11.3253 11.3301H5.67033C5.11804 11.3301 4.67033 10.8824 4.67033 10.3301V4.67513M11.3253 11.3301L11.3253 12.3327C11.3253 12.885 10.8776 13.3327 10.3253 13.3327H3.66772C3.11544 13.3327 2.66772 12.885 2.66772 12.3327V5.67513C2.66772 5.12285 3.11544 4.67513 3.66772 4.67513H4.67033M11.3253 11.3301H12.3321C12.8844 11.3301 13.3321 10.8824 13.3321 10.3301L13.3321 3.66699C13.3321 3.11471 12.8844 2.66699 12.3321 2.66699H5.67033C5.11804 2.66699 4.67033 3.11471 4.67033 3.66699V4.67513"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    <!-- Check Icon -->
                                    <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path d="M12.5 4.86133L6.2221 11.1392L3.5 8.41713" stroke="currentColor"
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Edit Container -->
                        <div x-show="editing"
                            class="w-full rounded-2xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-gray-900">
                            <!-- Textarea -->
                            <textarea x-model="draft" rows="3" @keydown.escape="editing = false"
                                class="w-full resize-none border-0 bg-transparent p-0 text-base leading-6 text-gray-800 [scrollbar-width:none] outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white/90 [&::-webkit-scrollbar]:hidden"></textarea>
                            <!-- Bottom Actions -->
                            <div class="mt-2 flex items-center justify-end gap-2">
                                <!-- Cancel -->
                                <button @click="editing = false"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Cancel
                                </button>

                                <!-- Send -->
                                <button @click="text = draft.trim() || text; editing = false"
                                    class="inline-flex h-9 items-center justify-center rounded-lg bg-gray-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-black dark:bg-white dark:text-gray-900">
                                    Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- AI Response -->
                <div class="flex justify-start">
                    <div>
                        <div class="max-w-[480px]">
                            <p class="mb-2 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                <img src="{{ $message['model_icon'] }}" alt="" />
                                {{ $message['model'] }}
                            </p>
                            <p class="mb-2 text-base leading-6 text-gray-800 dark:text-white/90">
                                {{ $message['text'] }}
                            </p>

                            @if (count($message['images']) === 1)
                                <div class="group relative w-full max-w-[300px] overflow-hidden rounded-xl">
                                    <img src="{{ $message['images'][0] }}"
                                        class="w-full rounded-xl border border-gray-100 object-cover dark:border-gray-700"
                                        alt="" />
                                    <!-- Hover Action Bar -->
                                    <div
                                        class="absolute right-0 bottom-0 left-0 flex translate-y-full items-center justify-between px-3 py-3 opacity-0 transition-all duration-300 ease-in-out group-hover:translate-y-0 group-hover:opacity-100">
                                        <div class="flex items-center gap-2">
                                            <!-- Edit Button -->
                                            <button data-tooltip="Edit" data-tooltip-placement="top" data-tooltip-variant="no-arrow"
                                                class="inline-flex size-9 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow backdrop-blur-sm transition hover:bg-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 16 16" fill="none">
                                                    <path
                                                        d="M9.90891 4.06528L11.9346 6.09096M12.5149 2.87395L13.1264 3.48541C13.5169 3.87594 13.5169 4.5091 13.1264 4.89962L6.26837 11.7577C6.15231 11.8737 6.00946 11.9594 5.85243 12.0072L3.17969 12.8207L3.99313 10.1479C4.04092 9.9909 4.12663 9.84805 4.2427 9.73198L11.1007 2.87395C11.4913 2.48342 12.1244 2.48342 12.5149 2.87395Z"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                            <!-- Regenerate Button -->
                                            <button data-tooltip="Regenerate" data-tooltip-placement="top"
                                                data-tooltip-variant="no-arrow"
                                                class="inline-flex size-9 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow backdrop-blur-sm transition hover:bg-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 16 16" fill="none">
                                                    <path
                                                        d="M13.6351 6.49025C13.3027 5.24998 12.5704 4.15402 11.5517 3.37235C10.5331 2.59068 9.28491 2.16699 8.00088 2.16699C6.71685 2.16699 5.4687 2.59068 4.45001 3.37235C3.43133 4.15402 2.69903 5.24998 2.3667 6.49025C2.29893 6.74315 2.25572 6.92051 2.22986 7.04262M2.36489 9.50909C2.69722 10.7494 3.42952 11.8453 4.4482 12.627C5.46689 13.4087 6.71504 13.8324 7.99907 13.8324C9.2831 13.8324 10.5312 13.4087 11.5499 12.627C12.5686 11.8453 13.3009 10.7494 13.6333 9.50909C13.7024 9.25111 13.7462 9.07175 13.7724 8.94947M1.13611 5.0999L2.22986 7.04262L2.29253 7.15392L4.34633 5.99744M11.6601 10.0022L13.7139 8.84571L13.7724 8.94947M14.8704 10.8997L13.7724 8.94947"
                                                        stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                            <!-- Copy Button -->
                                            <button x-data="{ copied: false }"
                                                @click="copied = true; setTimeout(() => copied = false, 2000)" data-tooltip="Copy"
                                                data-tooltip-placement="top" data-tooltip-variant="no-arrow"
                                                class="inline-flex size-9 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow backdrop-blur-sm transition hover:bg-white">
                                                <!-- Copy Icon -->
                                                <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" width="16"
                                                    height="16" viewBox="0 0 16 16" fill="none">
                                                    <path
                                                        d="M11.3254 11.3301H5.67045C5.11817 11.3301 4.67045 10.8824 4.67045 10.3301V4.67513M11.3254 11.3301L11.3254 12.3327C11.3254 12.885 10.8777 13.3327 10.3254 13.3327H3.66785C3.11556 13.3327 2.66785 12.885 2.66785 12.3327V5.67513C2.66785 5.12285 3.11556 4.67513 3.66785 4.67513H4.67045M11.3254 11.3301H12.3322C12.8845 11.3301 13.3322 10.8824 13.3322 10.3301L13.3322 3.66699C13.3322 3.11471 12.8845 2.66699 12.3322 2.66699H5.67045C5.11817 2.66699 4.67045 3.11471 4.67045 3.66699V4.67513"
                                                        stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <!-- Check Icon -->
                                                <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" width="16"
                                                    height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M12.5 4.86133L6.2221 11.1392L3.5 8.41713" stroke="currentColor"
                                                        stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Download Button -->
                                        <button data-tooltip="Download" data-tooltip-placement="top"
                                            data-tooltip-variant="no-arrow"
                                            class="inline-flex size-9 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow backdrop-blur-sm transition hover:bg-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 18 18" fill="none">
                                                <path
                                                    d="M15.0003 12V13.875C15.0003 14.4963 14.4966 15 13.8753 15H4.12463C3.50331 15 2.99963 14.4963 2.99963 13.875V12M9.00112 12L9.00112 3M5.53091 8.53155L8.99954 11.998L12.4684 8.53155"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-3.5 max-w-3xl">
                                    @foreach ($message['images'] as $img)
                                        <div class="relative group overflow-hidden rounded-xl">
                                            <img src="{{ $img }}" class="w-full rounded-xl border border-gray-100 object-cover dark:border-gray-700" alt="" />
                                            <div class="absolute right-0 bottom-0 left-0 flex translate-y-full items-center justify-end px-3 py-3 opacity-0 transition-all duration-300 ease-in-out group-hover:translate-y-0 group-hover:opacity-100">
                                                <button data-tooltip="Download" data-tooltip-placement="top" data-tooltip-variant="no-arrow"
                                                    class="inline-flex size-9 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow backdrop-blur-sm transition hover:bg-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                        <path d="M15.0003 12V13.875C15.0003 14.4963 14.4966 15 13.8753 15H4.12463C3.50331 15 2.99963 14.4963 2.99963 13.875V12M9.00112 12L9.00112 3M5.53091 8.53155L8.99954 11.998L12.4684 8.53155" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Fixed Input Wrapper -->
    <div class="fixed bottom-5 left-1/2 z-20 w-full -translate-x-1/2 transform px-4 sm:px-6 lg:bottom-10 lg:px-8"
        x-data="{
            open: false,
            activeMenu: null,
            aspectRatio: '3:4',
            variants: '1',
            resolution: '2K',
            model: 'Nano Banana Pro',
            activeDropdown: null
        }"
        @keydown.escape.window="open = false; activeMenu = null; activeDropdown = null">
        <!-- Container with max width -->
        <div
            class="mx-auto w-full max-w-[720px] rounded-2xl border border-gray-200 bg-white p-3 shadow-xs dark:border-gray-800 dark:bg-gray-800">
            <!-- Textarea -->
            <textarea placeholder="Type your prompt here..."
                class="h-20 w-full resize-none border-none bg-transparent p-0 px-2 font-normal text-gray-800 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>

            <!-- Bottom Section -->
            <div class="flex items-center justify-between pt-2">
                <div class="flex gap-2">
                    <label data-tooltip="Attachment" data-tooltip-placement="top" data-tooltip-variant="no-arrow"
                        class="flex size-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-gray-200 text-sm text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-300">
                        <input type="file" class="sr-only" />
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                            fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M8.58191 1.54199C10.5624 1.54218 12.1688 3.14806 12.1688 5.12891V13.4541C12.1685 14.6513 11.1972 15.6221 9.99988 15.6221C8.80293 15.6217 7.83224 14.6511 7.83191 13.4541V5.12891C7.83191 4.71484 8.1679 4.37915 8.58191 4.37891C8.99612 4.37891 9.33191 4.71469 9.33191 5.12891V13.4541C9.33224 13.8226 9.63135 14.1217 9.99988 14.1221C10.3688 14.1221 10.6685 13.8229 10.6688 13.4541V12.0537C10.6687 12.0478 10.6679 12.0412 10.6678 12.0352L10.6688 5.12891C10.6688 3.97681 9.73429 3.04218 8.58191 3.04199C7.42969 3.04217 6.495 3.97664 6.495 5.12891V13.4541C6.49533 15.3893 8.06465 16.9587 9.99988 16.959C11.9353 16.959 13.5044 15.3895 13.5048 13.4541V7.96484C13.5049 7.55092 13.8409 7.21511 14.2548 7.21484C14.6689 7.21484 15.0046 7.55076 15.0048 7.96484V13.4541C15.0044 16.2179 12.7638 18.459 9.99988 18.459C7.23623 18.4587 4.99533 16.2177 4.995 13.4541V5.12891C4.995 3.14821 6.60126 1.54217 8.58191 1.54199Z"
                                fill="currentColor" />
                        </svg>
                    </label>
                    <!-- Mobile Actions -->
                    <div class="relative block sm:hidden" @click.away="open = false; activeMenu = null">
                        <button @click="open = !open"
                            class="flex size-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-gray-200 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 20 20" fill="none">
                                <path
                                    d="M14.6534 5.90384C14.6534 4.48402 13.5024 3.33301 12.0826 3.33301C10.6628 3.33301 9.51176 4.48403 9.51174 5.90384M14.6534 5.90384C14.6534 7.32367 13.5024 8.47467 12.0826 8.47467C10.6627 8.47467 9.51174 7.32367 9.51174 5.90384M14.6534 5.90384L17.7084 5.90381M9.51174 5.90384L2.29169 5.90381M5.34664 14.0955C5.34664 12.6757 6.49764 11.5247 7.91747 11.5247C9.3373 11.5247 10.4883 12.6757 10.4883 14.0955M5.34664 14.0955C5.34664 15.5153 6.49764 16.6663 7.91747 16.6663C9.3373 16.6663 10.4883 15.5153 10.4883 14.0955M5.34664 14.0955L2.29169 14.0955M10.4883 14.0955L17.7084 14.0955"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                        <!-- Mobile Settings Panel -->
                        <div x-show="open" x-cloak
                            class="shadow-theme-md absolute bottom-full left-0 mb-2 w-56 overflow-hidden rounded-2xl bg-white p-1.5 dark:bg-gray-900">
                            <!-- Main Menu -->
                            <div x-show="activeMenu === null">
                                <button @click="activeMenu = 'aspect-ratio'"
                                    class="flex w-full items-center justify-between gap-2 rounded-lg px-3.5 py-3 text-gray-800 hover:bg-gray-100 dark:text-white/90 dark:hover:bg-gray-800">
                                    <div class="flex items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 20 20" fill="none">
                                            <path
                                                d="M15.0001 14.9997L15.0001 4.99967C15.0001 4.0792 14.2539 3.33301 13.3335 3.33301L6.66679 3.33301C5.74631 3.33301 5.00012 4.0792 5.00012 4.99967L5.00012 14.9997C5.00012 15.9201 5.74631 16.6663 6.66679 16.6663L13.3335 16.6663C14.2539 16.6663 15.0001 15.9201 15.0001 14.9997Z"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <span class="text-sm font-medium">Aspect Ratio</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span x-text="aspectRatio" class="text-sm"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 16 16" fill="none">
                                            <path d="M5.91669 12.1663L10.0834 7.99967L5.91669 3.83301"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </button>

                                <button @click="activeMenu = 'variants'"
                                    class="flex w-full items-center justify-between gap-2 rounded-lg px-3.5 py-3 text-gray-800 hover:bg-gray-100 dark:text-white/90 dark:hover:bg-gray-800">
                                    <div class="flex items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 20 20" fill="none">
                                            <path
                                                d="M4.18733 11.3287L2.61708 11.8916C2.19187 12.044 2.19187 12.6453 2.61708 12.7978L9.51246 15.2695C9.82748 15.3825 10.1719 15.3825 10.487 15.2695L17.3824 12.7978C17.8076 12.6453 17.8076 12.044 17.3824 11.8916L15.8194 11.3313M9.51246 4.72923L2.61708 7.20101C2.19187 7.35343 2.19187 7.95477 2.61708 8.10719L9.51246 10.579C9.82748 10.6919 10.1719 10.6919 10.487 10.579L17.3824 8.1072C17.8076 7.95477 17.8076 7.35343 17.3824 7.20101L10.487 4.72923C10.172 4.6163 9.82748 4.6163 9.51246 4.72923Z"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <span class="text-sm font-medium">Variants</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span x-text="variants" class="text-sm"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 16 16" fill="none">
                                            <path d="M5.91669 12.1663L10.0834 7.99967L5.91669 3.83301"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </button>

                                <button @click="activeMenu = 'resolution'"
                                    class="flex w-full items-center justify-between gap-2 rounded-lg px-3.5 py-3 text-gray-800 hover:bg-gray-100 dark:text-white/90 dark:hover:bg-gray-800">
                                    <div class="flex items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 20 20" fill="none">
                                            <path
                                                d="M14.0339 3.33301H5.96607C5.50036 3.33301 5.07326 3.59191 4.85784 4.0048L3.09863 7.37662C2.86279 7.82865 2.92512 8.37862 3.25616 8.7664L10 16.6663L16.7439 8.7664C17.0749 8.37862 17.1372 7.82865 16.9014 7.37662L15.1422 4.0048C14.9268 3.59191 14.4997 3.33301 14.0339 3.33301Z"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path d="M7.08334 7.28711L12.9167 7.28711" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                        <span class="text-sm font-medium">Resolution</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span x-text="resolution" class="text-sm"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 16 16" fill="none">
                                            <path d="M5.91669 12.1663L10.0834 7.99967L5.91669 3.83301"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                            <!-- Aspect Ratio Sub-menu -->
                            <div x-show="activeMenu === 'aspect-ratio'">
                                <div
                                    class="flex items-center gap-1.5 border-b border-gray-100 px-2 py-2 dark:border-gray-800">
                                    <button @click="activeMenu = null"
                                        class="flex items-center justify-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 16 16" fill="none">
                                            <path d="M10.0833 12.1663L5.91663 7.99967L10.0833 3.83301"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">Aspect Ratio</span>
                                </div>
                                <ul class="space-y-0.5 p-1.5">
                                    @foreach ($aspectRatios as $ar)
                                        <li>
                                            <button @click="aspectRatio = '{{ $ar['value'] }}'; open = false; activeMenu = null"
                                                :class="aspectRatio === '{{ $ar['value'] }}' ?
                                                    'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' :
                                                    'text-gray-700 dark:text-gray-400'"
                                                class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white">
                                                {!! $ar['svg'] !!}
                                                {{ $ar['value'] }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <!-- Variants Sub-menu -->
                            <div x-show="activeMenu === 'variants'">
                                <div
                                    class="flex items-center gap-1.5 border-b border-gray-100 px-2 py-2 dark:border-gray-800">
                                    <button @click="activeMenu = null"
                                        class="flex items-center justify-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 16 16" fill="none">
                                            <path d="M10.0833 12.1663L5.91663 7.99967L10.0833 3.83301"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">Variants</span>
                                </div>
                                <ul class="space-y-0.5 p-1.5">
                                    @foreach ($variants as $v)
                                        <li>
                                            <button @click="variants = '{{ $v }}'; open = false; activeMenu = null"
                                                :class="variants === '{{ $v }}' ?
                                                    'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' :
                                                    'text-gray-700 dark:text-gray-400'"
                                                class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white">
                                                {{ $v }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <!-- Resolution Sub-menu -->
                            <div x-show="activeMenu === 'resolution'">
                                <div
                                    class="flex items-center gap-1.5 border-b border-gray-100 px-2 py-2 dark:border-gray-800">
                                    <button @click="activeMenu = null"
                                        class="flex items-center justify-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 16 16" fill="none">
                                            <path d="M10.0833 12.1663L5.91663 7.99967L10.0833 3.83301"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">Resolution</span>
                                </div>
                                <ul class="space-y-0.5 p-1.5">
                                    @foreach ($resolutions as $r)
                                        <li>
                                            <button @click="resolution = '{{ $r }}'; open = false; activeMenu = null"
                                                :class="resolution === '{{ $r }}' ?
                                                    'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' :
                                                    'text-gray-700 dark:text-gray-400'"
                                                class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white">
                                                {{ $r }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="hidden gap-2 sm:flex">
                        <!-- Aspect Ratio Dropdown -->
                        <div class="relative" @click.away="if (activeDropdown === 'aspect-ratio') activeDropdown = null">
                            <button data-tooltip="Aspect Ratio" data-tooltip-placement="top"
                                data-tooltip-variant="no-arrow" @click="activeDropdown = activeDropdown === 'aspect-ratio' ? null : 'aspect-ratio'" :aria-expanded="activeDropdown === 'aspect-ratio'"
                                class="flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-gray-200 py-2 pr-3 pl-2.5 text-sm text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 20 20" fill="none">
                                    <path
                                        d="M15.0001 14.9997L15.0001 4.99967C15.0001 4.0792 14.2539 3.33301 13.3335 3.33301L6.66679 3.33301C5.74631 3.33301 5.00012 4.0792 5.00012 4.99967L5.00012 14.9997C5.00012 15.9201 5.74631 16.6663 6.66679 16.6663L13.3335 16.6663C14.2539 16.6663 15.0001 15.9201 15.0001 14.9997Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span x-text="aspectRatio"></span>
                            </button>
                            <ul x-show="activeDropdown === 'aspect-ratio'" x-transition.origin.bottom.left x-cloak role="menu"
                                class="shadow-theme-md absolute bottom-full left-0 mb-2 min-w-[106px] space-y-0.5 rounded-xl bg-white p-1.5 dark:bg-gray-900">
                                @foreach ($aspectRatios as $ar)
                                    <li>
                                        <button @click="aspectRatio = '{{ $ar['value'] }}'; activeDropdown = null"
                                            :class="aspectRatio === '{{ $ar['value'] }}' ?
                                                'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white' :
                                                'text-gray-700 dark:text-gray-400'"
                                            class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white"
                                            role="menuitem">
                                            {!! $ar['svg'] !!}
                                            {{ $ar['value'] }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <!-- Variant Dropdown -->
                        <div class="relative" @click.away="if (activeDropdown === 'variants') activeDropdown = null">
                            <button data-tooltip="Variants" data-tooltip-placement="top"
                                data-tooltip-variant="no-arrow" @click="activeDropdown = activeDropdown === 'variants' ? null : 'variants'" :aria-expanded="activeDropdown === 'variants'"
                                class="flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-gray-200 py-2 pr-3 pl-2.5 text-sm text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 20 20" fill="none">
                                    <path
                                        d="M4.18733 11.3287L2.61708 11.8916C2.19187 12.044 2.19187 12.6453 2.61708 12.7978L9.51246 15.2695C9.82748 15.3825 10.1719 15.3825 10.487 15.2695L17.3824 12.7978C17.8076 12.6453 17.8076 12.044 17.3824 11.8916L15.8194 11.3313M9.51246 4.72923L2.61708 7.20101C2.19187 7.35343 2.19187 7.95477 2.61708 8.10719L9.51246 10.579C9.82748 10.6919 10.1719 10.6919 10.487 10.579L17.3824 8.1072C17.8076 7.95477 17.8076 7.35343 17.3824 7.20101L10.487 4.72923C10.172 4.6163 9.82748 4.6163 9.51246 4.72923Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span x-text="variants"></span>
                            </button>
                            <ul x-show="activeDropdown === 'variants'" x-transition.origin.bottom.left x-cloak role="menu"
                                class="shadow-theme-md absolute bottom-full left-0 mb-2 min-w-[74px] space-y-0.5 rounded-xl bg-white p-1.5 dark:bg-gray-900">
                                @foreach ($variants as $v)
                                    <li>
                                        <button @click="variants = '{{ $v }}'; activeDropdown = null"
                                            :class="variants === '{{ $v }}' ?
                                                'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white' :
                                                'text-gray-700 dark:text-gray-400'"
                                            class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white"
                                            role="menuitem">
                                            {{ $v }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <!-- Resolution Dropdown -->
                        <div class="relative" @click.away="if (activeDropdown === 'resolution') activeDropdown = null">
                            <button data-tooltip="Resolution" data-tooltip-placement="top"
                                data-tooltip-variant="no-arrow" @click="activeDropdown = activeDropdown === 'resolution' ? null : 'resolution'" :aria-expanded="activeDropdown === 'resolution'"
                                class="flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-gray-200 py-2 pr-3 pl-2.5 text-sm text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 20 20" fill="none">
                                    <path
                                        d="M14.0339 3.33301H5.96607C5.50036 3.33301 5.07326 3.59191 4.85784 4.0048L3.09863 7.37662C2.86279 7.82865 2.92512 8.37862 3.25616 8.7664L10 16.6663L16.7439 8.7664C17.0749 8.37862 17.1372 7.82865 16.9014 7.37662L15.1422 4.0048C14.9268 3.59191 14.4997 3.33301 14.0339 3.33301Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M7.08334 7.28711L12.9167 7.28711" stroke="currentColor"
                                        stroke-width="1.5" stroke-linecap="round" />
                                </svg>
                                <span x-text="resolution"></span>
                            </button>
                            <ul x-show="activeDropdown === 'resolution'" x-transition.origin.bottom.left x-cloak role="menu"
                                class="shadow-theme-md absolute bottom-full left-0 mb-2 min-w-[86px] space-y-0.5 rounded-xl bg-white p-1.5 dark:bg-gray-900">
                                @foreach ($resolutions as $r)
                                    <li>
                                        <button @click="resolution = '{{ $r }}'; activeDropdown = null"
                                            :class="resolution === '{{ $r }}' ?
                                                'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white' :
                                                'text-gray-700 dark:text-gray-400'"
                                            class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white"
                                            role="menuitem">
                                            {{ $r }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Model -->
                <div class="flex items-center gap-2">
                    <!-- Model Dropdown here -->
                    <div class="relative" @click.away="if (activeDropdown === 'model') activeDropdown = null">
                        <button @click="activeDropdown = activeDropdown === 'model' ? null : 'model'" :aria-expanded="activeDropdown === 'model'"
                            class="flex h-9 items-center gap-1.5 rounded-lg px-2.5 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-900">
                            
                            @foreach ($models as $m)
                                @if ($m['type'] === 'svg')
                                    <div x-show="model === '{{ $m['name'] }}'">
                                        {!! $m['svg'] !!}
                                    </div>
                                @elseif ($m['type'] === 'img')
                                    <img x-show="model === '{{ $m['name'] }}'" src="{{ $m['src'] }}" width="18" height="18" alt="{{ $m['alt'] }}" />
                                @elseif ($m['type'] === 'img-themed')
                                    <img x-show="model === '{{ $m['name'] }}'" src="{{ $m['src_light'] }}" width="18" height="18" class="block dark:hidden" alt="{{ $m['alt'] }}" />
                                    <img x-show="model === '{{ $m['name'] }}'" src="{{ $m['src_dark'] }}" width="18" height="18" class="hidden dark:block" alt="{{ $m['alt'] }}" />
                                @endif
                            @endforeach
                            
                            <span x-text="model"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 18 18" fill="none" :class="activeDropdown === 'model' ? 'rotate-180' : ''"
                                class="transition-transform duration-150">
                                <path d="M4.3125 7.21875L9 11.9063L13.6875 7.21875" stroke="currentColor"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <ul x-show="activeDropdown === 'model'" x-transition.origin.bottom.right x-cloak role="menu"
                            class="absolute right-0 bottom-full mb-2 min-w-[220px] space-y-0.5 rounded-xl bg-white p-1.5 shadow-md dark:bg-gray-900">
                            @foreach ($models as $m)
                                <li>
                                    <button @click="model = '{{ $m['name'] }}'; activeDropdown = null"
                                        :class="model === '{{ $m['name'] }}' ?
                                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white' :
                                            'text-gray-700 dark:text-gray-400'"
                                        class="flex w-full items-center gap-2 rounded-lg px-1.5 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white"
                                        role="menuitem">
                                        @if ($m['type'] === 'svg')
                                            {!! $m['svg'] !!}
                                        @elseif ($m['type'] === 'img')
                                            <img src="{{ $m['src'] }}" alt="{{ $m['alt'] }}" />
                                        @elseif ($m['type'] === 'img-themed')
                                            <img src="{{ $m['src_light'] }}" class="block dark:hidden" alt="{{ $m['alt'] }} light" />
                                            <img src="{{ $m['src_dark'] }}" class="hidden dark:block" alt="{{ $m['alt'] }} dark" />
                                        @endif
                                        {{ $m['name'] }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <!-- Send Button -->
                    <button
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900 text-white transition hover:bg-gray-800 dark:bg-white/90 dark:text-gray-800 dark:hover:bg-gray-900 dark:hover:text-white/90">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                            fill="none">
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
</div>
