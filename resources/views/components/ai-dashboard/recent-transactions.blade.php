@props(['transactions' => null])
@php
    if (!$transactions) {
        $allTransactions = [
            [
                'user' => 'John Doe',
                'email' => 'johndeo@gmail.com',
                'package' => 'Starter - Monthly',
                'price' => '$20.00',
                'date' => '28 Feb, 2029',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
            [
                'user' => 'kierra@gmail.com',
                'email' => 'kierra@gmail.com',
                'package' => 'Growth - Yearly',
                'price' => '$249.00',
                'date' => '28 Jan, 2029',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
            [
                'user' => 'Emerson Workman',
                'email' => 'emerson@gmail.com',
                'package' => 'Premium - Monthly',
                'price' => '$199.00',
                'date' => '5 Jan, 2029',
                'status' => 'Expired',
                'status_class' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            ],
            [
                'user' => 'Chance Philips',
                'email' => 'chance@gmail.com',
                'package' => 'Growth - Yearly',
                'price' => '$249.00',
                'date' => '12 Dec, 2028',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
            [
                'user' => 'Terry Geidt',
                'email' => 'terry@gmail.com',
                'package' => 'Starter - Monthly',
                'price' => '$20.00',
                'date' => '25 Nov, 2028',
                'status' => 'Expired',
                'status_class' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            ],
            [
                'user' => 'Willie Gentry',
                'email' => 'willie@gmail.com',
                'package' => 'Starter - Monthly',
                'price' => '$20.00',
                'date' => '10 Nov, 2028',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
            [
                'user' => 'James Bond',
                'email' => '007@gmail.com',
                'package' => 'Premium - Monthly',
                'price' => '$199.00',
                'date' => '1 Nov, 2028',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
            [
                'user' => 'Linda Taylor',
                'email' => 'linda@gmail.com',
                'package' => 'Growth - Yearly',
                'price' => '$249.00',
                'date' => '25 Oct, 2028',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
            [
                'user' => 'Michael Smith',
                'email' => 'michael@gmail.com',
                'package' => 'Starter - Monthly',
                'price' => '$20.00',
                'date' => '15 Oct, 2028',
                'status' => 'Expired',
                'status_class' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            ],
            [
                'user' => 'Sarah Connor',
                'email' => 'sarah@gmail.com',
                'package' => 'Premium - Yearly',
                'price' => '$999.00',
                'date' => '10 Oct, 2028',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
            [
                'user' => 'John Wick',
                'email' => 'wick@gmail.com',
                'package' => 'Growth - Monthly',
                'price' => '$49.00',
                'date' => '1 Oct, 2028',
                'status' => 'Active',
                'status_class' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            ],
        ];

        // Real dynamic simulation
        $perPage = 5;
        $currentPage = (int) request('page', 1);
        $totalItems = count($allTransactions);
        $totalPages = (int) ceil($totalItems / $perPage);
        $transactions = array_slice($allTransactions, ($currentPage - 1) * $perPage, $perPage);
    } else {
        $currentPage = $transactions->currentPage();
        $totalPages = $transactions->lastPage();
    }
@endphp

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Recent Transactions
            </h3>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form>
                <div class="relative">
                    <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill="" />
                        </svg>
                    </span>
                    <input type="text" placeholder="Search..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
            </form>
            <div>
                <button
                    class="text-theme-sm shadow-theme-xs inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="fill-white stroke-current dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5" />
                        <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5" />
                    </svg>
                    Filter
                </button>
            </div>
        </div>
    </div>

    <div class="custom-scrollbar max-w-full overflow-x-auto overflow-y-visible">
        <table class="min-w-full">
            <thead class="border-y border-gray-100 bg-gray-50 py-3 dark:border-gray-800 dark:bg-gray-900">
                <th class="px-6 py-3 font-normal whitespace-nowrap sm:pr-6">
                    <div class="flex items-center">
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Paid By</p>
                    </div>
                </th>
                <th class="px-6 py-3 font-normal whitespace-nowrap sm:px-6">
                    <div class="flex items-center">
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Package Name</p>
                    </div>
                </th>
                <th class="px-6 py-3 font-normal whitespace-nowrap sm:px-6">
                    <div class="flex items-center">
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Price</p>
                    </div>
                </th>
                <th class="px-6 py-3 font-normal whitespace-nowrap sm:px-6">
                    <div class="flex items-center">
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Paid Date</p>
                    </div>
                </th>
                <th class="px-6 py-3 font-normal whitespace-nowrap sm:px-6">
                    <div class="flex items-center">
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Status</p>
                    </div>
                </th>
                <th class="px-6 py-3 font-normal whitespace-nowrap sm:px-6">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Actions</p>
                </th>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap sm:pr-5">
                            <div>
                                <p class="text-theme-sm block font-medium text-gray-700 dark:text-gray-400">
                                    {{ $transaction['user'] }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transaction['email'] }}
                                </p>
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                            <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                {{ $transaction['package'] }}
                            </p>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                            <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                {{ $transaction['price'] }}
                            </p>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                            <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                {{ $transaction['date'] }}
                            </p>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                            <span
                                class="{{ $transaction['status_class'] }} rounded-full px-2 py-0.5 text-theme-xs font-medium">
                                {{ $transaction['status'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap sm:px-6">
                            <div class="flex items-center justify-center">
                                <div x-data="dropdown()" @click.outside="open = false" class="relative">
                                    <button @click="toggle" class="text-gray-500 dark:text-gray-400">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill="" />
                                        </svg>
                                    </button>
                                    <div x-show="open" class="shadow-theme-lg dark:bg-gray-dark fixed right-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" x-ref="dropdown">
                                        <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">View More</button>
                                        <button
                                            class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <a href="{{ $currentPage > 1 ? request()->fullUrlWithQuery(['page' => $currentPage - 1]) : '#' }}"
                class="{{ $currentPage > 1 ? '' : 'pointer-events-none opacity-50' }} text-theme-sm shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-2 py-2 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:px-3.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill="" />
                </svg>
                <span class="hidden sm:inline"> Previous </span>
            </a>

            <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400">
                Page {{ $currentPage }} of {{ $totalPages }}
            </span>

            <ul class="hidden items-center gap-0.5 sm:flex">
                @php
                    $visiblePages = [];
                    if ($totalPages <= 7) {
                        $visiblePages = range(1, $totalPages);
                    } else {
                        if ($currentPage <= 4) {
                            $visiblePages = [1, 2, 3, 4, 5, '...', $totalPages];
                        } elseif ($currentPage >= $totalPages - 3) {
                            $visiblePages = [1, '...', $totalPages - 4, $totalPages - 3, $totalPages - 2, $totalPages - 1, $totalPages];
                        } else {
                            $visiblePages = [1, '...', $currentPage - 1, $currentPage, $currentPage + 1, '...', $totalPages];
                        }
                    }
                @endphp
                @foreach ($visiblePages as $page)
                    <li>
                        @if ($page === '...')
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                ...
                            </span>
                        @else
                            <a href="{{ request()->fullUrlWithQuery(['page' => $page]) }}" class="{{ $page === $currentPage ? 'bg-brand-500 text-white' : 'text-gray-700 dark:text-gray-400 hover:bg-brand-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500' }} flex h-10 w-10 items-center justify-center rounded-lg text-theme-sm font-medium transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>

            <a href="{{ $currentPage < $totalPages ? request()->fullUrlWithQuery(['page' => $currentPage + 1]) : '#' }}" class="{{ $currentPage < $totalPages ? '' : 'pointer-events-none opacity-50' }} text-theme-sm shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-2 py-2 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:px-3.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                <span class="hidden sm:inline"> Next </span>
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill="" />
                </svg>
            </a>
        </div>
    </div>
</div>
