// script.js

/**
*   Initializes the page by running all necessary JavaScript code.
*   This function is called after the DOM is fully loaded.
*/
function init() {

    /*
        init
            native (our implementation):
                #booking
                #bootstrapTheme
                #copyLink
                #scrolled
                #searchDivClick
                #tooltip
                #travelers
                #unchecked                                 
            vendor (external library):
                #filterizr
                #jalaliDatepicker
                #lozad
                #noUiSlider                                
                #swiper
                #tomSelect
    */
    /* ------------------------------------ . ----------------------------------- */
    /* --------------------------------- native --------------------------------- */
    /* ------------------------------------ . ----------------------------------- */
    /* -------------------------------------------------------------------------- */
    /*                                  #booking                                  */
    /* -------------------------------------------------------------------------- */
    if (document.getElementById('bookingType')) {

        // Get the main select element
        const mainSelect = document.getElementById('bookingType');
        // Get the bookingTo select element by its ID
        const bookingToSelect = document.getElementById('bookingTo');
        // Add an event listener to the main select element to listen for changes
        mainSelect.addEventListener('change', function () {
            // Check the value of the main select element
            if (mainSelect.value === '1') {
                // Disable the bookingTo select element if the value is '1'
                bookingToSelect.disabled = true;
            } else {
                // Enable the bookingTo select element for any other value
                bookingToSelect.disabled = false;
            }
        });

    }
    /* -------------------------------------------------------------------------- */
    /*                               #bootstrapTheme                              */
    /* -------------------------------------------------------------------------- */
    // Check if an element with the class 'btnSwitch' exists in the document
    if (document.querySelector('.btnSwitch')) {

        // Store the button element in a variable for later use
        let btnSwitch = document.querySelectorAll('.btnSwitch');

        btnSwitch.forEach(elm => {

            // Add a click event listener to the button
            elm.addEventListener('click', () => {

                // Toggle the 'active' class on the button element
                elm.classList.toggle('active');

                // Check the current theme set on the document's root element
                if (document.documentElement.getAttribute('data-bs-theme') == 'dark') {
                    // If the current theme is 'dark', switch it to 'light'
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                } else {
                    // Otherwise, switch the theme to 'dark'
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                }

            });

        });
    }
    /* -------------------------------------------------------------------------- */
    /*                                  #copyLink                                 */
    /* -------------------------------------------------------------------------- */
    if (document.getElementById('copyLinkBtn')) {
        const copyLinkBtn = document.getElementById('copyLinkBtn');
        const shareLink = document.getElementById('shareLink');

        copyLinkBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(shareLink.value);
                copyLinkBtn.textContent = 'Copied!';
                setTimeout(() => {
                    copyLinkBtn.textContent = 'Copy Link';
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
            }
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                  #scrolled                                 */
    /* -------------------------------------------------------------------------- */
    if (document.querySelector("header")) {

        const header = document.querySelector("header"); // Select the header element

        function addScrollClass() {
            const scrollPosition = window.scrollY || window.pageYOffset; // Get scroll position

            if (scrollPosition > 60) {
                header.classList.add("scrolled"); // Add "is-scrolled" class if scrolled down
            } else {
                header.classList.remove("scrolled"); // Remove "is-scrolled" class if at top
            }
        }

        // Add event listener
        window.addEventListener("scroll", addScrollClass);

        // Call the function initially to handle cases where the user lands on a scrolled page
        addScrollClass();

    }
    /* -------------------------------------------------------------------------- */
    /*                               #searchDivClick                              */
    /* -------------------------------------------------------------------------- */
    if (document.querySelectorAll('[data-click-active]')) {

        // Select all elements with the attribute 'data-click-active'
        const searchDivClick = document.querySelectorAll('[data-click-active]');

        // Iterate through each selected element
        searchDivClick.forEach(div => {
            // Select all navigation links within the current element
            const navLinks = div.querySelectorAll('.nav .nav-link');

            // Add a click event listener to the current element
            div.addEventListener('click', (event) => {
                // Check if the clicked element does not have the attribute 'data-click-nactive'
                if (!event.target.closest('[data-click-nactive]')) {
                    // Toggle the 'active' class on the current element
                    div.classList.toggle('active');

                    // If the current element now has the 'active' class
                    if (div.classList.contains('active')) {
                        // Set the 'data-click-nactive' attribute to 'true' for each navigation link
                        navLinks.forEach(link => link.dataset.clickNactive = 'true');
                    } else {
                        // Remove the 'data-click-nactive' attribute from each navigation link
                        navLinks.forEach(link => delete link.dataset.clickNactive);
                    }
                }
            });
        });

    }
    /* -------------------------------------------------------------------------- */
    /*                                  #tooltip                                  */
    /* -------------------------------------------------------------------------- */
    // Initialize Bootstrap tooltips

    if (document.querySelectorAll('[data-bs-toggle="tooltip"]')) {

        // Get all elements with the data-bs-toggle="tooltip" attribute
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

        // Create a new Bootstrap tooltip instance for each element with the data-bs-toggle="tooltip" attribute
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    }
    /* -------------------------------------------------------------------------- */
    /*                                 #travelers                                 */
    /* -------------------------------------------------------------------------- */
    if (document.getElementById('adults-count')) {

        // Initialize variables and DOM elements for managing traveler counts
        const adultCountPlus = document.getElementById('adults-plus');
        const adultCountMinus = document.getElementById('adults-minus');
        const adultCountDisplay = document.getElementById('adults-count');
        const childCountPlus = document.getElementById('children-plus');
        const childCountMinus = document.getElementById('children-minus');
        const childCountDisplay = document.getElementById('children-count');
        const infantCountPlus = document.getElementById('infants-plus');
        const infantCountMinus = document.getElementById('infants-minus');
        const infantCountDisplay = document.getElementById('infants-count');
        const totalTravelersDisplay = document.getElementById('total-travelers');

        // Initialize traveler counts and update the UI
        let adultCount = 1;
        let childCount = 0;
        let infantCount = 0;
        updateContentTravelers();

        // Event listeners for incrementing and decrementing traveler counts
        adultCountPlus.addEventListener('click', () => {
            // Increment adult count, update UI, and enable minus button if necessary
            adultCount++;
            if (adultCount > 0) { adultCountMinus.disabled = false; }
            adultCountDisplay.textContent = adultCount;
            updateTotalTravelers();
        });

        adultCountMinus.addEventListener('click', () => {
            // Decrement adult count, update UI, and disable minus button if necessary
            if (adultCount > 0) {
                adultCount--;
                if (adultCount == 0) { adultCountMinus.disabled = true; }
                adultCountDisplay.textContent = adultCount;
                updateTotalTravelers();
            }
        });

        childCountPlus.addEventListener('click', () => {
            // Increment child count, update UI, and enable minus button if necessary
            childCount++;
            if (childCount > 0) { childCountMinus.disabled = false; }
            childCountDisplay.textContent = childCount;
            updateTotalTravelers();
        });

        childCountMinus.addEventListener('click', () => {
            // Decrement child count, update UI, and disable minus button if necessary
            if (childCount > 0) {
                childCount--;
                if (childCount == 0) { childCountMinus.disabled = true; }
                childCountDisplay.textContent = childCount;
                updateTotalTravelers();
            }
        });

        infantCountPlus.addEventListener('click', () => {
            // Increment infant count, update UI, and enable minus button if necessary
            infantCount++;
            if (infantCount > 0) { infantCountMinus.disabled = false; }
            infantCountDisplay.textContent = infantCount;
            updateTotalTravelers();
        });

        infantCountMinus.addEventListener('click', () => {
            // Decrement infant count, update UI, and disable minus button if necessary
            if (infantCount > 0) {
                infantCount--;
                if (infantCount == 0) { infantCountMinus.disabled = true; }
                infantCountDisplay.textContent = infantCount;
                updateTotalTravelers();
            }
        });

        // Update UI with current traveler counts
        function updateContentTravelers() {
            // Update adult, child, and infant counts in the UI
            adultCountDisplay.textContent = adultCount;
            childCountDisplay.textContent = childCount;
            infantCountDisplay.textContent = infantCount;

            // Disable minus buttons if counts are zero
            if (adultCount == 0) { adultCountMinus.disabled = true; }
            if (childCount == 0) { childCountMinus.disabled = true; }
            if (infantCount == 0) { infantCountMinus.disabled = true; }

            // Update total traveler count
            updateTotalTravelers();
        }

        // Calculate and display total traveler count
        function updateTotalTravelers() {
            // Calculate total traveler count
            const total = adultCount + childCount + infantCount;
            // Display total traveler count in the UI
            totalTravelersDisplay.textContent = total.toString();
        }

    }
    /* -------------------------------------------------------------------------- */
    /*                                 #unchecked                                 */
    /* -------------------------------------------------------------------------- */
    if (document.querySelectorAll('[data-unchecked]')) {

        // Select all elements that have the attribute 'data-unchecked'
        const uncheckButtons = document.querySelectorAll('[data-unchecked]');

        // Loop through each of the selected elements
        uncheckButtons.forEach(button => {
            // Add a click event listener to each button
            button.addEventListener('click', (event) => {
                // Get the ID of the target checkbox from the button's 'data-unchecked' attribute
                const targetCheckboxId = event.target.dataset.unchecked;
                // Find the checkbox element by its ID
                const checkbox = document.getElementById(targetCheckboxId);
                // If the checkbox exists
                if (checkbox) {
                    // Uncheck the checkbox
                    checkbox.checked = false;
                    // Remove the button from the DOM
                    button.remove();
                }
            });
        });
    }
    /* ------------------------------------ . ----------------------------------- */
    /* --------------------------------- vendor --------------------------------- */
    /* ------------------------------------ . ----------------------------------- */
    /* -------------------------------------------------------------------------- */
    /*                                 #filterizr                                 */
    /* -------------------------------------------------------------------------- */
    if (document.querySelector('.filter-container')) {
        const filterizr = new Filterizr('.filter-container');
    }

    // add active class to menu buttons
    if (document.querySelector(".filter-nav")) {
        const navLinks = document.querySelectorAll('.filter-nav .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navLinks.forEach(link => link.classList.remove('active'));
                link.classList.add('active');
            });
        });
    }
    /* -------------------------------------------------------------------------- */
    /*                              #jalaliDatepicker                             */
    /* -------------------------------------------------------------------------- */
    if (document.querySelector('[data-jdp]')) {
        jalaliDatepicker.startWatch({
            zIndex: 1025,
            minDate: "today",
            persianDigits: true
        });
    }
    /* -------------------------------------------------------------------------- */
    /*                                   #lozad                                   */
    /* -------------------------------------------------------------------------- */
    if (document.querySelector('.lozad')) {
        const observer = lozad(); // lazy loads elements with default selector as '.lozad'
        observer.observe();
    }
    /* -------------------------------------------------------------------------- */
    /*                                 #noUiSlider                                */
    /* -------------------------------------------------------------------------- */
    if (document.getElementById('searchClockFilter')) {

        var slider = document.getElementById('searchClockFilter');

        noUiSlider.create(slider, {
            pips: {
                density: 5
            },
            margin: 1,
            tooltips: true,
            start: [4, 222],
            step: 0.5,
            connect: true,
            range: {
                'min': 4,
                'max': 22
            }
        });
    }
    /* -------------------------------------------------------------------------- */
    /*                                   #swiper                                  */
    /* -------------------------------------------------------------------------- */
    if (document.querySelector('.swiper')) {
        const swiperElmnts = document.querySelectorAll('.swiper');

        // default Swiper options
        const defaultOptions = {
            // ... other default options
        };

        swiperElmnts.forEach((swiperInst, index) => {
            // Generate unique class names for navigation buttons
            const nextBtnClass = `swiper-button-next-${index}`;
            const prevBtnClass = `swiper-button-prev-${index}`;

            // Get any custom options from data attribute (if present)
            const dataOptions = swiperInst.dataset.swiperOptions ? JSON.parse(swiperInst.dataset.swiperOptions) : {};

            // Merge custom options with default options (or use all defaults if no data attribute)
            const mergedOptions = {
                ...defaultOptions,
                ...dataOptions,
                navigation: {
                    nextEl: `.${nextBtnClass}`,
                    prevEl: `.${prevBtnClass}`
                }
            };

            // Add unique classes to the navigation buttons
            if (swiperInst.querySelector('.swiper-button-next')) {
                swiperInst.querySelector('.swiper-button-next').classList.add(nextBtnClass);
            }
            if (swiperInst.querySelector('.swiper-button-prev')) {
                swiperInst.querySelector('.swiper-button-prev').classList.add(prevBtnClass);
            }

            // Create a new Swiper instance with merged options
            new Swiper(swiperInst, mergedOptions);
        });
    }
    /* -------------------------------------------------------------------------- */
    /*                                 #tomSelect                                 */
    /* -------------------------------------------------------------------------- */
    document.querySelectorAll('.tom-select-header').forEach((el) => {

        let settings = {
            // allowEmptyOption: true,
            // hideSelected: false,
            sortField: 'text',
            plugins: {
                'dropdown_header': {
                    title: 'پرتردد'
                }
            },
            render: {
                no_results: function (data, escape) {
                    return '<div class="no-results">نتیجه ای پیدا نشد!</div>';
                },
            }
        };
        new TomSelect(el, settings);

    });

    // End of function: init
}

document.addEventListener("DOMContentLoaded", init);





// Wait until the entire content of the document is loaded
// convert English digits to Persian digits
/*document.addEventListener("DOMContentLoaded", function() {

    // Function to convert English digits to Persian digits
    function convertNumbersToPersian(text) {
        // Mapping of English digits to Persian digits
        const englishToPersianDigits = {
            '0': '۰', '1': '۱', '2': '۲', '3': '۳',
            '4': '۴', '5': '۵', '6': '۶', '7': '۷',
            '8': '۸', '9': '۹'
        };

        // Replace each English digit with its Persian equivalent
        return text.replace(/\d/g, function(match) {
            return englishToPersianDigits[match];
        });
    }

    // Function to replace numbers in the text of all nodes within the given node
    function replaceNumbersInText(node) {
        // Check if the node is a text node
        if (node.nodeType === Node.TEXT_NODE) {
            // Convert numbers in the text content to Persian
            node.textContent = convertNumbersToPersian(node.textContent);
        } else {
            // Recursively process child nodes
            for (let child of node.childNodes) {
                replaceNumbersInText(child);
            }
        }
    }

    // Start the replacement process from the body of the document
    replaceNumbersInText(document.body);
});
*/