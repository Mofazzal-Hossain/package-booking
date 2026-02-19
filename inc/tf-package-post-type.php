<?php
defined('ABSPATH') || exit;

class TF_Package_Post_Type
{
    public function __construct()
    {

        // Register post type
        add_action('init', array($this, 'tf_register_package_post_type'), 15);

        // Modify post type arguments
        add_filter('tf_package_args', array($this, 'tf_package_args'));

        // Modify post type labels
        add_filter('tf_package_labels', array($this, 'tf_package_labels'));
    }

    public function tf_register_package_post_type()
    {

        if (! class_exists('\Tourfic\Core\Post_Type')) {
            return;
        }

        $post = new class extends \Tourfic\Core\Post_Type {};

        $post->set_post_args(array(
            'name'           => esc_html__('Packages', 'tourfic-package'),
            'singular_name'  => esc_html__('Package', 'tourfic-package'),
            'slug'           => 'tf_package',
            'menu_icon'      => 'dashicons-slides',
            'rewrite_slug'   => 'package',
            'capability'     => 'post',
            'menu_position'  => 25,
            'supports'       => array('title'),
        ));

        $post->tf_post_type_register();
    }

    public function tf_package_args($args)
    {

        $args['has_archive'] = true;
        $args['rewrite']     = array(
            'slug'       => 'package',
            'with_front' => false
        );

        return $args;
    }

    public function tf_package_labels($labels)
    {

        $labels['menu_name']     = esc_html__('Packages', 'tourfic-package');
        $labels['name']          = esc_html__('Packages', 'tourfic-package');
        $labels['singular_name'] = esc_html__('Package', 'tourfic-package');
        $labels['add_new_item']  = esc_html__('Add New Package', 'tourfic-package');

        return $labels;
    }
}

new TF_Package_Post_Type();
