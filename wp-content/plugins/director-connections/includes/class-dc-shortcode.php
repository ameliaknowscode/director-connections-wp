<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_Shortcode {

    public function __construct() {
        add_shortcode( 'director_connections', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'dc-frontend', DC_PLUGIN_URL . 'assets/css/frontend.css', array(), DC_VERSION );
        wp_enqueue_script( 'dc-alpinejs', DC_PLUGIN_URL . 'assets/js/alpine.min.js', array(), '3.14.1', true );

        // Register dcForm via wp_add_inline_script so it is never run through
        // wptexturize or any other content filter.
        // dcForm reads initial state from the form's data-initial attribute,
        // avoiding any need to pass PHP state through wp_add_inline_script at render time.
        wp_add_inline_script( 'dc-alpinejs', '
function dcForm() {
    var form    = document.querySelector(".dc-wrap form[data-initial]");
    var initial = form ? JSON.parse(form.dataset.initial) : ["", ""];
    var max     = form ? parseInt(form.dataset.max, 10) : 10;
    return {
        directors: initial,
        add:       function()  { this.directors.push(""); },
        remove:    function(i) { this.directors.splice(i, 1); },
        canRemove: function()  { return this.directors.length > 1; },
        canAdd:    function()  { return this.directors.length < max; }
    };
}
', 'before' );
    }

    public function render( $atts ) {
        $data          = DC_Query::get_directors();
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

        $actors             = array();
        $films_by_actor     = array();
        $selected_directors = array();

        if ( ! empty( $ids ) ) {
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

        // Build initial slot values — passed to the template as $initial for data attributes.
        $initial = array_values( $ids );
        while ( count( $initial ) < 2 ) {
            $initial[] = '';
        }

        ob_start();
        include DC_PLUGIN_DIR . 'frontend/director-connections.php';
        return ob_get_clean();
    }
}
