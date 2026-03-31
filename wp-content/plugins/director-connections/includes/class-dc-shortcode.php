<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_Shortcode {

    public function __construct() {
        add_shortcode( 'director_connections', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        // Defer Alpine so it initialises after the DOM is ready.
        add_filter( 'script_loader_tag', array( $this, 'defer_alpine' ), 10, 2 );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'dc-frontend', DC_PLUGIN_URL . 'assets/css/frontend.css', array(), DC_VERSION );
        wp_enqueue_script( 'dc-alpinejs', 'https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js', array(), '3', true );
    }

    public function defer_alpine( $tag, $handle ) {
        if ( 'dc-alpinejs' === $handle ) {
            return str_replace( ' src=', ' defer src=', $tag );
        }
        return $tag;
    }

    public function render( $atts ) {
        $data = DC_Query::get_directors();
        $all_directors = $data['directors'];
        $coen_ids      = $data['coen_ids'];

        // Sanitize incoming GET values.
        $raw_ids = isset( $_GET['directors'] ) && is_array( $_GET['directors'] )
            ? $_GET['directors']
            : array();

        $ids = array_values( array_filter( array_map( function( $v ) {
            $v = sanitize_text_field( wp_unslash( $v ) );
            return ( 'coen-brothers' === $v || ctype_digit( $v ) ) ? $v : '';
        }, $raw_ids ) ) );

        $actors            = array();
        $films_by_actor    = array();
        $selected_directors = array();

        if ( ! empty( $ids ) ) {
            // Build selected director objects for column headers.
            $directors_by_id = array();
            foreach ( $all_directors as $d ) {
                $directors_by_id[ (string) $d->id ] = $d;
            }
            foreach ( $ids as $id ) {
                if ( isset( $directors_by_id[ $id ] ) ) {
                    $selected_directors[] = $directors_by_id[ $id ];
                }
            }

            $result         = DC_Query::run( $ids, $coen_ids );
            $actors         = $result['actors'];
            $films_by_actor = $result['filmsByActor'];
        }

        ob_start();
        include DC_PLUGIN_DIR . 'frontend/director-connections.php';
        return ob_get_clean();
    }
}
