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
                <div class="tf-next-prev tf-first-step">
                    <button class="tf-package-next tf-hotels-next tf_btn"><?php echo esc_html__('Next', 'tourfic-package'); ?></button>
                </div>
            </div>
            <div class="tf-package-content-item tours">
                <div class="tf-package-list-content">
                    <?php require_once TF_PACKAGE_PLUGIN_PATH . '/inc/templates/tf-tour-list-content.php'; ?>
                </div>

                <div class="tf-next-prev tf-second-step">
                    <!-- <button class="tf-package-prev tf_btn tf-hotel-prev"></?php echo esc_html__('Previous', 'tourfic-package'); ?></button> -->
                    <button class="tf-package-next tf_btn tf-tour-next"><?php echo esc_html__('Next', 'tourfic-package'); ?></button>
                </div>
            </div>
            <div class="tf-package-content-item booking">
                <div class="tf-booking-content">
                    <?php if (function_exists('WC') && WC()->cart && ! WC()->cart->is_empty()) :

                        $cart_items = WC()->cart->get_cart();

                        // Get last 2 items
                        $latest_items = array_slice($cart_items, -2, 2, true);

                        echo '<div class="tf-booking-details">';

                        foreach ($latest_items as $cart_item_key => $cart_item) {

                            $product = $cart_item['data'];
                            $product_id = $cart_item['product_id'];
                            $post_type = get_post_type($product_id);

                            if (! $product || ! $product->exists()) {
                                continue;
                            }
                            if ($post_type === 'tf_hotel' && isset($cart_item['tf_hotel_data'])) {
                                $hotel = $cart_item['tf_hotel_data'];
                                echo '<div class="tf-booking-item">';
                                echo '<div class="tf-booking-thumb">';
                                echo $product->get_image('thumbnail');
                                echo '</div>';
                                echo '<div class"tf-booking-info">';
                                echo '<h3 class="tf-booking-title">';
                                echo esc_html($product->get_name());
                                echo '</h3>';
                                if ( ! empty( $hotel['price_total'] ) ) {
                                    echo '<strong>' . wc_price( $hotel['price_total'] ) . '</strong>';
                                } 
                                echo '<div class="tf-booking-meta">';

                                if (! empty($hotel['room_name'])) {
                                    echo '<div><strong>' . esc_html__('Room', 'tourfic-package') . ':</strong> ' . esc_html($hotel['room_name']) . '</div>';
                                }
                                if (! empty($hotel['option'])) {
                                    echo '<div><strong>' . esc_html__('Option', 'tourfic-package') . ':</strong> ' . esc_html($hotel['option']) . '</div>';
                                }
                                if (! empty($hotel['room'])) {
                                    echo '<div><strong>' . esc_html__('Number of Room Booked', 'tourfic-package') . ':</strong> ' . esc_html($hotel['room']) . '</div>';
                                }
                                if (! empty($hotel['child'])) {
                                    echo '<div><strong>' . esc_html__('Children', 'tourfic-package') . ':</strong> ' . esc_html($hotel['child']) . '</div>';
                                }
                                if (! empty($hotel['adult'])) {
                                    echo '<div><strong>' . esc_html__('Adults', 'tourfic-package') . ':</strong> ' . esc_html($hotel['adult']) . '</div>';
                                }
                                if (! empty($hotel['children_ages'])) {
                                    echo '<div><strong>' . esc_html__('Children Ages', 'tourfic-package') . ':</strong> ' . esc_html($hotel['children_ages']) . '</div>';
                                }
                                if (! empty($hotel['check_in'])) {
                                    echo '<div><strong>' . esc_html__('Check-in', 'tourfic-package') . ':</strong> ' . esc_html($hotel['check_in']) . '</div>';
                                }
                                if (! empty($hotel['check_out'])) {
                                    echo '<div><strong>' . esc_html__('Check-out', 'tourfic-package') . ':</strong> ' . esc_html($hotel['check_out']) . '</div>';
                                }

                                // Airport Service
                                if (! empty($hotel['air_serivice_avail']) && $hotel['air_serivice_avail'] == 1) {
                                    if (! empty($hotel['air_serivicetype'])) {
                                        switch ($hotel['air_serivicetype']) {
                                            case 'pickup':
                                                echo '<div><strong>' . esc_html__('Airport Service', 'tourfic-package') . ':</strong> ' . esc_html__('Airport Pickup', 'tourfic-package') . '</div>';
                                                break;
                                            case 'dropoff':
                                                echo '<div><strong>' . esc_html__('Airport Service', 'tourfic-package') . ':</strong> ' . esc_html__('Airport Dropoff', 'tourfic-package') . '</div>';
                                                break;
                                            case 'both':
                                                echo '<div><strong>' . esc_html__('Airport Service', 'tourfic-package') . ':</strong> ' . esc_html__('Airport Pickup & Dropoff', 'tourfic-package') . '</div>';
                                                break;
                                        }
                                    }
                                    if (! empty($hotel['air_service_info'])) {
                                        echo '<div><strong>' . esc_html__('Airport Service Fee', 'tourfic-package') . ':</strong> ' . wp_strip_all_tags(wc_price($hotel['air_service_info'])) . '</div>';
                                    }
                                }

                                // Hotel Extra
                                if (! empty($hotel['hotel_extra'])) {
                                    echo '<div><strong>' . esc_html__('Hotel Extra Service', 'tourfic-package') . ':</strong> ' . esc_html($hotel['hotel_extra']) . '</div>';
                                }
                                if (! empty($hotel['hotel_extra_price'])) {
                                    echo '<div><strong>' . esc_html__('Hotel Extra Service Fee', 'tourfic-package') . ':</strong> ' . wp_strip_all_tags(wc_price($hotel['hotel_extra_price'])) . '</div>';
                                }

                                // Due
                                if (isset($hotel['due'])) {
                                    echo '<div><strong>' . esc_html__('Due', 'tourfic-package') . ':</strong> ' . wp_strip_all_tags(wc_price($hotel['due'])) . '</div>';
                                }
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            } else if ($post_type === 'tf_tours' && isset($cart_item['tf_tours_data'])) {
                                $tour = $cart_item['tf_tours_data'];

                                // Assign variables
                                $tour_type        = ! empty($tour['tour_type']) ? $tour['tour_type'] : '';
                                $adults_number    = ! empty($tour['adults']) ? $tour['adults'] : '';
                                $childrens_number = ! empty($tour['childrens']) ? $tour['childrens'] : '';
                                $infants_number   = ! empty($tour['infants']) ? $tour['infants'] : '';
                                $start_date       = ! empty($tour['start_date']) ? $tour['start_date'] : '';
                                $end_date         = ! empty($tour['end_date']) ? $tour['end_date'] : '';
                                $tour_date        = ! empty($tour['tour_date']) ? $tour['tour_date'] : '';
                                $tour_time        = ! empty($tour['tour_time']) ? $tour['tour_time'] : '';
                                $tour_extra       = ! empty($tour['tour_extra_title']) ? $tour['tour_extra_title'] : '';
                                $package_title    = ! empty($tour['package_title']) ? $tour['package_title'] : '';
                                $due              = ! empty($tour['due']) ? $tour['due'] : null;

                                echo '<div class="tf-booking-item">';
                                echo '<div class="tf-booking-thumb">';
                                echo $product->get_image('thumbnail');
                                echo '</div>';
                                echo '<div class="tf-booking-info">';
                                echo '<h3 class="tf-booking-title">' . esc_html($product->get_name()) . '</h3>';
                                
                                if ( isset( $tour['price'] ) && ! empty( $tour['tour_extra_total'] ) ) {
                                    echo '<strong>' . wc_price( $tour['price'] + $tour['tour_extra_total'] ) . '</strong>';
                                } elseif ( isset( $tour['price'] ) && empty( $tour['tour_extra_total'] ) ) {
                                    echo '<strong>' . wc_price( $tour['price'] ) . '</strong>';
                                }
                                echo '<div class="tf-booking-meta">';

                                // Adults
                                if ($adults_number && $adults_number >= 1) {
                                    echo '<div><strong>' . esc_html__('Adults', 'tourfic-package') . ':</strong> ' . esc_html($adults_number) . '</div>';
                                }

                                // Children
                                if ($childrens_number && $childrens_number >= 1) {
                                    echo '<div><strong>' . esc_html__('Children', 'tourfic-package') . ':</strong> ' . esc_html($childrens_number) . '</div>';
                                }

                                // Infants
                                if ($infants_number && $infants_number >= 1) {
                                    echo '<div><strong>' . esc_html__('Infant', 'tourfic-package') . ':</strong> ' . esc_html($infants_number) . '</div>';
                                }

                                // Tour Date
                                if ($tour_type === 'fixed' && $start_date && $end_date) {
                                    echo '<div><strong>' . esc_html__('Tour Date', 'tourfic-package') . ':</strong> ' . esc_html($start_date . ' - ' . $end_date) . '</div>';
                                } elseif ($tour_type === 'continuous' && $tour_date) {
                                    echo '<div><strong>' . esc_html__('Tour Date', 'tourfic-package') . ':</strong> ' . esc_html(date_i18n("F j, Y", strtotime($tour_date))) . '</div>';
                                    if ($tour_time) {
                                        echo '<div><strong>' . esc_html__('Tour Time', 'tourfic-package') . ':</strong> ' . esc_html($tour_time) . '</div>';
                                    }
                                }

                                // Tour Extras
                                if ($tour_extra) {
                                    echo '<div><strong>' . esc_html__('Tour Extra', 'tourfic-package') . ':</strong> ' . esc_html($tour_extra) . '</div>';
                                }

                                // Package Title
                                if ($package_title) {
                                    echo '<div><strong>' . esc_html__('Package', 'tourfic-package') . ':</strong> ' . esc_html($package_title) . '</div>';
                                }

                                // Due
                                if (! empty($due)) {
                                    echo '<div><strong>' . esc_html__('Due', 'tourfic-package') . ':</strong> ' . wp_strip_all_tags(wc_price($due)) . '</div>';
                                }

                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }

                        echo '</div>';

                    endif; ?>
                </div>
                <div class="tf-next-prev tf-third-step">
                    <!-- <button class="tf-package-prev tf_btn tf-tour-prev"></?php echo esc_html__('Previous', 'tourfic-package'); ?></button> -->
                    <a href="<?php echo esc_url(home_url('/checkout/')); ?>" class="tf-package-booking tf_btn"><?php echo esc_html__('Checkout', 'tourfic-package'); ?></a>
                </div>
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
