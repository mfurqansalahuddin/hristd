import { createPopper } from '@popperjs/core';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import './bootstrap';
import './components/popover';
import './components/tooltip';
// Prism
import Prism from 'prismjs';
import 'prismjs/components/prism-bash';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-json';
import 'prismjs/components/prism-jsx';
import 'prismjs/components/prism-markdown';
import 'prismjs/components/prism-scss';
import 'prismjs/components/prism-tsx';
import 'prismjs/components/prism-typescript';
import 'prismjs/plugins/line-numbers/prism-line-numbers';
import 'prismjs/plugins/line-numbers/prism-line-numbers.css';
// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { register } from 'swiper/element/bundle';
// register Swiper custom elements
register();


window.Alpine = Alpine;
window.createPopper = createPopper;
window.ApexCharts = ApexCharts;
window.Prism = Prism;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;


// Register Alpine.js components before initializing
Alpine.data("dropdown", () => ({
    open: false,
    toggle() {
        this.open = !this.open;
        if (this.open) this.position();
    },
    position() {
        this.$nextTick(() => {
            const button = this.$el;
            const dropdown = this.$refs.dropdown;
            const rect = button.getBoundingClientRect();
            
            // Apply initial styles to ensure measurement is possible
            dropdown.style.position = "fixed";
            dropdown.style.zIndex = "999";
            dropdown.style.right = `${window.innerWidth - rect.right - 13}px`;

            const dropdownHeight = Math.max(dropdown.offsetHeight, 0);
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
                dropdown.style.top = `${rect.top - dropdownHeight}px`;
            } else {
                dropdown.style.top = `${rect.bottom}px`;
            }
        });
    },
    init() {
        this.$watch("open", (value) => {
            if (value) this.position();
        });
    },
}));

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Map imports
    if (document.querySelector('#mapOne') || document.querySelector('#mapTwo')) {
        import('./components/map').then(module => module.initMap());
    }
    if (document.querySelector('#mapLocationView')) {
        import('./components/maps/vector/location-maps').then(module => module.default());
    }
    if (document.querySelector('#mapLocationView3')) {
        import('./components/maps/vector/location-maps-3').then(module => module.default());
    }
    if (document.querySelector('#mapGlobalUser')) {
        import('./components/maps/vector/global-user-map-init').then(module => module.default());
    }
    if (document.querySelector('#mapTrafficAnalytics')) {
        import('./components/maps/vector/traffic-analytics-map-init').then(module => module.default());
    }
    if (document.querySelector('#mapCustomerPinPoint')) {
        import('./components/maps/vector/customer-pin-point-map').then(module => module.default());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartFour')) {
        import('./components/chart/chart-4').then(module => module.initChartFour());
    }
    if (document.querySelector('#chartFive')) {
        import('./components/chart/chart-5').then(module => module.initChartFive());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartSeven')) {
        import('./components/chart/chart-7').then(module => module.initChartSeven());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelectorAll('.chartNine').length) {
        import('./components/chart/chart-9').then(module => module.initChartNine());
    }
    if (document.querySelector('#chartTen')) {
        import('./components/chart/chart-10').then(module => module.initChartTen());
    }
    if (document.querySelector('#chartEleven')) {
        import('./components/chart/chart-11').then(module => module.initChartEleven());
    }
    if (document.querySelector('#chartTwelve')) {
        import('./components/chart/chart-12').then(module => module.initChartTwelve());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }
    if (document.querySelector('#chartFourteen')) {
        import('./components/chart/chart-14').then(module => module.initChartFourteen());
    }
    if (document.querySelector('#chartFifteen')) {
        import('./components/chart/chart-15').then(module => module.initChartFifteen());
    }
    if (document.querySelector('#chartSixteen')) {
        import('./components/chart/chart-16').then(module => module.initChartSixteen());
    }
    if (document.querySelector('#chartSeventeen')) {
        import('./components/chart/chart-17').then(module => module.initChartSeventeen());
    }
    if (document.querySelector('#chartEighteen')) {
        import('./components/chart/chart-18').then(module => module.initChartEighteen());
    }
    if (document.querySelector('#chartNineteen')) {
        import('./components/chart/chart-19').then(module => module.initChartNineteen());
    }
    if (document.querySelector('#chartTwenty')) {
        import('./components/chart/chart-20').then(module => module.initChartTwenty());
    }
    if (document.querySelector('#chartTwentyOne')) {
        import('./components/chart/chart-21').then(module => module.initChartTwentyOne());
    }
    if (document.querySelector('#chartTwentyTwo')) {
        import('./components/chart/chart-22').then(module => module.initChartTwentyTwo());
    }
    if (document.querySelector('#chartTwentyThree')) {
        import('./components/chart/chart-23').then(module => module.initChartTwentyThree());
    }
    if (document.querySelector('#chartTwentyFour')) {
        import('./components/chart/chart-24').then(module => module.initChartTwentyFour());
    }
    if (document.querySelectorAll('.chartTwentyFive').length) {
        import('./components/chart/chart-25').then(module => module.initChartTwentyFive());
    }
    if (document.querySelector('#chartTwentySix')) {
        import('./components/chart/chart-26').then(module => module.initChartTwentySix());
    }
    if (document.querySelector('#chartTwentySeven')) {
        import('./components/chart/chart-27').then(module => module.initChartTwentySeven());
    }
    if (document.querySelector('#chartTwentyEight')) {
        import('./components/chart/chart-28').then(module => module.initChartTwentyEight());
    }
    if (document.querySelector('#chartTwentyNine')) {
        import('./components/chart/chart-29').then(module => module.initChartTwentyNine());
    }
    if (document.querySelector('#chartThirty')) {
        import('./components/chart/chart-30').then(module => module.initChartThirty());
    }
    if (document.querySelector('#chartThirtyOne')) {
        import('./components/chart/chart-31').then(module => module.initChartThirtyOne());
    }
    if (document.querySelector('#chartThirtyTwo')) {
        import('./components/chart/chart-32').then(module => module.initChartThirtyTwo());
    }
    if (document.querySelector('#chartThirtyThree')) {
        import('./components/chart/chart-33').then(module => module.initChartThirtyThree());
    }
    if (document.querySelector('#chartThirtyFour')) {
        import('./components/chart/chart-34').then(module => module.initChartThirtyFour());
    }
    if (document.querySelector('#chartThirtyFive')) {
        import('./components/chart/chart-35').then(module => module.initChartThirtyFive());
    }
    if (document.querySelector('#chartThirtySeven')) {
        import('./components/chart/chart-37').then(module => module.initChartThirtySeven());
    }
    if (document.querySelector('#chartThirtyEight')) {
        import('./components/chart/chart-38').then(module => module.initChartThirtyEight());
    }
    if (document.querySelector('#chartThirtyNine')) {
        import('./components/chart/chart-39').then(module => module.initChartThirtyNine());
    }
    if (document.querySelector('#chartForty')) {
        import('./components/chart/chart-40').then(module => module.initChartForty());
    }
    if (document.querySelector('#chartFortyOne')) {
        import('./components/chart/chart-41').then(module => module.initChartFortyOne());
    }
    if (document.querySelector('#chartFortyTwo')) {
        import('./components/chart/chart-42').then(module => module.initChartFortyTwo());
    }
    if (document.querySelector('#chartFortyThree')) {
        import('./components/chart/chart-43').then(module => module.initChartFortyThree());
    }

    // Prism highlight
    Prism.highlightAll();

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }

    // copy button
    const copyInput = document.getElementById("copy-input");
    if (copyInput) {
        // Select the copy button and input field
        const copyButton = document.getElementById("copy-button");
        const copyText = document.getElementById("copy-text");
        const websiteInput = document.getElementById("website-input");

        // Event listener for the copy button
        copyButton.addEventListener("click", () => {
            // Copy the input value to the clipboard
            navigator.clipboard.writeText(websiteInput.value).then(() => {
                // Change the text to "Copied"
                copyText.textContent = "Copied";

                // Reset the text back to "Copy" after 2 seconds
                setTimeout(() => {
                    copyText.textContent = "Copy";
                }, 2000);
            });
        });
    }
});
