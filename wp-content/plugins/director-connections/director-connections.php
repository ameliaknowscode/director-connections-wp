<?php
/**
 * Plugin Name: Director Connections
 * Plugin URI:
 * Description: Find actors who appeared in films by multiple selected directors.
 * Version: 0.1.0
 * Author: Amy
 * Text Domain: director-connections
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'DC_VERSION', '0.1.0' );
define( 'DC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once DC_PLUGIN_DIR . 'includes/class-dc-activator.php';
require_once DC_PLUGIN_DIR . 'includes/class-dc-people.php';
require_once DC_PLUGIN_DIR . 'includes/class-dc-movies.php';
require_once DC_PLUGIN_DIR . 'includes/class-dc-credits.php';
require_once DC_PLUGIN_DIR . 'includes/class-dc-query.php';
require_once DC_PLUGIN_DIR . 'includes/class-dc-shortcode.php';
require_once DC_PLUGIN_DIR . 'includes/class-dc-admin.php';

register_activation_hook( __FILE__, array( 'DC_Activator', 'activate' ) );

if ( is_admin() ) {
    new DC_Admin();
} else {
    new DC_Shortcode();
}
