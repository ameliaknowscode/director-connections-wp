<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DC_List_Table_Credits extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'credit',
            'plural'   => 'credits',
            'ajax'     => false,
        ) );
    }

    public function get_columns() {
        return array(
            'cb'             => '<input type="checkbox" />',
            'person_name'    => 'Person',
            'movie_title'    => 'Movie',
            'release_year'   => 'Year',
            'type_name'      => 'Role',
            'character_name' => 'Character',
        );
    }

    public function get_sortable_columns() {
        return array(
            'person_name'  => array( 'person_name', true ),
            'movie_title'  => array( 'movie_title', false ),
            'release_year' => array( 'release_year', false ),
            'type_name'    => array( 'type_name', false ),
        );
    }

    protected function get_bulk_actions() {
        return array(
            'bulk_delete' => 'Delete',
        );
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="credits[]" value="%d" />', $item->id );
    }

    protected function column_person_name( $item ) {
        $edit_url   = add_query_arg( array( 'action' => 'edit', 'id' => $item->id ) );
        $delete_url = wp_nonce_url(
            add_query_arg( array( 'action' => 'delete', 'id' => $item->id ) ),
            'dc_delete_credit_' . $item->id
        );

        $actions = array(
            'edit'   => sprintf( '<a href="%s">Edit</a>', esc_url( $edit_url ) ),
            'delete' => sprintf( '<a href="%s" onclick="return confirm(\'Delete this credit?\')">Delete</a>', esc_url( $delete_url ) ),
        );

        return sprintf( '<strong><a href="%s">%s</a></strong> %s', esc_url( $edit_url ), esc_html( $item->person_name ), $this->row_actions( $actions ) );
    }

    protected function column_default( $item, $column_name ) {
        return esc_html( $item->$column_name ?? '—' );
    }

    public function prepare_items() {
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $search       = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
        $orderby      = isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'person_name';
        $order        = isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC';

        $total = DC_Credits::count( $search );

        $this->set_pagination_args( array(
            'total_items' => $total,
            'per_page'    => $per_page,
        ) );

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

        $this->items = DC_Credits::all( array(
            'orderby' => $orderby,
            'order'   => $order,
            'search'  => $search,
            'limit'   => $per_page,
            'offset'  => ( $current_page - 1 ) * $per_page,
        ) );
    }
}
