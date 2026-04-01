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
        add_action( 'admin_init', array( $this, 'process_forms' ) );
    }

    public function process_forms() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // --- People ---
        if ( isset( $_POST['dc_save_person'] ) ) {
            check_admin_referer( 'dc_save_person' );

            $person_id   = absint( $_POST['person_id'] ?? 0 );
            $name        = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
            $nationality = sanitize_text_field( wp_unslash( $_POST['nationality'] ?? '' ) );
            $data        = array( 'name' => $name, 'nationality' => $nationality );

            if ( empty( $name ) ) {
                $this->set_error( 'Name is required.' );
                wp_redirect( add_query_arg( array(
                    'page'   => 'dc-people',
                    'action' => $person_id ? 'edit' : 'add',
                    'id'     => $person_id ?: false,
                ), admin_url( 'admin.php' ) ) );
                exit;
            }

            $person_id ? DC_People::update( $person_id, $data ) : DC_People::insert( $data );
            wp_redirect( add_query_arg( array( 'page' => 'dc-people', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // --- Movies ---
        if ( isset( $_POST['dc_save_movie'] ) ) {
            check_admin_referer( 'dc_save_movie' );

            $movie_id     = absint( $_POST['movie_id'] ?? 0 );
            $title        = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
            $release_year = absint( $_POST['release_year'] ?? 0 );
            $data         = array( 'title' => $title, 'release_year' => $release_year ?: null );

            if ( empty( $title ) ) {
                $this->set_error( 'Title is required.' );
                wp_redirect( add_query_arg( array(
                    'page'   => 'dc-movies',
                    'action' => $movie_id ? 'edit' : 'add',
                    'id'     => $movie_id ?: false,
                ), admin_url( 'admin.php' ) ) );
                exit;
            }

            $movie_id ? DC_Movies::update( $movie_id, $data ) : DC_Movies::insert( $data );
            wp_redirect( add_query_arg( array( 'page' => 'dc-movies', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // --- Credits ---
        if ( isset( $_POST['dc_save_credit'] ) ) {
            check_admin_referer( 'dc_save_credit' );

            $credit_id      = absint( $_POST['credit_id'] ?? 0 );
            $person_id      = absint( $_POST['person_id'] ?? 0 );
            $movie_id       = absint( $_POST['movie_id'] ?? 0 );
            $type_id        = absint( $_POST['type_id'] ?? 0 );
            $character_name = sanitize_text_field( wp_unslash( $_POST['character_name'] ?? '' ) );
            $data           = compact( 'person_id', 'movie_id', 'type_id', 'character_name' );

            if ( ! $person_id || ! $movie_id || ! $type_id ) {
                $this->set_error( 'Person, movie, and role are all required.' );
                wp_redirect( add_query_arg( array(
                    'page'   => 'dc-credits',
                    'action' => $credit_id ? 'edit' : 'add',
                    'id'     => $credit_id ?: false,
                ), admin_url( 'admin.php' ) ) );
                exit;
            }

            $credit_id ? DC_Credits::update( $credit_id, $data ) : DC_Credits::insert( $data );
            wp_redirect( add_query_arg( array( 'page' => 'dc-credits', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    private function set_error( $message ) {
        set_transient( 'dc_form_error_' . get_current_user_id(), $message, 60 );
    }

    public static function get_error() {
        $uid = get_current_user_id();
        $msg = get_transient( 'dc_form_error_' . $uid );
        if ( $msg ) {
            delete_transient( 'dc_form_error_' . $uid );
        }
        return $msg;
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
