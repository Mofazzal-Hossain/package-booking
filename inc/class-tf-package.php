<?php
defined('ABSPATH') || exit;

use Tourfic\Classes\Helper;

class TF_Package
{

    public function __construct()
    {
        // init
        add_action('init', [$this, 'tf_package_init']);

        // package single template
        add_filter('template_include', [$this, 'tf_package_template']);

        // filter tf options post type
        add_filter('tf-options-post-type', [$this, 'tf_package_post_type']);

        // package scripts
        add_action('wp_enqueue_scripts', [$this, 'tf_package_scripts']);

        // load single template
        add_action('wp_ajax_tf_load_single_template', [$this, 'tf_load_single_template']);
        add_action('wp_ajax_nopriv_tf_load_single_template', [$this, 'tf_load_single_template']);

        // body classes
        add_filter('body_class', [$this, 'tf_package_body_class']);

        // filters
        add_filter('tf_services_list', [$this, 'tf_package_services_list']);
        add_filter('tf_ask_question_post_types', [$this, 'tf_package_ask_question_post_types']);

        // booking data
        add_action('wp_ajax_tf_package_booking_data', [$this, 'tf_package_booking_data']);
        add_action('wp_ajax_nopriv_tf_package_booking_data', [$this, 'tf_package_booking_data']);
    }


    // Initialize package post type metabox
    public function tf_package_init()
    {

        if (class_exists('TF_Metabox')) {
            require_once TF_PACKAGE_PLUGIN_PATH . 'inc/tf-package-metabox.php';
        }

        // review star rating
        if (!function_exists('tf_review_star_rating')) {
            function tf_review_star_rating($tf_rating)
            {
                $full_star = floor($tf_rating);
                $half_star = ($tf_rating - $full_star) >= 0.5 ? 1 : 0;
                $empty_star = 5 - $full_star - $half_star;

                $output = '<span class="tft-destination-rating tft-color-primary">';
                for ($i = 0; $i < $full_star; $i++) {
                    $output .= '<i class="ri-star-fill"></i>';
                }
                if ($half_star) {
                    $output .= '<i class="ri-star-half-line"></i>';
                }
                for ($i = 0; $i < $empty_star; $i++) {
                    $output .= '<i class="ri-star-line"></i>';
                }
                $output .= '</span>';
                return $output;
            }
        }
    }

    // Add package template to tf-template-include filter
    public function tf_package_template($template)
    {
        // package single
        if (is_singular('tf_package')) {
            $plugin_template = TF_PACKAGE_PLUGIN_PATH . 'inc/tf-single-package.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }

        // package archive
        if (is_post_type_archive('tf_package')) {
            return TF_PACKAGE_PLUGIN_PATH . 'inc/tf-package-archive.php';
        } 

        return $template;
    }

    // Add package post type to tf-options-post-type filter
    public function tf_package_post_type($post_types)
    {
        $post_types[] = 'tf_package';
        return $post_types;
    }

    // enqueue scripts
    public function tf_package_scripts()
    {
        if (is_singular('tf_package') || is_archive('tf_package')) {

            $hotel_date_format_for_users   = ! empty(Helper::tfopt("tf-date-format-for-users")) ? Helper::tfopt("tf-date-format-for-users") : "Y/m/d";
            $check_in_out = ! empty($_GET['check-in-out-date']) ? sanitize_text_field($_GET['check-in-out-date']) : '';

            wp_enqueue_style('tf-package-css', TF_PACKAGE_PLUGIN_URL . 'assets/css/tf-package.css', array(), time(), 'all');
            wp_enqueue_script('tf-package-js', TF_PACKAGE_PLUGIN_URL . 'assets/js/tf-package.js', array('jquery'), time(), true);
            wp_localize_script('tf-package-js', 'tf_package_data', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('tf_load_single_template'),
                'booking_nonce'   => wp_create_nonce('tf_package_booking_data'),
                'user_date_format' => $hotel_date_format_for_users,
                'checkInOut' => explode('-', $check_in_out),
            ));
        }
    }

    // load single template
    public function tf_load_single_template()
    {
        global $post, $wp_query, $withcomments;
        $post_id   = intval($_POST['post_id']);
        $post_type = sanitize_text_field($_POST['post_type']);

        if (!wp_verify_nonce($_POST['nonce'], 'tf_load_single_template')) {
            wp_die();
        }

        if (!$post_id || !$post_type) {
            wp_die();
        }

        global $post;
        $post = get_post($post_id);

        if (!$post || $post->post_type !== $post_type) {
            wp_die();
        }

        setup_postdata($post);
        $withcomments = true;
        $wp_query->is_single = true;
        $wp_query->queried_object = $post;
        $wp_query->queried_object_id = $post->ID;

        ob_start();
        if ($post_type === 'tf_hotel') {
            include TF_PACKAGE_PLUGIN_PATH . '/inc/templates/single/hotel-single.php';
        }

        if ($post_type === 'tf_tours') {
            include TF_PACKAGE_PLUGIN_PATH . '/inc/templates/single/tour-single.php';
        }
        $html = ob_get_clean();

        // tour availability
        $single_tour_form_data = array();

        $meta = get_post_meta($post_id, 'tf_tours_opt', true);
        $tour_type                  = ! empty($meta['type']) ? $meta['type'] : '';
        $tour_date_format_for_users = ! empty(Helper::tfopt("tf-date-format-for-users")) ? Helper::tfopt("tf-date-format-for-users") : "Y/m/d";

        $tour_availability          = ! empty($meta['tour_availability']) ? json_decode($meta['tour_availability']) : '';

        // Same Day Booking
        $disable_same_day = ! empty($meta['disable_same_day']) ? $meta['disable_same_day'] : '';
        $single_tour_form_data['first_day_of_week'] = !empty(Helper::tfopt("tf-week-day-flatpickr")) ? Helper::tfopt("tf-week-day-flatpickr") : 0;
        $single_tour_form_data['date_format']      = esc_html($tour_date_format_for_users);
        $single_tour_form_data['flatpickr_locale'] = ! empty(get_locale()) ? get_locale() : 'en_US';
        if ($tour_type == 'fixed') {
            $tour_availability          = ! empty($meta['tour_availability']) ? json_decode($meta['tour_availability'], true) : '';

            $expanded = [];
            if (!empty($tour_availability) && is_array($tour_availability)) {
                foreach ($tour_availability as $range_key => $data) {
                    if (empty($data['check_in']) || empty($data['check_out'])) {
                        continue;
                    }
                    // copy original data and set check_in/check_out to the single date
                    $entry = $data;
                    $key = $data['check_in'] . ' - ' . $data['check_in'];
                    $entry['check_in']  = $data['check_in'];
                    $entry['check_out'] = $data['check_in'];
                    $expanded[$key] = $entry;
                }
            }
            $tour_availability =  $expanded;
        }
        $single_tour_form_data['disable_same_day'] = $disable_same_day;
        $single_tour_form_data['tour_availability'] = $tour_availability;
        $single_tour_form_data['is_all_unavailable'] = Helper::is_all_unavailable($tour_availability);

        wp_reset_postdata();
        wp_send_json_success([
            'html' => $html,
            'tour_form_data' => $single_tour_form_data
        ]);
        wp_die();
    }

    // add body class
    public function tf_package_body_class($classes)
    {
        if (is_singular('tf_package')) {
            $classes[] = 'single-tf_hotel single-tf_tours';
        }
        return $classes;
    }

    // add package to services list
    public function tf_package_services_list($services)
    {
        if (is_singular('tf_package')) {
            $services['hotel'] = 'tf_package';
            $services['tour'] = 'tf_package';
        }
        return $services;
    }
    public function tf_package_ask_question_post_types($post_types)
    {
        if (is_singular('tf_package')) {
            $post_types[] = 'tf_package';
        }
        return $post_types;
    }

    public function tf_package_booking_data(){
        if (!wp_verify_nonce($_POST['nonce'], 'tf_package_booking_data')) {
            wp_die();
        }
        $hotel_booking_id = ! empty($_POST['hotel_booking_id']) ? intval($_POST['hotel_booking_id']) : '';
        $tour_booking_id = ! empty($_POST['tour_booking_id']) ? intval($_POST['tour_booking_id']) : '';
        
        if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
            wp_send_json_error(['message' => 'Cart empty']);
        }

        $cart_items = array_reverse(WC()->cart->get_cart(), true);

        $latest_hotel = null;
        $latest_tour  = null; 

        foreach ($cart_items as $cart_item_key => $cart_item) {

            $product_id = $cart_item['product_id'];


            // Save latest hotel match
            if ($product_id == $hotel_booking_id && isset($cart_item['tf_hotel_data']) ) {
                $latest_hotel = $cart_item; 
            }

            // Save latest tour match
            if ($product_id == $tour_booking_id && isset($cart_item['tf_tours_data']) ) {
                $latest_tour = $cart_item;
            }
            if ($latest_hotel && $latest_tour) {
                break; 
            }
            
        }

        ob_start();
        echo '<div class="tf-booking-details">';

        if ($latest_hotel) {
            $product = $latest_hotel['data'];
            $hotel = $latest_hotel['tf_hotel_data'];

            echo '<div class="tf-booking-item">';
            echo '<div class="tf-booking-thumb">';
            echo wp_kses_post( $product->get_image( 'thumbnail' ) );
            echo '</div>';
            echo '<div class"tf-booking-info">';
            echo '<h3 class="tf-booking-title">';
            echo esc_html($product->get_name());
            echo '</h3>';
            if (! empty($hotel['price_total'])) {
                echo '<strong>' . wp_kses_post( wc_price( $hotel['price_total'] ) ) . '</strong>';
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
                    echo '<div><strong>' . esc_html__('Airport Service Fee', 'tourfic-package') . ':</strong> ' . esc_html($hotel['air_service_info']) . '</div>';
                }
            }

            // Hotel Extra
            if (! empty($hotel['hotel_extra'])) {
                echo '<div><strong>' . esc_html__('Hotel Extra Service', 'tourfic-package') . ':</strong> ' . esc_html($hotel['hotel_extra']) . '</div>';
            }
            if (! empty($hotel['hotel_extra_price'])) {
                echo '<div><strong>' . esc_html__('Hotel Extra Service Fee', 'tourfic-package') . ':</strong> ' . wp_kses_post(wc_price($hotel['hotel_extra_price'])) . '</div>';
            }

            // Due
            if (isset($hotel['due'])) {
                echo '<div><strong>' . esc_html__('Due', 'tourfic-package') . ':</strong> ' . wp_kses_post(wc_price($hotel['due'])) . '</div>';
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        } 
        if ($latest_tour) {
            $product = $latest_tour['data'];
            $tour = $latest_tour['tf_tours_data'];

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
            echo wp_kses_post( $product->get_image( 'thumbnail' ) );
            echo '</div>';
            echo '<div class="tf-booking-info">';
            echo '<h3 class="tf-booking-title">' . esc_html($product->get_name()) . '</h3>';

            if (isset($tour['price']) && ! empty($tour['tour_extra_total'])) {
                echo '<strong>' . wp_kses_post( wc_price( $tour['price'] + $tour['tour_extra_total'] ) ) . '</strong>';
            } elseif (isset($tour['price']) && empty($tour['tour_extra_total'])) {
                echo '<strong>' . wp_kses_post( wc_price( $tour['price'] ) ) . '</strong>';
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
                echo '<div><strong>' . esc_html__('Tour Extra', 'tourfic-package') . ':</strong> ' . wp_kses_post($tour_extra) . '</div>';
            }

            // Package Title
            if ($package_title) {
                echo '<div><strong>' . esc_html__('Package', 'tourfic-package') . ':</strong> ' . esc_html($package_title) . '</div>';
            }

            // Due
            if (! empty($due)) {
                echo '<div><strong>' . esc_html__('Due', 'tourfic-package') . ':</strong> ' . wp_kses_post(wc_price($due)) . '</div>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';

        $output = ob_get_clean();
        wp_send_json_success(['booking_content' => $output]);
        wp_die();
    }
}

new TF_Package();
