<button
    x-data="{
        theme: localStorage.getItem('theme') || 'auto',
        resolvedTheme: 'light',
        init() {
            this.updateTheme();
        },
        toggle() {
            if (this.theme === 'light') {
                this.theme = 'dark';
            } else if (this.theme === 'dark') {
                this.theme = 'auto';
            } else {
                this.theme = 'light';
            }

            localStorage.setItem('theme', this.theme);
            this.updateTheme();
        },
        updateTheme() {
            let isDark = false;

            if (this.theme === 'dark') {
                isDark = true;
            } else if (this.theme === 'light') {
                isDark = false;
            } else {
                isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            document.documentElement.classList.toggle('dark', isDark);
            this.resolvedTheme = isDark ? 'dark' : 'light';
            document.documentElement.dataset['theme'] = this.resolvedTheme;
            document.documentElement.style.colorScheme = this.resolvedTheme;
            if (document.body) {
                document.body.dataset['theme'] = this.resolvedTheme;
                document.body.style.colorScheme = this.resolvedTheme;
            }
        }
    }"
    x-init="init()"
    @click="toggle()"
    class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
>
    <svg x-show="theme === 'auto'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" width="20" height="20">
        <path fill="currentColor" d="M10 1.75C5.44365 1.75 1.75 5.44365 1.75 10C1.75 14.5563 5.44365 18.25 10 18.25C14.5563 18.25 18.25 14.5563 18.25 10C18.25 5.44365 14.5563 1.75 10 1.75Z" />
        <path fill="currentColor" opacity="0.35" d="M10 5.25C7.37665 5.25 5.25 7.37665 5.25 10C5.25 12.6234 7.37665 14.75 10 14.75C12.6234 14.75 14.75 12.6234 14.75 10C14.75 7.37665 12.6234 5.25 10 5.25Z" />
    </svg>

    <svg x-show="theme !== 'auto' && resolvedTheme === 'dark'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" width="20" height="20">
        <path fill="currentColor" d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415Z" />
        <path fill="currentColor" d="M17.7077 10.7501C18.1219 10.7501 18.4577 10.4143 18.4577 10.0001C18.4577 9.58592 18.1219 9.25013 17.7077 9.25013H16.4577C16.0435 9.25013 15.7077 9.58592 15.7077 10.0001C15.7077 10.4143 16.0435 10.7501 16.4577 10.7501H17.7077Z" />
    </svg>
    <svg x-show="theme !== 'auto' && resolvedTheme === 'light'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" width="20" height="20">
        <path fill="currentColor" d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97Z" />
        <path fill="currentColor" d="M8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459Z" />
    </svg>
</button>
