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
        if (is_singular('tf_package')) {

            $plugin_template = TF_PACKAGE_PLUGIN_PATH . 'inc/tf-single-package.php';

            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
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
        $hotel_date_format_for_users   = ! empty(Helper::tfopt("tf-date-format-for-users")) ? Helper::tfopt("tf-date-format-for-users") : "Y/m/d";
        $check_in_out = ! empty( $_GET['check-in-out-date'] ) ? sanitize_text_field( $_GET['check-in-out-date'] ) : '';
        
        wp_enqueue_style('tf-package-css', TF_PACKAGE_PLUGIN_URL . 'assets/css/tf-package.css', array(), time(), 'all');
        wp_enqueue_script('tf-package-js', TF_PACKAGE_PLUGIN_URL . 'assets/js/tf-package.js', array('jquery'), time(), true);
        wp_localize_script('tf-package-js', 'tf_package_data', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('tf_load_single_template'),
            'user_date_format' => $hotel_date_format_for_users,
            'checkInOut' => explode('-', $check_in_out),
        ));
    }

    // load single template
    public function tf_load_single_template()
    {
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

        if ($post_type === 'tf_hotel') {
            include TF_PACKAGE_PLUGIN_PATH . '/inc/templates/single/hotel-single.php';
        }

        if ($post_type === 'tf_tours') {
            include TF_PACKAGE_PLUGIN_PATH . '/inc/templates/single/tour-single.php';
        }

        wp_reset_postdata();
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
}

new TF_Package();
