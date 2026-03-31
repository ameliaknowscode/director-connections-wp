<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_People {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'dc_people';
    }

    public static function all( $args = array() ) {
        global $wpdb;
        $table = self::table();

        $defaults = array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'search'  => '',
            'limit'   => 20,
            'offset'  => 0,
        );
        $args = wp_parse_args( $args, $defaults );

        $orderby = in_array( $args['orderby'], array( 'id', 'name', 'nationality' ), true ) ? $args['orderby'] : 'name';
        $order   = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

        $where = '1=1';
        $values = array();

        if ( ! empty( $args['search'] ) ) {
            $where   .= ' AND name LIKE %s';
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
                $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE name LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' )
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
        $data['slug'] = self::unique_slug( sanitize_title( $data['name'] ) );

        return $wpdb->insert(
            self::table(),
            array(
                'name'        => sanitize_text_field( $data['name'] ),
                'slug'        => $data['slug'],
                'nationality' => sanitize_text_field( $data['nationality'] ),
            )
        );
    }

    public static function update( $id, $data ) {
        global $wpdb;
        $person = self::find( $id );

        if ( ! $person ) {
            return false;
        }

        $new_name = sanitize_text_field( $data['name'] );
        $slug = ( $new_name !== $person->name )
            ? self::unique_slug( sanitize_title( $new_name ), $id )
            : $person->slug;

        return $wpdb->update(
            self::table(),
            array(
                'name'        => $new_name,
                'slug'        => $slug,
                'nationality' => sanitize_text_field( $data['nationality'] ),
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
