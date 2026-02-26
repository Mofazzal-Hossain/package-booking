<?php
// Don't load directly
defined('ABSPATH') || exit;

use \Tourfic\Classes\Helper;
use \Tourfic\Classes\Tour\Tour;
use \Tourfic\Classes\Tour\Tour_Price;
use \Tourfic\Classes\Tour\Pricing;
use \Tourfic\App\Wishlist;

$post_id = get_the_ID();

// Get Tour Meta
$meta = get_post_meta($post_id, 'tf_tours_opt', true);

/**
 * Show/hide sections
 */
$disable_review_sec   = ! empty($meta['t-review']) ? $meta['t-review'] : '';
$disable_related_tour = ! empty($meta['t-related']) ? $meta['t-related'] : '';
$disable_wishlist_tour = ! empty($meta['t-wishlist']) ? $meta['t-wishlist'] : 0;

/**
 * Get global settings value
 */
$s_review  = ! empty(Helper::tfopt('t-review')) ? Helper::tfopt('t-review') : '';
$s_related = ! empty(Helper::tfopt('t-related')) ? Helper::tfopt('t-related') : '';

/**
 * Disable Review Section
 */
$disable_review_sec = ! empty($disable_review_sec) ? $disable_review_sec : $s_review;

/**
 * Disable Related Tour
 */
$disable_related_tour = ! empty($disable_related_tour) ? $disable_related_tour : $s_related;


// Get destination
$destinations           = get_the_terms($post_id, 'tour_destination');
$first_destination_slug = ! empty($destinations) ? $destinations[0]->slug : '';

// Wishlist
$post_type       = substr(get_post_type(), 3, -1);
$has_in_wishlist = Wishlist::tf_has_item_in_wishlist($post_id);

// tour type meta
$tour_type = ! empty($meta['type']) ? $meta['type'] : '';

// date format for users
$tf_tour_date_format_for_users  = !empty(Helper::tfopt("tf-date-format-for-users")) ? Helper::tfopt("tf-date-format-for-users") : "Y/m/d";


if (!function_exists('tf_fixed_tour_start_date_changer')) {
    function tf_fixed_tour_start_date_changer($date, $months)
    {
        if ((count($months) > 0) && !empty($date)) {
            preg_match('/(\d{4})\/(\d{2})\/(\d{2})/', $date, $matches);

            $new_months[] = $matches[0];

            foreach ($months as $month) {

                if ($month < gmdate('m')) {
                    $year = $matches[1] + 1;
                } else $year = $matches[1];

                $day_selected = gmdate('d', strtotime($date));
                $last_day_of_month = gmdate('t', strtotime(gmdate('Y') . '-' . $month . '-01'));
                $matches[2] = $month;
                $changed_date = sprintf("%s/%s/%s", $year, $matches[2], $matches[3]);

                if (($day_selected == "31") && ($last_day_of_month != "31")) {
                    $new_months[] = gmdate('Y/m/d', strtotime($changed_date . ' -1 day'));
                } else {
                    $new_months[] = $changed_date;
                }
            }
            return $new_months;
        } else return array();
    }
}

//Social Share
$share_text = get_the_title();
$share_link = get_permalink($post_id);
$disable_share_opt  = ! empty($meta['t-share']) ? $meta['t-share'] : '';
$t_share  = ! empty(Helper::tfopt('t-share')) ? Helper::tfopt('t-share') : 0;
$disable_share_opt = ! empty($disable_share_opt) ? $disable_share_opt : $t_share;
$tf_tour_single_book_now_text = isset($meta['single_tour_booking_form_button_text']) && ! empty($meta['single_tour_booking_form_button_text']) ? stripslashes(sanitize_text_field($meta['single_tour_booking_form_button_text'])) : esc_html__("Book Now", 'tourfic');

// Location
if (!empty($meta['location']) && Helper::tf_data_types($meta['location'])) {
    $location = !empty(Helper::tf_data_types($meta['location'])['address']) ? Helper::tf_data_types($meta['location'])['address'] : '';

    $location_latitude = !empty(Helper::tf_data_types($meta['location'])['latitude']) ? Helper::tf_data_types($meta['location'])['latitude'] : '';
    $location_longitude = !empty(Helper::tf_data_types($meta['location'])['longitude']) ? Helper::tf_data_types($meta['location'])['longitude'] : '';
    $location_zoom = !empty(Helper::tf_data_types($meta['location'])['zoom']) ? Helper::tf_data_types($meta['location'])['zoom'] : '';
}
// Gallery
$gallery = ! empty($meta['tour_gallery']) ? $meta['tour_gallery'] : array();
if ($gallery) {
    $gallery_ids = explode(',', $gallery);
}
$hero_title = ! empty($meta['hero_title']) ? $meta['hero_title'] : '';

// Map Type
$tf_openstreet_map = ! empty(Helper::tfopt('google-page-option')) ? Helper::tfopt('google-page-option') : "default";
$tf_google_map_key = !empty(Helper::tfopt('tf-googlemapapi')) ? Helper::tfopt('tf-googlemapapi') : '';

// Highlights
$highlights = ! empty($meta['additional_information']) ? $meta['additional_information'] : '';
// Informations
$tour_duration = ! empty($meta['duration']) ? $meta['duration'] : '';
$tour_refund_policy = ! empty($meta['refund_des']) ? $meta['refund_des'] : '';
$info_tour_type = ! empty($meta['tour_types']) ? $meta['tour_types'] : [];
$duration_time = ! empty($meta['duration_time']) ? $meta['duration_time'] : 'Day';
$night         = ! empty($meta['night']) ? $meta['night'] : false;
$night_count   = ! empty($meta['night_count']) ? $meta['night_count'] : '';
$group_size    = ! empty($meta['group_size']) ? $meta['group_size'] : '';
$language      = ! empty($meta['language']) ? $meta['language'] : '';
$email         = ! empty($meta['email']) ? $meta['email'] : '';
$phone         = ! empty($meta['phone']) ? $meta['phone'] : '';
$fax           = ! empty($meta['fax']) ? $meta['fax'] : '';
$website       = ! empty($meta['website']) ? $meta['website'] : '';
$itinerary_map = ! empty(Helper::tfopt('itinerary_map')) && function_exists('is_tf_pro') && is_tf_pro() ? Helper::tfopt('itinerary_map') : 0;
$vendor_contact_info = !empty(Helper::tfopt("multi-vendor-setings")["vendor-contact-info"]) ? Helper::tfopt("multi-vendor-setings")["vendor-contact-info"] : 0;
$author = !empty(get_userdata(get_post()->post_author)) ? get_userdata(get_post()->post_author) : array();

if ((is_plugin_active("tourfic-vendor/tourfic-vendor.php"))) {

    if ($vendor_contact_info == 1) {
        if (in_array('tf_vendor', $author->roles)) {
            $email = !empty(Helper::tfopt("multi-vendor-setings")["email"]) ? Helper::tfopt("multi-vendor-setings")["email"] : "";
            $phone = !empty(Helper::tfopt("multi-vendor-setings")["phone"]) ? Helper::tfopt("multi-vendor-setings")["phone"] : "";
            $fax = !empty(Helper::tfopt("multi-vendor-setings")["fax"]) ? Helper::tfopt("multi-vendor-setings")["fax"] : "";
            $website = !empty(Helper::tfopt("multi-vendor-setings")["website"]) ? Helper::tfopt("multi-vendor-setings")["website"] : "";
        }
    }
}

/**
 * Get features
 * hotel_feature
 */
$features = ! empty(get_the_terms($post_id, 'tour_features')) ? get_the_terms($post_id, 'tour_features') : '';

$min_days = ! empty($meta['min_days']) ? $meta['min_days'] : '';

$faqs            = !empty($meta['faqs']) ? $meta['faqs'] : null;
if (!empty($faqs) && gettype($faqs) == "string") {
    $tf_hotel_faqs_value = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($match) {
        return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
    }, $faqs);
    $faqs = unserialize($tf_hotel_faqs_value);
}
$inc             = !empty($meta['inc']) ? $meta['inc'] : null;
if (!empty($inc) && gettype($inc) == "string") {
    $tf_hotel_inc_value = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($match) {
        return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
    }, $inc);
    $inc = unserialize($tf_hotel_inc_value);
}
$exc             = !empty($meta['exc']) ? $meta['exc'] : null;
if (!empty($exc) && gettype($exc) == "string") {
    $tf_hotel_exc_value = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($match) {
        return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
    }, $exc);
    $exc = unserialize($tf_hotel_exc_value);
}

$inc_icon        = ! empty($meta['inc_icon']) ? $meta['inc_icon'] : null;
$exc_icon        = ! empty($meta['exc_icon']) ? $meta['exc_icon'] : null;
$custom_inc_icon = ! empty($inc_icon) ? "custom-inc-icon" : '';
$custom_exc_icon = ! empty($exc_icon) ? "custom-exc-icon" : '';
$itineraries     = !empty($meta['itinerary']) ? $meta['itinerary'] : null;
if (!empty($itineraries) && gettype($itineraries) == "string") {
    $tf_hotel_itineraries_value = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($match) {
        return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
    }, $itineraries);
    $itineraries = unserialize($tf_hotel_itineraries_value);
}

$terms_and_conditions = ! empty($meta['terms_conditions']) ? $meta['terms_conditions'] : '';
$tf_faqs              = (get_post_meta($post->ID, 'tf_faqs', true)) ? get_post_meta($post->ID, 'tf_faqs', true) : array();

/**
 * Review query
 */
$args           = array(
    'post_id' => $post_id,
    'status'  => 'approve',
    'type'    => 'comment',
);
$comments_query = new WP_Comment_Query($args);
$comments       = $comments_query->comments;

/**
 * Pricing
 */
$pricing_rule = ! empty($meta['pricing']) ? $meta['pricing'] : '';
$tour_type    = ! empty($meta['type']) ? $meta['type'] : '';
$discount_type  = ! empty($meta['discount_type']) ? $meta['discount_type'] : 'none';
$disable_adult  = ! empty($meta['disable_adult_price']) ? $meta['disable_adult_price'] : false;
$disable_child  = ! empty($meta['disable_child_price']) ? $meta['disable_child_price'] : false;
$disable_infant = ! empty($meta['disable_infant_price']) ? $meta['disable_infant_price'] : false;

# Get Pricing
$group_price    = ! empty($meta['group_price']) ? $meta['group_price'] : 0;
$adult_price    = ! empty($meta['adult_price']) ? $meta['adult_price'] : 0;
$children_price = ! empty($meta['child_price']) ? $meta['child_price'] : 0;
$infant_price   = ! empty($meta['infant_price']) ? $meta['infant_price'] : 0;
$tour_price = new Tour_Price($meta);

// Single Template
$tf_tour_layout_conditions = ! empty($meta['tf_single_tour_layout_opt']) ? $meta['tf_single_tour_layout_opt'] : 'global';
if ("single" == $tf_tour_layout_conditions) {
    $tf_tour_single_template = ! empty($meta['tf_single_tour_template']) ? $meta['tf_single_tour_template'] : 'design-1';
}
$tf_tour_global_template = ! empty(Helper::tf_data_types(Helper::tfopt('tf-template'))['single-tour']) ? Helper::tf_data_types(Helper::tfopt('tf-template'))['single-tour'] : 'design-1';
$tf_tour_selected_check = !empty($tf_tour_single_template) ? $tf_tour_single_template : $tf_tour_global_template;

$tf_tour_selected_template = $tf_tour_selected_check;

$tour_duration_icon = ! empty($meta['tf-tour-duration-icon']) ? $meta['tf-tour-duration-icon'] : 'ri-history-line';
$tour_type_icon = ! empty($meta['tf-tour-type-icon']) ? $meta['tf-tour-type-icon'] : 'ri-menu-unfold-line';
$tour_group_icon = ! empty($meta['tf-tour-group-icon']) ? $meta['tf-tour-group-icon'] : 'ri-team-line';
$tour_lang_icon = ! empty($meta['tf-tour-lang-icon']) ? $meta['tf-tour-lang-icon'] : 'ri-global-line';

$tf_booking_type = '1';
$tf_booking_url = $tf_booking_query_url = $tf_booking_attribute = $tf_hide_booking_form = $tf_hide_price = '';
if (function_exists('is_tf_pro') && is_tf_pro()) {
    $tf_booking_type      = ! empty($meta['booking-by']) ? $meta['booking-by'] : 1;
    $tf_booking_url       = ! empty($meta['booking-url']) ? esc_url($meta['booking-url']) : '';
    $tf_booking_query_url = ! empty($meta['booking-query']) ? $meta['booking-query'] : 'adult={adult}&child={child}&infant={infant}';
    $tf_booking_attribute = ! empty($meta['booking-attribute']) ? $meta['booking-attribute'] : '';
    $tf_hide_booking_form = ! empty($meta['hide_booking_form']) ? $meta['hide_booking_form'] : '';
    $tf_hide_price        = ! empty($meta['hide_price']) ? $meta['hide_price'] : '';
}
if (2 == $tf_booking_type && !empty($tf_booking_url)) {
    $external_search_info = array(
        '{adult}'    => !empty($adults) ? $adults : 1,
        '{child}'    => !empty($children) ? $children : 0,
        '{infant}'     => !empty($infant) ? $infant : 0,
        '{booking_date}' => !empty($tour_date) ? $tour_date : '',
    );
    if (!empty($tf_booking_attribute)) {
        $tf_booking_query_url = str_replace(array_keys($external_search_info), array_values($external_search_info), $tf_booking_query_url);
        if (!empty($tf_booking_query_url)) {
            $tf_booking_url = $tf_booking_url . '/?' . $tf_booking_query_url;
        }
    }
}

?>
<div class="tf-single-template__one">
    <div class="tf-tour-single">
        <div class="tf-container">
            <div class="tf-container-inner">
                <!-- Single Tour Heading Section start -->
                <div class="tf-section tf-single-head">
                    <div class="tf-head-info tf-flex tf-flex-space-bttn tf-flex-gap-24">
                        <div class="tf-head-title">
                            <h1><?php the_title(); ?></h1>
                            <div class="tf-title-meta tf-flex tf-flex-align-center tf-flex-gap-8">
                                <?php if (!empty($location)) { ?>
                                    <i class="fa-solid fa-location-dot"></i>
                                <?php
                                    echo '<a href="#tf-tour-map">' . wp_kses_post($location) . '.</a>';
                                }; ?>
                            </div>
                        </div>
                        <div class="tf-head-social tf-flex tf-flex-gap-8 tf-flex-align-center">
                            <?php
                            // Wishlist
                            if ($disable_wishlist_tour == 0) {

                                if (is_user_logged_in()) {
                                    if (Helper::tfopt('wl-for') && in_array('li', Helper::tfopt('wl-for'))) { ?>
                                        <div class="tf-icon tf-wishlist-box">
                                            <i class="<?php echo $has_in_wishlist ? 'fas fa-heart tf-text-red remove-wishlist' : 'far fa-heart-o add-wishlist' ?>"
                                                data-icon="far fa-heart-o" data-active-icon="fas fa-heart"
                                                data-nonce="<?php echo esc_attr(wp_create_nonce("wishlist-nonce")) ?>" data-id="<?php echo esc_attr($post_id) ?>" data-type="<?php echo esc_attr($post_type) ?>" <?php if (Helper::tfopt('wl-page')) {
                                                                                                                                                                                                                        echo 'data-page-title="' . esc_attr(get_the_title(Helper::tfopt('wl-page'))) . '" data-page-url="' . esc_url(get_permalink(Helper::tfopt('wl-page'))) . '"';
                                                                                                                                                                                                                    } ?>></i>
                                        </div>
                                    <?php }
                                } else {
                                    if (Helper::tfopt('wl-for') && in_array('lo', Helper::tfopt('wl-for'))) { ?>
                                        <div class="tf-icon tf-wishlist-box">
                                            <i class="<?php echo $has_in_wishlist ? 'fas fa-heart tf-text-red remove-wishlist' : 'far fa-heart-o add-wishlist' ?>"
                                                data-icon="far fa-heart-o" data-active-icon="fas fa-heart"
                                                data-nonce="<?php echo esc_attr(wp_create_nonce("wishlist-nonce")) ?>" data-id="<?php echo esc_attr($post_id) ?>"
                                                data-type="<?php echo esc_attr($post_type) ?>" <?php if (Helper::tfopt('wl-page')) {
                                                                                                    echo 'data-page-title="' . esc_attr(get_the_title(Helper::tfopt('wl-page'))) . '" data-page-url="' . esc_url(get_permalink(Helper::tfopt('wl-page'))) . '"';
                                                                                                } ?>></i>
                                        </div>
                                <?php }
                                } ?>
                                <?php } else {
                                if (Helper::tfopt('wl-bt-for') && in_array('2', Helper::tfopt('wl-bt-for'))) {
                                    if (is_user_logged_in()) {
                                        if (Helper::tfopt('wl-for') && in_array('li', Helper::tfopt('wl-for'))) {
                                ?>
                                            <div class="tf-icon tf-wishlist-box">
                                                <i class="<?php echo $has_in_wishlist ? 'fas fa-heart tf-text-red remove-wishlist' : 'far fa-heart-o add-wishlist' ?>"
                                                    data-icon="far fa-heart-o" data-active-icon="fas fa-heart"
                                                    data-nonce="<?php echo esc_attr(wp_create_nonce("wishlist-nonce")) ?>" data-id="<?php echo esc_attr($post_id) ?>" data-type="<?php echo esc_attr($post_type) ?>" <?php if (Helper::tfopt('wl-page')) {
                                                                                                                                                                                                                            echo 'data-page-title="' . esc_attr(get_the_title(Helper::tfopt('wl-page'))) . '" data-page-url="' . esc_url(get_permalink(Helper::tfopt('wl-page'))) . '"';
                                                                                                                                                                                                                        } ?>></i>
                                            </div>
                                        <?php }
                                    } else {
                                        if (Helper::tfopt('wl-for') && in_array('lo', Helper::tfopt('wl-for'))) {
                                        ?>
                                            <div class="tf-icon tf-wishlist-box">
                                                <i class="<?php echo $has_in_wishlist ? 'fas fa-heart tf-text-red remove-wishlist' : 'far fa-heart-o add-wishlist' ?>"
                                                    data-icon="far fa-heart-o" data-active-icon="fas fa-heart"
                                                    data-nonce="<?php echo esc_attr(wp_create_nonce("wishlist-nonce")) ?>" data-id="<?php echo esc_attr($post_id) ?>"
                                                    data-type="<?php echo esc_attr($post_type) ?>" <?php if (Helper::tfopt('wl-page')) {
                                                                                                        echo 'data-page-title="' . esc_attr(get_the_title(Helper::tfopt('wl-page'))) . '" data-page-url="' . esc_url(get_permalink(Helper::tfopt('wl-page'))) . '"';
                                                                                                    } ?>></i>
                                            </div>
                            <?php }
                                    }
                                }
                            } ?>

                            <!-- Share Section -->
                            <?php if (! $disable_share_opt == '1') { ?>
                                <div class="tf-share">
                                    <a href="#dropdown-share-center" class="share-toggle tf-icon tf-social-box"
                                        data-toggle="true">
                                        <i class="ri-share-line"></i>
                                    </a>
                                    <div id="dropdown-share-center" class="share-tour-content">
                                        <div class="tf-dropdown-share-content">
                                            <h4><?php esc_html_e("Share with friends", "tourfic"); ?></h4>
                                            <ul>
                                                <li>
                                                    <a href="http://www.facebook.com/share.php?u=<?php echo esc_url($share_link); ?>"
                                                        class="tf-dropdown-item" target="_blank">
                                                        <span class="tf-dropdown-item-content">
                                                            <i class="fab fa-facebook"></i>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="http://twitter.com/share?text=<?php echo esc_attr($share_text); ?>&url=<?php echo esc_url($share_link); ?>"
                                                        class="tf-dropdown-item" target="_blank">
                                                        <span class="tf-dropdown-item-content">
                                                            <i class="fab fa-twitter-square"></i>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="https://www.linkedin.com/cws/share?url=<?php echo esc_url($share_link); ?>"
                                                        class="tf-dropdown-item" target="_blank">
                                                        <span class="tf-dropdown-item-content">
                                                            <i class="fab fa-linkedin"></i>
                                                        </span>
                                                    </a>
                                                </li>
                                                <?php $share_image_link = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full'); ?>
                                                <li>
                                                    <a href="http://pinterest.com/pin/create/button/?url=<?php echo esc_url($share_link); ?>&media=<?php echo esc_url(get_the_post_thumbnail_url()); ?>&description=<?php echo esc_attr($share_text); ?>"
                                                        class="tf-dropdown-item" target="_blank">
                                                        <span class="tf-dropdown-item-content">
                                                            <i class="fab fa-pinterest"></i>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <div title="<?php esc_attr_e('Share this link', 'tourfic'); ?>"
                                                        aria-controls="share_link_button">
                                                        <button id="share_link_button" class="tf_btn tf_btn_small share-center-copy-cta" tabindex="0"
                                                            role="button">
                                                            <i class="fa fa-link" aria-hidden="true"></i>

                                                            <span class="tf-button-text share-center-copied-message"><?php esc_html_e('Link Copied!', 'tourfic'); ?></span>
                                                        </button>
                                                        <input type="text" id="share_link_input"
                                                            class="share-center-url share-center-url-input"
                                                            value="<?php echo esc_attr($share_link); ?>" readonly>

                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            <?php } ?>
                            <!-- End Share Section -->
                        </div>
                    </div>
                </div>
                <!-- Single Tour Heading Section End -->

                <!-- Single Tour Body details start -->
                <div class="tf-single-details-wrapper tf-mt-30">
                    <div class="tf-single-details-inner tf-flex">
                        <div class="tf-tour-details-left">
                            <?php
                            $avail_prices = Pricing::instance($post_id)->get_avail_price();
                            if (! empty(Helper::tf_data_types(Helper::tfopt('tf-template'))['single-tour-layout'])) {
                                foreach (Helper::tf_data_types(Helper::tfopt('tf-template'))['single-tour-layout'] as $section) {
                                    if (! empty($section['status']) && $section['status'] == "1" && ! empty($section['slug'])) {
                                        include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/' . $section['slug'] . '.php';
                                    }
                                }
                            } else {
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/gallery.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/price.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/description.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/information.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/highlights.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/include-exclude.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/itinerary.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/map.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/faq.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/trams-condition.php';
                                include TF_PACKAGE_TEMPLATE_PATH . 'single/tour/review.php';
                            }
                            ?>
                        </div>

                        <!-- SIdebar Tour single -->
                        <div class="tf-tour-details-right">
                            <div class="tf-tour-booking-box tf-box">
                                <?php
                                $hide_price = !empty(Helper::tfopt('t-hide-start-price')) ? Helper::tfopt('t-hide-start-price') : '';
                                if (($tf_booking_type == 2 && $tf_hide_price !== '1') || $tf_booking_type == 1 || $tf_booking_type == 3) :
                                    if (isset($hide_price) && $hide_price !== '1') : ?>
                                        <!-- Tourfic Pricing Head -->
                                        <div class="tf-booking-form-data">
                                            <div class="tf-booking-block">
                                                <div class="tf-booking-price">
                                                    <?php
                                                    $tour_price = [];
                                                    $tf_pricing_rule = ! empty($meta['pricing']) ? $meta['pricing'] : '';
                                                    $tour_single_price_settings = !empty(Helper::tfopt('tour_archive_price_minimum_settings')) ? Helper::tfopt('tour_archive_price_minimum_settings') : 'adult';

                                                    $min_sale_price = null;
                                                    if ($tf_pricing_rule  && $tf_pricing_rule == 'person') {
                                                        if ($tour_single_price_settings == 'all') {
                                                            if (!empty($avail_prices['adult_price']) && !$disable_adult) {
                                                                $tour_price[] = $avail_prices['adult_price'];
                                                                $min_sale_price = $avail_prices['sale_adult_price'];
                                                            }
                                                            if (!empty($avail_prices['child_price']) && !$disable_child) {
                                                                $tour_price[] = $avail_prices['child_price'];
                                                                if ($avail_prices['sale_child_price'] < $min_sale_price) {
                                                                    $min_sale_price = $avail_prices['sale_child_price'];
                                                                }
                                                            }
                                                        }
                                                        if ($tour_single_price_settings == "adult") {
                                                            if (!empty($avail_prices['adult_price']) && !$disable_adult) {
                                                                $tour_price[] = $avail_prices['adult_price'];
                                                                $min_sale_price = $avail_prices['sale_adult_price'];
                                                            }
                                                        }
                                                        if ($tour_single_price_settings == "child") {
                                                            if (!empty($avail_prices['child_price']) && !$disable_adult) {
                                                                $tour_price[] = $avail_prices['child_price'];
                                                                $min_sale_price = $avail_prices['sale_child_price'];
                                                            }
                                                        }
                                                    }
                                                    if ($tf_pricing_rule  && $tf_pricing_rule == 'group') {
                                                        if (!empty($avail_prices['group_price'])) {
                                                            $tour_price[] = $avail_prices['group_price'];
                                                            $min_sale_price = $avail_prices['sale_group_price'];
                                                        }
                                                    }
                                                    if ($tf_pricing_rule  && $tf_pricing_rule == 'package') {
                                                        if ($tour_single_price_settings == 'all') {
                                                            if (!empty($avail_prices['adult_price']) && !$disable_adult) {
                                                                $tour_price[] = $avail_prices['adult_price'];
                                                                $min_sale_price = $avail_prices['sale_adult_price'];
                                                            }
                                                            if (!empty($avail_prices['child_price']) && !$disable_child) {
                                                                $tour_price[] = $avail_prices['child_price'];
                                                                if ($avail_prices['sale_child_price'] < $min_sale_price) {
                                                                    $min_sale_price = $avail_prices['sale_child_price'];
                                                                }
                                                            }
                                                        }
                                                        if ($tour_single_price_settings == "adult") {
                                                            if (!empty($avail_prices['adult_price']) && !$disable_adult) {
                                                                $tour_price[] = $avail_prices['adult_price'];
                                                                $min_sale_price = $avail_prices['sale_adult_price'];
                                                            }
                                                        }
                                                        if ($tour_single_price_settings == "child") {
                                                            if (!empty($avail_prices['child_price']) && !$disable_adult) {
                                                                $tour_price[] = $avail_prices['child_price'];
                                                                $min_sale_price = $avail_prices['sale_child_price'];
                                                            }
                                                        }
                                                        if (!empty($avail_prices['group_price'])) {
                                                            $tour_price[] = $avail_prices['group_price'];
                                                            if ($avail_prices['sale_group_price'] < $min_sale_price) {
                                                                $min_sale_price = $avail_prices['sale_group_price'];
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <p> <span><?php esc_html_e("From", "tourfic"); ?></span>

                                                        <?php
                                                        //get the lowest price from all available room price
                                                        $tf_tour_min_price      = !empty($tour_price) ? min($tour_price) : 0;

                                                        if (! empty($min_sale_price)) {
                                                            echo wp_kses_post(wp_strip_all_tags(wc_price($tf_tour_min_price))) . " " . "<span><del>" . wp_kses_post(wp_strip_all_tags(wc_price($min_sale_price))) . "</del></span>";
                                                        } else {
                                                            echo wp_kses_post(wp_strip_all_tags(wc_price($tf_tour_min_price)));
                                                        }
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                <?php endif;
                                endif; ?>
                                <!-- Tourfic Booking form -->
                                <div class="tf-booking-form">
                                    <div class="tf-booking-form-inner tf-mt-24 <?php echo $tf_booking_type == 2 && $tf_hide_price !== '1' ? 'tf-mt-24' : '' ?>">
                                        <h3><?php echo ! empty($meta['booking-section-title']) ? esc_html($meta['booking-section-title']) : ''; ?></h3>
                                        <?php
                                        if (($tf_booking_type == 2 && $tf_hide_booking_form !== '1') || $tf_booking_type == 1 || $tf_booking_type == 3) {
                                            echo wp_kses(Tour::tf_single_tour_booking_form($post->ID, 'design-1'), Helper::tf_custom_wp_kses_allow_tags());
                                        }
                                        ?>
                                        <?php if ($tf_booking_type == 2 && $tf_hide_booking_form == 1): ?>
                                            <a href="<?php echo esc_url($tf_booking_url) ?>" target="_blank" class="tf_btn tf_btn_large" style="margin-top: 10px;"><?php echo esc_html($tf_tour_single_book_now_text); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if ($email || $phone || $fax || $website) {
                            ?>
                                <div class="tf-tour-booking-advantages tf-box tf-mt-30">
                                    <div class="tf-head-title">
                                        <h3><?php echo ! empty($meta['contact-info-section-title']) ? esc_html($meta['contact-info-section-title']) : ''; ?></h3>
                                    </div>
                                    <div class="tf-booking-advantage-items">
                                        <ul class="tf-list">
                                            <?php
                                            if (! empty($phone)) { ?>
                                                <li><i class="fa-solid fa-headphones"></i> <a href="tel:<?php echo esc_html($phone) ?>"><?php echo esc_html($phone) ?></a></li>
                                            <?php } ?>
                                            <?php
                                            if (! empty($email)) { ?>
                                                <li><i class="fa-solid fa-envelope"></i> <a href="mailto:<?php echo esc_html($email) ?>"><?php echo esc_html($email) ?></a></li>
                                            <?php } ?>
                                            <?php
                                            if (! empty($website)) { ?>
                                                <li><i class="fa-solid fa-link"></i> <a target="_blank" href="<?php echo esc_html($website) ?>"><?php echo esc_html($website) ?></a></li>
                                            <?php } ?>
                                            <?php
                                            if (! empty($fax)) { ?>
                                                <li><i class="fa-solid fa-fax"></i> <a href="tel:<?php echo esc_html($fax) ?>"><?php echo esc_html($fax) ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            $tf_enquiry_section_status = ! empty($meta['t-enquiry-section']) ? $meta['t-enquiry-section'] : '';
                            $tf_enquiry_section_icon = ! empty($meta['t-enquiry-option-icon']) ? esc_html($meta['t-enquiry-option-icon']) : '';
                            $tf_enquiry_section_title = ! empty($meta['t-enquiry-option-title']) ? esc_html($meta['t-enquiry-option-title']) : '';
                            $tf_enquiry_section_des = ! empty($meta['t-enquiry-option-content']) ? esc_html($meta['t-enquiry-option-content']) : '';
                            $tf_enquiry_section_button = ! empty($meta['t-enquiry-option-btn']) ? esc_html($meta['t-enquiry-option-btn']) : '';

                            if (! empty($tf_enquiry_section_status)) {
                            ?>
                                <!-- Enquiry box -->
                                <div class="tf-tour-booking-advantages tf-box tf-mt-30">
                                    <div class="tf-ask-enquiry">
                                        <?php
                                        if (!empty($tf_enquiry_section_icon)) {
                                        ?>
                                            <i class="<?php echo esc_attr($tf_enquiry_section_icon); ?>" aria-hidden="true"></i>
                                        <?php
                                        }
                                        if (!empty($tf_enquiry_section_title)) {
                                        ?>
                                            <h3><?php echo esc_html($tf_enquiry_section_title); ?></h3>
                                        <?php
                                        }
                                        if (!empty($tf_enquiry_section_des)) {
                                        ?>
                                            <p><?php echo wp_kses_post($tf_enquiry_section_des); ?></p>
                                        <?php
                                        }
                                        if (!empty($tf_enquiry_section_button)) {
                                        ?>
                                            <div class="tf-btn-wrap"><a href="javaScript:void(0);" data-target="#tf-ask-modal" class="tf-modal-btn tf_btn tf_btn_full"><span><?php echo esc_html($tf_enquiry_section_button); ?></span></a></div>
                                        <?php
                                        }
                                        ?>

                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Responsive booking Modal -->
                        <div class="tf-modal" id="tf-tour-booking-modal">
                            <div class="tf-modal-dialog">
                                <div class="tf-modal-content">
                                    <div class="tf-modal-header">
                                        <a data-dismiss="modal" class="tf-modal-close">&#10005;</a>
                                    </div>
                                    <div class="tf-modal-body">
                                        <div class="tf-tour-booking-box tf-box">
                                            <!-- Tourfic Pricing Head -->
                                            <div class="tf-booking-form-data">
                                                <div class="tf-booking-block">
                                                    <div class="tf-booking-price">
                                                        <p><span><?php esc_html_e("From", "tourfic"); ?></span>
                                                            <?php
                                                            //get the lowest price from all available room price
                                                            $tour_price = isset($tour_price) && is_array($tour_price) ? $tour_price : [];
                                                            $tf_tour_min_price      = !empty($tour_price) ? min($tour_price) : 0;
                                                            $lowest_price = wp_strip_all_tags(wc_price($tf_tour_min_price));

                                                            if (! empty($min_sale_price)) {
                                                                echo wp_kses_post($lowest_price) . " " . "<span><del>" . wp_kses_post(wp_strip_all_tags(wc_price($min_sale_price))) . "</del></span>";
                                                            } else {
                                                                echo wp_kses_post($lowest_price);
                                                            }
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tourfic Booking form -->
                                            <div class="tf-booking-form">
                                                <div class="tf-booking-form-inner tf-mt-24">
                                                    <h3><?php echo ! empty($meta['booking-section-title']) ? esc_html($meta['booking-section-title']) : ''; ?></h3>
                                                    <?php echo wp_kses(Tour::tf_single_tour_booking_form($post->ID), Helper::tf_custom_wp_kses_allow_tags()); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Single Tour Body details End -->
                </div>
            </div>
        </div>

        <?php
        if (! $disable_related_tour == '1') {
            $related_tour_type = Helper::tfopt('rt_display');
            $args              = array(
                'post_type'      => 'tf_tours',
                'post_status'    => 'publish',
                'posts_per_page' => 8,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'tax_query'      => array( // WPCS: slow query ok.
                    array(
                        'taxonomy' => 'tour_destination',
                        'field'    => 'slug',
                        'terms'    => $first_destination_slug,
                    ),
                ),
            );

            //show related tour based on selected tours
            $selected_ids = !empty(Helper::tfopt('tf-related-tours')) ? Helper::tfopt('tf-related-tours') : array();

            if ($related_tour_type == 'selected') {
                if (in_array($post_id, $selected_ids)) {
                    $index = array_search($post_id, $selected_ids);

                    $current_post_id = array($selected_ids[$index]);

                    unset($selected_ids[$index]);
                } else {
                    $current_post_id = array($post_id);
                }

                if (count($selected_ids) > 0) {
                    $args['post__in'] = $selected_ids;
                } else {
                    $args['post__in'] = array(-1);
                }
            } else {
                $current_post_id = array($post_id);
            }

            $tours = new WP_Query($args);

            $all_tour_ids = array_filter(wp_list_pluck($tours->posts, 'ID'), function ($id) use ($current_post_id) {
                return $id != $current_post_id[0];
            });

            if ($tours->have_posts()) {
        ?>
                <!-- Tourfic upcomming tours tours -->
                <div class="upcomming-tours">
                    <div class="tf-container">
                        <div class="tf-container-inner">
                            <div class="section-title">
                                <h2 class="tf-title"><?php echo ! empty(Helper::tfopt('rt-title')) ? esc_html(Helper::tfopt('rt-title')) : ''; ?></h2>
                                <?php
                                if (! empty(Helper::tfopt('rt-description'))) { ?>
                                    <p><?php echo wp_kses_post(Helper::tfopt('rt-description')) ?></p>
                                <?php } ?>
                            </div>
                            <div class="tf-slider-items-wrapper tf-slick-slider tf-upcomming-tours-list-outter tf-mt-40 tf-flex tf-flex-gap-24">
                                <?php
                                while ($tours->have_posts()) {
                                    $tours->the_post();
                                    if (is_array($all_tour_ids) && in_array(get_the_ID(), $all_tour_ids)):
                                        $selected_design_post_id = get_the_ID();
                                        $destinations           = get_the_terms($selected_design_post_id, 'tour_destination');

                                        $first_destination_name = $destinations[0]->name;
                                        $related_comments       = get_comments(array('post_id' => $selected_design_post_id));
                                        $meta                   = get_post_meta($selected_design_post_id, 'tf_tours_opt', true);
                                        $pricing_rule           = ! empty($meta['pricing']) ? $meta['pricing'] : '';
                                        $disable_adult          = ! empty($meta['disable_adult_price']) ? $meta['disable_adult_price'] : false;
                                        $disable_child          = ! empty($meta['disable_child_price']) ? $meta['disable_child_price'] : false;
                                        $tour_price             = new Tour_Price($meta);
                                ?>
                                        <div class="tf-slider-item tf-post-box-lists">
                                            <div class="tf-post-single-box">
                                                <div class="tf-image-data">
                                                    <img src="<?php echo ! empty(get_the_post_thumbnail_url($selected_design_post_id, 'full')) ? esc_url(get_the_post_thumbnail_url($selected_design_post_id, 'full')) : esc_url(TF_ASSETS_APP_URL . 'images/feature-default.jpg'); ?>"
                                                        alt="">
                                                    <div class="tf-meta-data-price">
                                                        <?php esc_html_e("From", "tourfic"); ?>
                                                        <span>
                                                            <?php if ($pricing_rule == 'group') {
                                                                echo !empty($tour_price->wc_sale_group) ? wp_kses_post($tour_price->wc_sale_group) : wp_kses_post($tour_price->wc_group);
                                                            } else if ($pricing_rule == 'person') {
                                                                if (! $disable_adult && ! empty($tour_price->adult)) {
                                                                    echo !empty($tour_price->wc_sale_adult) ? wp_kses_post($tour_price->wc_sale_adult) : wp_kses_post($tour_price->wc_adult);
                                                                } else if (! $disable_child && ! empty($tour_price->child)) {
                                                                    echo !empty($tour_price->wc_sale_child) ? wp_kses_post($tour_price->wc_sale_child) : wp_kses_post($tour_price->wc_child);
                                                                }
                                                            } else if ($pricing_rule == 'package') {
                                                                if (! $disable_adult && ! empty($tour_price->adult)) {
                                                                    echo !empty($tour_price->wc_sale_adult) ? wp_kses_post($tour_price->wc_sale_adult) : wp_kses_post($tour_price->wc_adult);
                                                                } else if (! $disable_child && ! empty($tour_price->child)) {
                                                                    echo !empty($tour_price->wc_sale_child) ? wp_kses_post($tour_price->wc_sale_child) : wp_kses_post($tour_price->wc_child);
                                                                }
                                                            }
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="tf-meta-info tf-mt-30">
                                                    <div class="tf-meta-location">
                                                        <i class="fa-solid fa-location-dot"></i> <?php echo esc_html($first_destination_name); ?>
                                                    </div>
                                                    <div class="tf-meta-title">
                                                        <h2><a href="<?php the_permalink($selected_design_post_id) ?>"><?php echo wp_kses_post(Helper::tourfic_character_limit_callback(html_entity_decode(get_the_title($selected_design_post_id)), 35)); ?></a></h2>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
            wp_reset_postdata();
            ?>
        <?php } ?>
    </div>
</div>