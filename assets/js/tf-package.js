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
    $(document).on('click', '.tf-package-prev', function () {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    });

    // open popup
    document.querySelectorAll(".tf-open-popup").forEach(button => {
        button.addEventListener("click", function() {
            const postId   = this.getAttribute("data-post-id");
            const postType = this.getAttribute("data-post-type");
            const popup    = document.getElementById("tf-package-popup");
            const loader   = document.querySelector(".skeleton-wrapper");
            const body = document.body;

            if (!postId || !postType) return;
            popup.style.display = "block";

            // Store active values (optional)
            popup.setAttribute("data-active-id", postId);
            popup.setAttribute("data-active-type", postType);

            // loader.style.display = "flex";
            document.body.style.overflow = "hidden";
            loader.style.display = "flex";

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
                    popup.querySelector(".tf-package-template-content").innerHTML = response;
                    $()
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

    // hotel date picker
    window.flatpickr.l10ns.default.firstDayOfWeek = 0;
    const regexMap = {
        'Y/m/d': /(\d{4}\/\d{2}\/\d{2}).*(\d{4}\/\d{2}\/\d{2})/,
        'd/m/Y': /(\d{2}\/\d{2}\/\d{4}).*(\d{2}\/\d{2}\/\d{4})/,
        'm/d/Y': /(\d{2}\/\d{2}\/\d{4}).*(\d{2}\/\d{2}\/\d{4})/,
        'Y-m-d': /(\d{4}-\d{2}-\d{2}).*(\d{4}-\d{2}-\d{2})/,
        'd-m-Y': /(\d{2}-\d{2}-\d{4}).*(\d{2}-\d{2}-\d{4})/,
        'm-d-Y': /(\d{2}-\d{2}-\d{4}).*(\d{2}-\d{2}-\d{4})/,
        'Y.m.d': /(\d{4}\.\d{2}\.\d{2}).*(\d{4}\.\d{2}\.\d{2})/,
        'd.m.Y': /(\d{2}\.\d{2}\.\d{4}).*(\d{2}\.\d{2}\.\d{4})/,
        'm.d.Y': /(\d{2}\.\d{2}\.\d{4}).*(\d{2}\.\d{2}\.\d{4})/
    };
    const dateRegex = regexMap[tf_package_data.user_date_format];

    $(document).on('focus click', ".tf-hotel-booking-sidebar #check-in-out-date", function() {
        if (!this._flatpickr) {
            this._flatpickr = flatpickr(this, {
                enableTime: false,
                mode: "range",
                minDate: "today",
                altInput: true,
                altFormat: tf_package_data.user_date_format,
                dateFormat: "Y/m/d",
                defaultDate: tf_package_data.check_in_out,
                onReady: function (selectedDates, dateStr, instance) {
                    instance.element.value = dateStr.replace(/(\d{4}\/\d{2}\/\d{2}).*(\d{4}\/\d{2}\/\d{2})/g, function (match, date1, date2) {
                        return `${date1} - ${date2}`;
                    });
                    instance.altInput.value = instance.altInput.value.replace( dateRegex, function (match, d1, d2) {
                        return `${d1} - ${d2}`;
                    });
                },
                onChange: function (selectedDates, dateStr, instance) {
                    instance.element.value = dateStr.replace(/(\d{4}\/\d{2}\/\d{2}).*(\d{4}\/\d{2}\/\d{2})/g, function (match, date1, date2) {
                        return `${date1} - ${date2}`;
                    });
                    instance.altInput.value = instance.altInput.value.replace( dateRegex, function (match, d1, d2) {
                        return `${d1} - ${d2}`;
                    });
                },
            });
        }
        this._flatpickr.open();
    });


    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url.includes('admin-ajax.php') && 
            settings.data && 
            settings.data.includes('tf_room_availability')) {
            if( $("#rooms").length > 0){
                $('.tf-package-template-content').animate({
                    scrollTop: $("#rooms").offset().top
                }, 500);
               
            }
        }
    });

});
