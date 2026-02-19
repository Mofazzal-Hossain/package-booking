<?php
get_header();

?>
<div class="tf-single-template__one tf-single-package">
    <div class="tf-container">
        <div class="tf-package-progressbar">
            <div class="progressbar">
                <li class="active"><?php echo esc_html__('Choose your stay', 'tourfic-package'); ?></li>
                <li><?php echo esc_html__('Package Flow', 'tourfic-package'); ?></li>
                <li><?php echo esc_html__('View your booking', 'tourfic-package'); ?></li>
            </div>
        </div>
        <div class="tf-package-content">
            <div class="tf-package-content-item hotels active">
                <div class="tf-package-list-content">
                    <?php require_once TF_PACKAGE_PLUGIN_PATH . '/inc/templates/tf-hotel-list-content.php'; ?>
                </div>
                <button class="tf-package-next">Next</button>
            </div>
            <div class="tf-package-content-item tours">
                <div class="tf-package-list-content">
                    <?php require_once TF_PACKAGE_PLUGIN_PATH . '/inc/templates/tf-tour-list-content.php'; ?>
                </div>

                <button class="tf-package-prev">Previous</button>
                <button class="tf-package-next">Next</button>
            </div>
            <div class="tf-package-content-item booking">
                <h3>Booking content</h3>
                <button class="tf-package-prev">Previous</button>
                <a href="<?php echo esc_url(home_url('/checkout')); ?>" class="tf-package-booking">checkout</a>
            </div>
        </div>
    </div>
</div>

<!-- Package details popup -->
<div class="tf-package-popup-overlay"></div>

<div class="tf-package-popup" id="tf-package-popup">
    <div class="tf-package-popup-inner">
        <button class="tf-package-popup-close"><i class="ri-close-large-line"></i></button>
        <div class="tf-package-popup-content">
            <div class="tf-post-details">
                <div class="skeleton-wrapper">
                    <span class="skeleton"></span>
                    <span class="skeleton"></span>
                    <span class="skeleton" style="width: 90%"></span>
                    <span class="skeleton"></span>
                    <span class="skeleton" style="width: 80%"></span>
                    <span class="skeleton" style="width: 50%"></span>
                    <span class="skeleton"></span>
                    <span class="skeleton" style="width: 80%"></span>
                    <span class="skeleton"></span>
                    <span class="skeleton"></span>
                    <span class="skeleton" style="width: 90%"></span>
                    <span class="skeleton"></span>
                    <span class="skeleton" style="width: 80%"></span>
                    <span class="skeleton" style="width: 50%"></span>
                    <span class="skeleton"></span>
                    <span class="skeleton" style="width: 80%"></span>
                    <span class="skeleton"></span>
                </div>
                <div class="tf-package-template-content"></div>
            </div>
        </div>
    </div>
</div>

<?php

get_footer();
