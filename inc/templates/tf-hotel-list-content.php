<?php
// don't load directly
defined('ABSPATH') || exit;

use \Tourfic\Classes\Hotel\Pricing as Hotel_Price;

$package_id = get_the_ID();
$tf_package_opt = get_post_meta($package_id, 'tf_package_opt', true);
$selected_hotels = !empty($tf_package_opt['tf_package_hotels']) ? $tf_package_opt['tf_package_hotels'] : [];

if (empty($selected_hotels)) {
    echo 'No hotels found';
    return;
}

$args = array(
    'post_type' => 'tf_hotel',
    'post__in'  => $selected_hotels,
    'posts_per_page' => -1,
    'orderby'   => 'post__in',
);
$query = new \WP_Query($args);
if ($query->have_posts()) :
    while ($query->have_posts()) : $query->the_post();
        $post_id = get_the_ID();

        // Review Query 
        $tf_comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
        $tf_average_rating = 0;

        if ($tf_comments) {
            $tf_comments_meta = get_comment_meta($tf_comments[0]->comment_ID, 'tf_comment_meta', true);
            if (!empty($tf_comments_meta) && is_array($tf_comments_meta)) {
                $tf_total_rating = array_sum($tf_comments_meta);
                $tf_category_count = count($tf_comments_meta);
                $tf_average_rating = $tf_category_count > 0 ? $tf_total_rating / $tf_category_count : 0;
            }
        }

        $tf_total_price = 0;
        // get average rating
        $comments_query = new WP_Comment_Query($args);
        $comments = $comments_query->comments;
        $option_meta = get_post_meta(get_the_ID(), 'tf_hotels_opt', true);
        $disable_review_sec = !empty($option_meta['h-review']) ? $option_meta['h-review'] : '';
        $min_price_arr = Hotel_Price::instance($post_id)->get_min_price();
        $tf_total_price = $min_price_arr['min_sale_price'] ? $min_price_arr['min_sale_price'] : $min_price_arr['min_regular_price'];

        // featured
        $tf_featured = isset($option_meta['featured']) ? $option_meta['featured'] : false;
        $tf_featured_text = !empty($option_meta['featured_text']) ? $option_meta['featured_text'] : 'Featured';

        // location
        if (is_array($option_meta) && isset($option_meta['map'])) {
            $tf_location = maybe_unserialize($option_meta['map'])['address'] ?? '';
        } else {
            $tf_location = '';
        }

?>
        <!-- item -->
        <div class="tf-package-list-item">
            <!-- item thumbnail -->
            <div class="tf-item-thumbnail">
                <?php
                $tft_hotel_image = !empty(get_the_post_thumbnail_url(get_the_ID())) ? esc_url(get_the_post_thumbnail_url(get_the_ID())) : esc_url(site_url() . '/wp-content/plugins/elementor/assets/images/placeholder.png');
                ?>
                <img src="<?php echo esc_url($tft_hotel_image); ?>" alt="post thumbnail">
                <div class="tf-item-featured">
                    <?php echo $tf_featured ? '<span class="tf-item-featured-text">' . esc_html($tf_featured_text) . '</span>' : ''; ?>
                </div>
            </div>
            <!-- item content -->
            <div class="tf-item-content">
                <div class="tf-item-top-info">
                    <?php echo tf_review_star_rating((float) $tf_average_rating);  ?>
                    <?php if (!empty($tf_location)): ?>
                        <span class="tf-item-location">
                            <i class="ri-map-pin-line tf-item-location-icon"></i>
                            <span class="tf-item-location-text"><?php echo esc_html($tf_location); ?></span>
                        </span>
                    <?php endif; ?>
                    <h2 class="tf-item-title">
                        <span href="<?php echo esc_url(get_the_permalink()) ?>" class="tf-item-title-link">
                            <?php the_title(); ?>
                        </span>
                    </h2>
                </div>
                <div class="tf-item-bottom-info">
                    <div class="tf-item-price">
                        <span class="tf-item-price-title tf-item-price-title-text">
                            <?php
                            if (function_exists('wc_price')) {
                                $currency_code   = get_woocommerce_currency();
                                echo sprintf(esc_html__('From %s', 'tourfic-package'), $currency_code);
                            } else {
                                echo esc_html__('From USD', 'tourfic-package');
                            }
                            ?>
                        </span>
                        <span class="tf-item-price-value tf-item-price-value-text">
                            <?php if (function_exists('wc_price')) {
                                echo wc_price($tf_total_price);
                            } else {
                                echo '$' . number_format($tf_total_price, 2);
                            }
                            ?>
                        </span>
                    </div>
                    <div class="tf-item-btn">
                        <button class="tf-btn tf-open-popup" data-post-id="<?php echo esc_attr(get_the_ID()); ?>" data-post-type="tf_hotel">
                            <?php echo esc_html__('Explore', 'tourfic-package'); ?>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif;

wp_reset_postdata();
