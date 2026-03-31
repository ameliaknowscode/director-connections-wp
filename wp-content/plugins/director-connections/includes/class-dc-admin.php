<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_Admin {

    public function __construct() {
        require_once DC_PLUGIN_DIR . 'includes/class-dc-list-table-people.php';
        require_once DC_PLUGIN_DIR . 'includes/class-dc-list-table-movies.php';
        require_once DC_PLUGIN_DIR . 'includes/class-dc-list-table-credits.php';
        add_action( 'admin_menu', array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function register_menus() {
        add_menu_page(
            'Director Connections',
            'Dir. Connections',
            'manage_options',
            'director-connections',
            array( $this, 'page_dashboard' ),
            'dashicons-networking',
            30
        );

        add_submenu_page(
            'director-connections',
            'People',
            'People',
            'manage_options',
            'dc-people',
            array( $this, 'page_people' )
        );

        add_submenu_page(
            'director-connections',
            'Movies',
            'Movies',
            'manage_options',
            'dc-movies',
            array( $this, 'page_movies' )
        );

        add_submenu_page(
            'director-connections',
            'Credits',
            'Credits',
            'manage_options',
            'dc-credits',
            array( $this, 'page_credits' )
        );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'director-connections' ) === false && strpos( $hook, 'dc-people' ) === false && strpos( $hook, 'dc-movies' ) === false && strpos( $hook, 'dc-credits' ) === false ) {
            return;
        }
        wp_enqueue_style( 'dc-admin', DC_PLUGIN_URL . 'assets/css/admin.css', array(), DC_VERSION );
    }

    public function page_dashboard() {
        include DC_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_people() {
        include DC_PLUGIN_DIR . 'admin/views/people.php';
    }

    public function page_movies() {
        include DC_PLUGIN_DIR . 'admin/views/movies.php';
    }

    public function page_credits() {
        include DC_PLUGIN_DIR . 'admin/views/credits.php';
    }
}
