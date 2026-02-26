(function () {
    const originalParse = JSON.parse;
    JSON.parse = function (text) {
        let data = originalParse(text);
        if (data && data.redirect_to) {
            data._original_redirect_to = data.redirect_to;
            delete data.redirect_to;
        }
        return data;
    };

})();

jQuery(document).ready(function ($) {

    // progress step
    const $steps = $('.tf-package-content-item');
    const $progressItems = $('.progressbar li');
    let currentStep = 0;

    function showStep(index) {
        $steps.removeClass('active');
        $progressItems.removeClass('active');
        $steps.eq(index).addClass('active');
        $progressItems.each(function (i) {
            if (i <= index) {
                $(this).addClass('active');
            }
        });
        currentStep = index;
    }

    // Next button
    $(document).on('click', '.tf-package-next', function () {
        if (currentStep < $steps.length - 1) {
            showStep(currentStep + 1);
        }
    });

    // Previous button
    // $(document).on('click', '.tf-package-prev', function () {
    //     if (currentStep > 0) {
    //         showStep(currentStep - 1);
    //     }
    // });

    // open popup
    document.querySelectorAll(".tf-open-popup").forEach(button => {
        button.addEventListener("click", function() {
            const postId   = this.getAttribute("data-post-id");
            const postType = this.getAttribute("data-post-type");
            const popup    = document.getElementById("tf-package-popup");
            const loader   = document.querySelector(".skeleton-wrapper");
            const $popupContent = jQuery(".tf-package-template-content");
            const $tfHotelBookingId = jQuery(".tf-hotel-booking-id");
            const $tfTourBookingId = jQuery(".tf-tour-booking-id");
            const body = document.body;

            $popupContent.html('');

            if (!postId || !postType) return;
            popup.style.display = "block";

            // Store active values (optional)
            popup.setAttribute("data-active-id", postId);
            popup.setAttribute("data-active-type", postType);

            // loader.style.display = "flex";
            document.body.style.overflow = "hidden";
            loader.style.display = "flex";

            if(postType == 'tf_hotel'){
                $tfHotelBookingId.val(postId);
                sessionStorage.setItem('tf_hotel_book_id', postId);
            }else if(postType == 'tf_tours'){
                $tfTourBookingId.val(postId);  
            }

            $.ajax({
                url: tf_package_data.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tf_load_single_template',
                    post_id: postId,
                    post_type: postType,
                    nonce: tf_package_data.nonce
                },
                success: function (response) {
                    loader.style.display = "none";
                    $popupContent.html(response.data.html);
                    tf_package_data.tour_form_data = response.data.tour_form_data;
                    setTimeout(function () {
                        tf_package_slick_init();
                        initTourFlatpickr();
                        tfTourStickBar();
                        // initHotelFlatpickr();
                      
    
                    }, 50);
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: " + error);
                }
            });
        });
    });

    // close popup
    $(document).on('click', '.tf-package-popup-close', function () {
        const popup = document.getElementById("tf-package-popup");
        popup.style.display = "none";
        document.body.style.overflow = "";
        if( jQuery(".tf-single-package").length > 0){
            jQuery('body,html').animate({
                scrollTop: jQuery(".tf-single-package").offset().top
            }, 500);
        }
    });

    // hotel room availability
    $(document).on('click', '.hotel-room-availability', function (e) {
        e.preventDefault();
        var offset = 200;
        if (window.innerWidth <= 768) {
            offset = 100;
        }
        $('.tf-package-template-content').animate({
            scrollTop: $("#tf-single-hotel-avail").offset().top - offset
        }, 500);
    });

   

    jQuery(document).ajaxSuccess(function (event, xhr, settings) {

        if (!settings || !settings.url || !settings.url.includes('admin-ajax.php')) {
            return;
        }

        let action = null;

        if (typeof settings.data === 'string') {
            const params = new URLSearchParams(settings.data);
            action = params.get('action');

        }

        else if (typeof settings.data === 'object' && !(settings.data instanceof FormData)) {
            action = settings.data.action || null;
        }

        else if (settings.data instanceof FormData) {
            action = settings.data.get('action');
        }

        if (!action) return; 

        // HOTEL BOOKING
        if (action === 'tf_hotel_booking') {
            handleHotelBooking(xhr);
        }

        // TOUR BOOKING
        if (action === 'tf_tours_booking') {
            handleTourBooking(xhr);
        }

        // ROOM AVAILABILITY
        if (action === 'tf_room_availability') {
            handleRoomAvailability();
        }

    });

    const step = sessionStorage.getItem('tf_package_step');

    if (step === 'second') {
        if (jQuery('.tf-hotels-next').length) {
            jQuery('.tf-hotels-next').click();
        }
    }

    const target = document.body;
    const observer = new MutationObserver(function () {
        const isOpen = document.querySelector('.flatpickr-calendar.open');
        if (isOpen) {
            $(".tf-package-template-content").css("overflow", "hidden");
        } else {
            $(".tf-package-template-content").css("overflow", "auto");
        }
    });

    observer.observe(target, {
        attributes: true,
        childList: true,
        subtree: true
    });


});

// hotel booking
function handleHotelBooking(xhr) {
    let response;
    try {
        response = JSON.parse(xhr.responseText);
    } catch (e) {
        return; // invalid JSON
    }

    if (response._original_redirect_to) {
        
        const $content = jQuery(".tf-package-template-content");
     
        if ($content.length) {
            $content.html('<h3 class="tf-booking-success">Booking completed successfully! Moving to next step...</h3>');
        }

        sessionStorage.setItem('tf_package_step', 'second');

        setTimeout(function () {
            const $popupClose = jQuery('.tf-package-popup-close');
            if ($popupClose.length) {
                $popupClose.click();
            }

            const $nextStep = jQuery('.tf-hotels-next');
            if ($nextStep.length) {
                $nextStep.click();
            }

            const $secondStep = jQuery('.tf-first-step');
            if ($secondStep.length) {
                $secondStep.css({
                    height: 'auto',
                    opacity: 1,
                    visibility: 'visible'
                });
            }
        }, 3000); 
        if( jQuery(".tf-single-package").length > 0){
            jQuery('body,html').animate({
                scrollTop: jQuery(".tf-single-package").offset().top
            }, 500);
        }
    }
}

// tour booking
function handleTourBooking(xhr) {
    let response;
    try {
        response = JSON.parse(xhr.responseText);
    } catch (e) {
        return; 
    }

    if (response._original_redirect_to) {
        
        const $content = jQuery(".tf-package-template-content");
        const $popupClose = jQuery('.tf-package-popup-close');
        const $nextStep = jQuery('.tf-tour-next');
        const $tfHotelBookingId = sessionStorage.getItem('tf_hotel_book_id');
        const $tfTourBookingId = jQuery(".tf-tour-booking-id").val();

        sessionStorage.removeItem('tf_package_step');

        jQuery.ajax({
            url: tf_package_data.ajaxurl,
            type: 'POST',
            data: {
                action: 'tf_package_booking_data',
                hotel_booking_id: $tfHotelBookingId,
                tour_booking_id: $tfTourBookingId,
                nonce: tf_package_data.booking_nonce
            },
            beforeSend: function () {
                if ($content.length) {
                    $content.html('<h3 class="tf-booking-success">Booking completed successfully! Moving to next step...</h3>');
                }
            },
            success: function (response) {
                if(response.data.booking_content){
                    jQuery(".tf-booking-content").html(response.data.booking_content);
                }

                if ($popupClose.length) {
                    $popupClose.click();
                }
                if ($nextStep.length) {
                    $nextStep.click();
                }
                if( jQuery(".tf-single-package").length > 0){
                    jQuery('body,html').animate({
                        scrollTop: jQuery(".tf-single-package").offset().top
                    }, 500);
                }
                // remove hotel booking id from session storage
                sessionStorage.removeItem('tf_hotel_book_id');

            },
            error: function (xhr, status, error) {
                console.error("AJAX Error: " + error);
            }
        });
    }
}

// room availability
function handleRoomAvailability() {
    var $container = jQuery('.tf-package-template-content');
    var $target = jQuery('.tf-rooms-sections');

    if ($target.length > 0) {
        $container.animate({
            scrollTop: $target.position().top + $container.scrollTop()
        }, 500);
    }
}

// package popup slick init
function tf_package_slick_init(){
    jQuery('.tf-slider-items-wrapper').each(function () {
        if (jQuery(this).hasClass('slick-initialized')) {
            jQuery(this).slick('unslick');
        }

        jQuery(this).slick({
            dots: true,
            arrows: false,
            infinite: true,
            speed: 300,
            autoplaySpeed: 2000,
            slidesToShow: 3,
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: true
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });

    });
}

// init hotel flatpickr
// function initHotelFlatpickr(){
//     window.flatpickr.l10ns.default.firstDayOfWeek = 0;
//     const regexMap = {
//         'Y/m/d': /(\d{4}\/\d{2}\/\d{2}).*(\d{4}\/\d{2}\/\d{2})/,
//         'd/m/Y': /(\d{2}\/\d{2}\/\d{4}).*(\d{2}\/\d{2}\/\d{4})/,
//         'm/d/Y': /(\d{2}\/\d{2}\/\d{4}).*(\d{2}\/\d{2}\/\d{4})/,
//         'Y-m-d': /(\d{4}-\d{2}-\d{2}).*(\d{4}-\d{2}-\d{2})/,
//         'd-m-Y': /(\d{2}-\d{2}-\d{4}).*(\d{2}-\d{2}-\d{4})/,
//         'm-d-Y': /(\d{2}-\d{2}-\d{4}).*(\d{2}-\d{2}-\d{4})/,
//         'Y.m.d': /(\d{4}\.\d{2}\.\d{2}).*(\d{4}\.\d{2}\.\d{2})/,
//         'd.m.Y': /(\d{2}\.\d{2}\.\d{4}).*(\d{2}\.\d{2}\.\d{4})/,
//         'm.d.Y': /(\d{2}\.\d{2}\.\d{4}).*(\d{2}\.\d{2}\.\d{4})/
//     };
//     const dateRegex = regexMap[tf_package_data.user_date_format];

//     jQuery(document).on('focus click', ".tf-hotel-booking-sidebar #check-in-out-date", function() {
//         this._flatpickr = flatpickr(this, {
//             enableTime: false,
//             mode: "range",
//             minDate: "today",
//             altInput: true,
//             altFormat: tf_package_data.user_date_format,
//             dateFormat: "Y/m/d",
//             defaultDate: tf_package_data.check_in_out,
//             onOpen: function() {
//                 console.log('open');
//                 jQuery(".tf-package-template-content").css("overflow", "hidden");
//             },
//             onClose: function() {
//                 console.log('close');
//                 jQuery(".tf-package-template-content").css("overflow", "auto");
//             },
//             onReady: function (selectedDates, dateStr, instance) {
//                 instance.element.value = dateStr.replace(/(\d{4}\/\d{2}\/\d{2}).*(\d{4}\/\d{2}\/\d{2})/g, function (match, date1, date2) {
//                     return `${date1} - ${date2}`;
//                 });
//                 instance.altInput.value = instance.altInput.value.replace( dateRegex, function (match, d1, d2) {
//                     return `${d1} - ${d2}`;
//                 });
//             },
//             onChange: function (selectedDates, dateStr, instance) {
//                 instance.element.value = dateStr.replace(/(\d{4}\/\d{2}\/\d{2}).*(\d{4}\/\d{2}\/\d{2})/g, function (match, date1, date2) {
//                     return `${date1} - ${date2}`;
//                 });
//                 instance.altInput.value = instance.altInput.value.replace( dateRegex, function (match, d1, d2) {
//                     return `${d1} - ${d2}`;
//                 });
//             },
//         });
      
//     });
// }

// init tour flatpickr
function initTourFlatpickr() {
    function tf_package_flatpickr_locale() {
        let locale = tf_package_data.tour_form_data.flatpickr_locale;
        let allowed_locales = ['ar', 'bn_BD', 'de_DE', 'es_ES', 'fr_FR', 'hi_IN', 'it_IT', 'nl_NL', 'ru_RU', 'zh_CN' ];

        if( jQuery.inArray(locale, allowed_locales) !== -1 ) {
            
            switch (locale) {
                case "bn_BD":
                    locale = 'bn';
                    break;
                case "de_DE":
                    locale = 'de';
                    break;
                case "es_ES":
                    locale = 'es';
                    break;
                case "fr_FR":
                    locale = 'fr';
                    break;
                case "hi_IN":
                    locale = 'hi';
                    break;
                case "it_IT":
                    locale = 'it';
                    break;
                case "nl_NL":
                    locale = 'nl';
                    break;
                case "ru_RU":
                    locale = 'ru';
                    break;
                case "zh_CN":
                    locale = 'zh';
                    break;
            }
        } else {
            locale = 'default';
        }

        return locale;
    }

    // let locale_zone = tf_package_flatpickr_locale();
    window.flatpickr.l10ns[tf_package_flatpickr_locale()].firstDayOfWeek = tf_package_data.tour_form_data.first_day_of_week;

    function populateTimeSelect(times) {
        let timeSelect = jQuery('select[name="check-in-time"]');
        let timeSelectDiv = jQuery(".check-in-time-div");
        timeSelect.empty();

        if (Object.keys(times).length > 0) {
            timeSelect.append(`<option value="" selected hidden>${tf_params.tour_form_data.select_time_text}</option>`);
            // Use the keys and values from the object to populate the options
            $.each(times, function (key, value) {
                timeSelect.append(`<option value="${key}">${value}</option>`);
            });
            timeSelectDiv.css('display', 'flex');
        } else timeSelectDiv.hide();
    }

    var tour_date_options = {
        enableTime: false,
        dateFormat: "Y/m/d",
        altInput: true,
        altFormat: tf_package_data.tour_form_data.date_format,
        locale: tf_package_flatpickr_locale(),
        onReady: function (selectedDates, dateStr, instance) {
            instance.element.value = dateStr.replace(/[a-z]+/g, '-');
            instance.altInput.value = instance.altInput.value.replace(/[a-z]+/g, '-');
        },

        onChange: function (selectedDates, dateStr, instance) {

            instance.altInput.value = instance.altInput.value.replace(/[a-z]+/g, '-');
            jQuery(".tours-check-in-out").val(instance.altInput.value);
            jQuery('.tours-check-in-out[type="hidden"]').val(dateStr.replace(/[a-z]+/g, '-'));
            
            // Initialize empty object for times
            let times = {};
            const selectedDate = selectedDates[0];
            const timestamp = selectedDate.getTime();

            const tourAvailability = tf_package_data.tour_form_data.tour_availability;

            for (const key in tourAvailability) {
                const availability = tourAvailability[key];

                if (availability.status !== 'available') continue;

                const from = new Date(availability.check_in.trim()).getTime();
                const to   = new Date(availability.check_out.trim()).getTime();

                if (timestamp >= from && timestamp <= to) {
                    const allowedTime = availability.allowed_time?.time || [];
                    if (Array.isArray(allowedTime)) {
                        allowedTime.forEach((t) => {
                            if (t && t.trim() !== '') {
                                times[t] = t;
                            }
                        });
                    } else if (typeof allowedTime === 'object' && allowedTime !== null) {
                        Object.values(allowedTime).forEach((t) => {
                            if (t && t.trim() !== '') {
                                times[t] = t;
                            }
                        });
                    }

                    break; // stop after first match
                }
            }

            populateTimeSelect(times);
        },

    };

    if (!tf_package_data.tour_form_data.is_all_unavailable && typeof tf_package_data.tour_form_data.tour_availability === 'object' && tf_package_data.tour_form_data.tour_availability && Object.keys(tf_package_data.tour_form_data.tour_availability).length > 0) {
        tour_date_options.minDate = "today";
        tour_date_options.disableMobile = "true";
        tour_date_options.enable = Object.entries(tf_package_data.tour_form_data.tour_availability)
        .filter(([dateRange, data]) => data.status === "available")
        .map(([dateRange, data]) => {
            const [fromRaw, toRaw] = dateRange.split(' - ').map(str => str.trim());

            const today = new Date();
            const formattedToday = today.getFullYear() + '/' + (today.getMonth() + 1) + '/' + today.getDate();
            let fromDate = fromRaw;

            return {
                from: fromDate,
                to: toRaw
            };
        });
    }else{
        tour_date_options.minDate = "today";
    }

    tour_date_options.disable = [];
    if (tf_package_data.tour_form_data.is_all_unavailable && typeof tf_package_data.tour_form_data.tour_availability === 'object' && tf_package_data.tour_form_data.tour_availability && Object.keys(tf_package_data.tour_form_data.tour_availability).length > 0) {
        tour_date_options.disable = Object.entries(tf_package_data.tour_form_data.tour_availability)
        .filter(([dateRange, data]) => data.status === "unavailable")
        .map(([dateRange, data]) => {
            const [fromRaw, toRaw] = dateRange.split(' - ').map(str => str.trim());

            const today = new Date();
            const formattedToday = today.getFullYear() + '/' + (today.getMonth() + 1) + '/' + today.getDate();
            let fromDate = fromRaw;

            return {
                from: fromDate,
                to: toRaw
            };
        });
    }

    if (tf_package_data.tour_form_data.disable_same_day) {
        tour_date_options.disable.push("today");
    }
    tour_date_options.disableMobile = "true";
    jQuery(".tours-check-in-out").flatpickr(tour_date_options);
}

function tfTourStickBar() { 
    // sticky bottom bar
    if (jQuery('.tf-single-template__one .tf-booking-form').length > 0) {
        jQuery('.tf-package-template-content').on("scroll", function () {
            let bookingBox = jQuery('.tf-single-template__one .tf_tours_main_booking');
            var sticky = jQuery('.tf-single-template__one .tf_tours_bottom_booking .tf-bottom-booking-bar'),
                scroll = jQuery(window).scrollTop(),
                footer = jQuery('footer');
        
            if (footer.length === 0 || bookingBox.length === 0 || sticky.length === 0) {
                return; 
            }
            let boxOffset = bookingBox.offset().top + bookingBox.outerHeight();
            var footerOffset = footer.offset().top,
                windowHeight = jQuery(window).height();
        
            if (scroll >= boxOffset) {
                if (scroll + windowHeight >= footerOffset) {
                    sticky.removeClass('active'); 
                } else {
                    sticky.addClass('active');
                }
            } else {
                sticky.removeClass('active');
            }
        });
    }
}
