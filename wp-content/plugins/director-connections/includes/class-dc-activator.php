<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_Activator {

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "
            CREATE TABLE {$wpdb->prefix}dc_people (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                slug varchar(255) NOT NULL,
                nationality varchar(100) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}dc_movies (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL,
                slug varchar(255) NOT NULL,
                release_year smallint(4) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}dc_types (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(100) NOT NULL,
                is_crew tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) $charset_collate;

            CREATE TABLE {$wpdb->prefix}dc_credits (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                movie_id bigint(20) unsigned NOT NULL,
                person_id bigint(20) unsigned NOT NULL,
                type_id bigint(20) unsigned NOT NULL,
                character_name varchar(255) DEFAULT NULL,
                PRIMARY KEY (id),
                KEY movie_id (movie_id),
                KEY person_id (person_id),
                KEY type_id (type_id)
            ) $charset_collate;
        ";

        dbDelta( $sql );

        self::seed_types();
    }

    private static function seed_types() {
        global $wpdb;
        $table = $wpdb->prefix . 'dc_types';

        if ( $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) > 0 ) {
            return;
        }

        $types = array(
            array( 'name' => 'Director', 'is_crew' => 1 ),
            array( 'name' => 'Actor',    'is_crew' => 0 ),
            array( 'name' => 'Writer',   'is_crew' => 1 ),
            array( 'name' => 'Producer', 'is_crew' => 1 ),
        );

        foreach ( $types as $type ) {
            $wpdb->insert( $table, $type );
        }
    }
}
