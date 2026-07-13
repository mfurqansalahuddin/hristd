@php
    $messages = [
        [
            'type' => 'user',
            'text' => "Can you generate some random, creative, and engaging placeholder text for me? It doesn't need to follow any specific structure—just something fun or interesting to fill space temporarily.",
        ],
        [
            'type' => 'ai',
            'model' => 'Claude Sonnet 4.6',
            'paragraphs' => [
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus et varius tortor. Aenean dui magna, vehicula in lacinia non, euismod sed odio. Aliquam erat volutpat.",
            ],
        ],
        [
            'type' => 'user',
            'text' => "I'm looking for a block of random, imaginative text—something quirky or unexpected to use as placeholder content.",
        ],
        [
            'type' => 'ai',
            'model' => 'Claude Sonnet 4.6',
            'paragraphs' => [
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus et varius tortor. Aenean dui magna, vehicula in lacinia non, euismod sed odio. Aliquam erat volutpat.",
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus et varius tortor. Aenean dui magna, vehicula in lacinia non, euismod sed odio. Aliquam erat volutpat.",
            ],
        ],
    ];
@endphp

<!-- Top  -->
<x-ai.ai-top-header text="Generate responsive login" />

<!-- Msg Area -->
<div class="relative mx-auto flex max-w-[720px] flex-col">
    <div class="custom-scrollbar relative z-20 max-h-[55vh] flex-1 space-y-4 overflow-y-auto pb-10 lg:pb-7">
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                    fill="none">
                                    <g clip-path="url(#clip0_6682_9259)">
                                        <path
                                            d="M3.46203 10.4293L6.40036 8.7869L6.44953 8.64376L6.40036 8.56465H6.25666L5.76505 8.53451L4.086 8.48931L2.63006 8.42904L1.21951 8.3537L0.864035 8.27836L0.53125 7.8414L0.565285 7.62292L0.864035 7.42327L1.29136 7.46094L2.23677 7.52498L3.65489 7.62292L4.6835 7.68319L6.2075 7.8414H6.44953L6.48356 7.74346L6.40036 7.68319L6.33608 7.62292L4.8688 6.63221L3.28051 5.58501L2.44854 4.9823L1.99853 4.67718L1.77163 4.39089L1.67331 3.76558L2.08172 3.31731L2.63006 3.35498L2.76998 3.39265L3.32589 3.81832L4.51332 4.73368L6.0638 5.87129L6.2907 6.05964L6.38146 5.9956L6.3928 5.9504L6.2907 5.78089L5.44739 4.26281L4.54736 2.71837L4.1465 2.07799L4.04062 1.69377C4.0028 1.53556 3.97633 1.40371 3.97633 1.24174L4.44147 0.612658L4.69862 0.529785L5.31881 0.612658L5.57975 0.838674L5.96547 1.71637L6.58945 3.09883L7.55755 4.97853L7.84117 5.53604L7.99244 6.05211L8.04916 6.21032H8.14748V6.11991L8.2269 5.0614L8.37438 3.76181L8.51809 2.08929L8.56725 1.61843L8.80171 1.05339L9.26685 0.748267L9.62989 0.921546L9.92864 1.34721L9.88704 1.6222L9.7093 2.77111L9.36139 4.5717L9.13449 5.77712H9.26685L9.41812 5.62644L10.0307 4.81655L11.0594 3.5358L11.5131 3.02726L12.0426 2.46599L12.3829 2.19854H13.0258L13.4985 2.89919L13.2867 3.62244L12.625 4.45869L12.0766 5.16688L11.29 6.22162L10.7984 7.06541L10.8438 7.13322L10.961 7.12192L12.7384 6.74522L13.6989 6.57194L14.8448 6.37606L15.3629 6.61715L15.4196 6.862L15.2154 7.363L13.9901 7.66435L12.5531 7.95064L10.4127 8.45541L10.3862 8.47424L10.4165 8.51191L11.3808 8.60232L11.793 8.62492H12.8027L14.6822 8.7643L15.1738 9.08825L15.4688 9.48378L15.4196 9.78514L14.6633 10.1694L13.6422 9.92828L11.2598 9.36324L10.4429 9.15983H10.3295V9.22763L11.0102 9.89061L12.2581 11.0132L13.82 12.4597L13.8994 12.8175L13.6989 13.1L13.4872 13.0699L12.1144 12.0415L11.585 11.5782L10.3862 10.5724H10.3068V10.6779L10.5829 11.081L12.0426 13.2658L12.1182 13.9363L12.0123 14.1548L11.6342 14.2866L11.2182 14.2113L10.3635 13.0172L9.4824 11.6724L8.77146 10.467L8.68448 10.5159L8.26472 15.0174L8.06807 15.2472L7.61427 15.4205L7.23611 15.1342L7.03568 14.6708L7.23611 13.7555L7.47813 12.5614L7.67478 11.6121L7.85252 10.433L7.9584 10.0413L7.95084 10.0149L7.86386 10.0262L6.97139 11.2467L5.61378 13.0737L4.53979 14.2188L4.28264 14.3205L3.83641 14.0907L3.87801 13.6801L4.12759 13.3148L5.61378 11.4313L6.51003 10.2635L7.08862 9.58926L7.08484 9.49132H7.05081L3.10277 12.0453L2.39938 12.1357L2.09685 11.8532L2.13467 11.3898L2.27837 11.2392L3.46581 10.4255L3.46203 10.4293Z"
                                            fill="#D97757" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_6682_9259">
                                            <rect width="16" height="16" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                {{ $message['model'] }}
                            </p>

                            @foreach ($message['paragraphs'] as $index => $paragraph)
                                @php
                                    $paragraphClass = 'text-base leading-6 text-gray-800 dark:text-white/90';
                                    if (count($message['paragraphs']) === 1) {
                                        $paragraphClass .= ' mb-2';
                                    } elseif ($index < count($message['paragraphs']) - 1) {
                                        $paragraphClass .= ' mb-5';
                                    }
                                @endphp
                                <p class="{{ $paragraphClass }}">
                                    {{ $paragraph }}
                                </p>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <div class="relative inline-flex">
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
                                <!-- Like Button -->
                                <button x-data="{ liked: false }" @click="liked = !liked" data-tooltip="Like"
                                    data-tooltip-placement="top" data-tooltip-variant="plain"
                                    class="group flex size-8 items-center justify-center rounded-lg p-2 text-sm font-medium hover:bg-gray-100 dark:border-white/5 dark:bg-gray-900 dark:hover:bg-gray-800">
                                    <svg :class="liked ? 'text-brand-500' : 'text-gray-800 dark:text-gray-400'"
                                        class="transition-colors duration-200 dark:group-hover:text-white/90"
                                        xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13"
                                        fill="none">
                                        <path
                                            d="M2.93333 11.2852H8.9997C9.4712 11.2852 9.87865 10.9558 9.97748 10.4948L11.105 5.23518C11.2385 4.61265 10.7639 4.02557 10.1272 4.02557H7.85181C7.52082 4.02557 7.23994 3.78272 7.19214 3.45519L6.90027 1.45566C6.82856 0.964374 6.40725 0.600098 5.91076 0.600098H5.09327C4.90917 0.600098 4.75993 0.749336 4.75993 0.933431V2.6206C4.75993 3.36148 4.35037 4.04166 3.69557 4.38825L2.93333 4.79171M2.93333 4.31869H0.933332C0.749237 4.31869 0.599998 4.46792 0.599998 4.65202V11.2015C0.599998 11.3856 0.749237 11.5348 0.933332 11.5348H2.93333V4.31869Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <!-- Dislike Button -->
                                <button x-data="{ disliked: false }" @click="disliked = !disliked" data-tooltip="Dislike"
                                    data-tooltip-placement="top" data-tooltip-variant="plain"
                                    class="group flex size-8 items-center justify-center rounded-lg p-2 text-sm font-medium hover:bg-gray-100 dark:border-white/5 dark:bg-gray-900 dark:hover:bg-gray-800">
                                    <svg :class="disliked ? 'text-brand-500' : 'text-gray-800 dark:text-gray-400'"
                                        class="transition-colors duration-200 dark:group-hover:text-white/90"
                                        xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                        fill="none">
                                        <path
                                            d="M5.06923 2.78237H11.1356C11.6071 2.78237 12.0145 3.11173 12.1134 3.57276L13.2409 8.83236C13.3744 9.4549 12.8998 10.042 12.2631 10.042H9.98771C9.65672 10.042 9.37585 10.2848 9.32804 10.6124L9.03618 12.6119C8.96447 13.1032 8.54316 13.4674 8.04666 13.4674H7.22917C7.04507 13.4674 6.89583 13.3182 6.89583 13.1341V11.4469C6.89583 10.7061 6.48628 10.0259 5.83147 9.67929L5.06923 9.27583M5.06923 9.74886H3.06923C2.88514 9.74886 2.7359 9.59962 2.7359 9.41553V2.86605C2.7359 2.68195 2.88514 2.53271 3.06923 2.53271H5.06923V9.74886Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <!-- Regenerate Button -->
                                <button data-tooltip="Regenerate" data-tooltip-placement="top" data-tooltip-variant="plain"
                                    class="group flex size-8 items-center justify-center rounded-lg p-2 text-sm font-medium text-gray-800 hover:bg-gray-100 hover:text-gray-900 dark:border-white/5 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white/90">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M13.635 6.49025C13.3027 5.24998 12.5704 4.15402 11.5517 3.37235C10.533 2.59069 9.28486 2.16699 8.00086 2.16699C6.7168 2.16699 5.46866 2.59069 4.44997 3.37235C3.43128 4.15402 2.69898 5.24998 2.36665 6.49025C2.29889 6.74317 2.25568 6.92051 2.22982 7.04264M1.13606 5.0999L2.22982 7.04264L2.29248 7.15391L4.34628 5.99745M2.36484 9.50911C2.69718 10.7494 3.42947 11.8453 4.44816 12.627C5.46685 13.4086 6.715 13.8324 7.999 13.8324C9.28306 13.8324 10.5312 13.4086 11.5499 12.627C12.5686 11.8453 13.3009 10.7494 13.6332 9.50911C13.7023 9.25111 13.7462 9.07177 13.7723 8.94944M14.8703 10.8997L13.7723 8.94944L13.7139 8.84571L11.6601 10.0022"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <!-- Fixed Input Wrapper -->
    <x-ai.generator-input type="text" placeholder="Type your prompt here..." />
</div>
