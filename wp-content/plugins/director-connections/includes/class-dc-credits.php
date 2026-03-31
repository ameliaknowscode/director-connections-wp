<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_Credits {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'dc_credits';
    }

    /**
     * Returns credits joined with person, movie, and type names.
     */
    public static function all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'orderby' => 'person_name',
            'order'   => 'ASC',
            'search'  => '',
            'limit'   => 20,
            'offset'  => 0,
        );
        $args = wp_parse_args( $args, $defaults );

        $allowed_orderby = array( 'person_name', 'movie_title', 'type_name', 'release_year' );
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'person_name';
        $order   = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

        $credits = $wpdb->prefix . 'dc_credits';
        $people  = $wpdb->prefix . 'dc_people';
        $movies  = $wpdb->prefix . 'dc_movies';
        $types   = $wpdb->prefix . 'dc_types';

        $where  = '1=1';
        $values = array();

        if ( ! empty( $args['search'] ) ) {
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where   .= ' AND (pe.name LIKE %s OR mo.title LIKE %s)';
            $values[] = $like;
            $values[] = $like;
        }

        $values[] = (int) $args['limit'];
        $values[] = (int) $args['offset'];

        $sql = $wpdb->prepare(
            "SELECT c.id, c.character_name,
                    pe.id AS person_id, pe.name AS person_name,
                    mo.id AS movie_id, mo.title AS movie_title, mo.release_year,
                    t.id AS type_id, t.name AS type_name
             FROM $credits c
             JOIN $people pe ON pe.id = c.person_id
             JOIN $movies mo ON mo.id = c.movie_id
             JOIN $types  t  ON t.id  = c.type_id
             WHERE $where
             ORDER BY $orderby $order
             LIMIT %d OFFSET %d",
            $values
        );

        return $wpdb->get_results( $sql );
    }

    public static function count( $search = '' ) {
        global $wpdb;

        $credits = $wpdb->prefix . 'dc_credits';
        $people  = $wpdb->prefix . 'dc_people';
        $movies  = $wpdb->prefix . 'dc_movies';

        if ( ! empty( $search ) ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $credits c
                     JOIN $people pe ON pe.id = c.person_id
                     JOIN $movies mo ON mo.id = c.movie_id
                     WHERE pe.name LIKE %s OR mo.title LIKE %s",
                    $like, $like
                )
            );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $credits" );
    }

    public static function find( $id ) {
        global $wpdb;
        $table = self::table();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    }

    public static function insert( $data ) {
        global $wpdb;
        return $wpdb->insert(
            self::table(),
            array(
                'person_id'      => (int) $data['person_id'],
                'movie_id'       => (int) $data['movie_id'],
                'type_id'        => (int) $data['type_id'],
                'character_name' => sanitize_text_field( $data['character_name'] ?? '' ),
            )
        );
    }

    public static function update( $id, $data ) {
        global $wpdb;
        return $wpdb->update(
            self::table(),
            array(
                'person_id'      => (int) $data['person_id'],
                'movie_id'       => (int) $data['movie_id'],
                'type_id'        => (int) $data['type_id'],
                'character_name' => sanitize_text_field( $data['character_name'] ?? '' ),
            ),
            array( 'id' => (int) $id )
        );
    }

    public static function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( self::table(), array( 'id' => (int) $id ) );
    }

    public static function get_types() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}dc_types ORDER BY name ASC" );
    }
}
