<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_Movies {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'dc_movies';
    }

    public static function all( $args = array() ) {
        global $wpdb;
        $table = self::table();

        $defaults = array(
            'orderby' => 'title',
            'order'   => 'ASC',
            'search'  => '',
            'limit'   => 20,
            'offset'  => 0,
        );
        $args = wp_parse_args( $args, $defaults );

        $orderby = in_array( $args['orderby'], array( 'id', 'title', 'release_year' ), true ) ? $args['orderby'] : 'title';
        $order   = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

        $where  = '1=1';
        $values = array();

        if ( ! empty( $args['search'] ) ) {
            $where   .= ' AND title LIKE %s';
            $values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }

        $values[] = (int) $args['limit'];
        $values[] = (int) $args['offset'];

        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE $where ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $values
        );

        return $wpdb->get_results( $sql );
    }

    public static function count( $search = '' ) {
        global $wpdb;
        $table = self::table();

        if ( ! empty( $search ) ) {
            return (int) $wpdb->get_var(
                $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE title LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' )
            );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    }

    public static function find( $id ) {
        global $wpdb;
        $table = self::table();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    }

    public static function insert( $data ) {
        global $wpdb;
        $data['slug'] = self::unique_slug( sanitize_title( $data['title'] ) );

        return $wpdb->insert(
            self::table(),
            array(
                'title'        => sanitize_text_field( $data['title'] ),
                'slug'         => $data['slug'],
                'release_year' => ! empty( $data['release_year'] ) ? (int) $data['release_year'] : null,
            )
        );
    }

    public static function update( $id, $data ) {
        global $wpdb;
        $movie = self::find( $id );

        if ( ! $movie ) {
            return false;
        }

        $new_title = sanitize_text_field( $data['title'] );
        $slug = ( $new_title !== $movie->title )
            ? self::unique_slug( sanitize_title( $new_title ), $id )
            : $movie->slug;

        return $wpdb->update(
            self::table(),
            array(
                'title'        => $new_title,
                'slug'         => $slug,
                'release_year' => ! empty( $data['release_year'] ) ? (int) $data['release_year'] : null,
            ),
            array( 'id' => (int) $id )
        );
    }

    public static function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( self::table(), array( 'id' => (int) $id ) );
    }

    private static function unique_slug( $base_slug, $exclude_id = 0 ) {
        global $wpdb;
        $table = self::table();
        $slug  = $base_slug;
        $i     = 1;

        while ( true ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table WHERE slug = %s AND id != %d",
                    $slug,
                    (int) $exclude_id
                )
            );
            if ( ! $existing ) {
                break;
            }
            $slug = $base_slug . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
