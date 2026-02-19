<?php
/**
 * Plugin Name: Tourfic Package
 * Description: Adds Package Booking system for Tourfic.
 * Version: 1.0.0
 * Author: Themefic
 * Author URI: https://themefic.com/
 * Text Domain: tourfic-package
 */

defined( 'ABSPATH' ) || exit;

define( 'TF_PACKAGE_VERSION', '1.0.0' );
define( 'TF_PACKAGE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'TF_PACKAGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TF_PACKAGE_TEMPLATE_PATH', TF_PACKAGE_PLUGIN_PATH . 'inc/templates/' );

// Include required files
require_once TF_PACKAGE_PLUGIN_PATH . 'inc/class-tf-package.php';
require_once TF_PACKAGE_PLUGIN_PATH . 'inc/tf-package-post-type.php';