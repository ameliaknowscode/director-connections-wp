<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DC_List_Table_People extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'person',
            'plural'   => 'people',
            'ajax'     => false,
        ) );
    }

    public function get_columns() {
        return array(
            'cb'          => '<input type="checkbox" />',
            'name'        => 'Name',
            'nationality' => 'Nationality',
            'slug'        => 'Slug',
        );
    }

    public function get_sortable_columns() {
        return array(
            'name'        => array( 'name', true ),
            'nationality' => array( 'nationality', false ),
        );
    }

    protected function get_bulk_actions() {
        return array(
            'bulk_delete' => 'Delete',
        );
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="people[]" value="%d" />', $item->id );
    }

    protected function column_name( $item ) {
        $edit_url   = add_query_arg( array( 'action' => 'edit', 'id' => $item->id ) );
        $delete_url = wp_nonce_url(
            add_query_arg( array( 'action' => 'delete', 'id' => $item->id ) ),
            'dc_delete_person_' . $item->id
        );

        $actions = array(
            'edit'   => sprintf( '<a href="%s">Edit</a>', esc_url( $edit_url ) ),
            'delete' => sprintf( '<a href="%s" onclick="return confirm(\'Delete %s?\')">Delete</a>', esc_url( $delete_url ), esc_js( $item->name ) ),
        );

        return sprintf( '<strong><a href="%s">%s</a></strong> %s', esc_url( $edit_url ), esc_html( $item->name ), $this->row_actions( $actions ) );
    }

    protected function column_default( $item, $column_name ) {
        return esc_html( $item->$column_name ?? '—' );
    }

    public function prepare_items() {
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $search       = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
        $orderby      = isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'name';
        $order        = isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC';

        $total = DC_People::count( $search );

        $this->set_pagination_args( array(
            'total_items' => $total,
            'per_page'    => $per_page,
        ) );

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

        $this->items = DC_People::all( array(
            'orderby' => $orderby,
            'order'   => $order,
            'search'  => $search,
            'limit'   => $per_page,
            'offset'  => ( $current_page - 1 ) * $per_page,
        ) );
    }
}
