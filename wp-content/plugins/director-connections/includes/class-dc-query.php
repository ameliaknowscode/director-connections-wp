<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DC_Query {

    private static $director_type_id = null;
    private static $actor_type_id    = null;

    private static function director_type_id() {
        if ( null === self::$director_type_id ) {
            global $wpdb;
            self::$director_type_id = (int) $wpdb->get_var(
                $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}dc_types WHERE name = %s", 'Director' )
            );
        }
        return self::$director_type_id;
    }

    private static function actor_type_id() {
        if ( null === self::$actor_type_id ) {
            global $wpdb;
            self::$actor_type_id = (int) $wpdb->get_var(
                $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}dc_types WHERE name = %s", 'Actor' )
            );
        }
        return self::$actor_type_id;
    }

    /**
     * Returns all people who have at least one Director credit, with the
     * Coen Brothers collapsed into a single virtual entry.
     *
     * @return array { directors: object[], coen_ids: int[] }
     */
    public static function get_directors() {
        global $wpdb;

        $director_type_id = self::director_type_id();

        $all = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT pe.id, pe.name, pe.slug, pe.nationality
             FROM {$wpdb->prefix}dc_people pe
             JOIN {$wpdb->prefix}dc_credits c ON c.person_id = pe.id
             WHERE c.type_id = %d
             ORDER BY pe.name ASC",
            $director_type_id
        ) );

        $coen_names = array( 'Joel Coen', 'Ethan Coen' );
        $coens      = array_filter( $all, fn( $d ) => in_array( $d->name, $coen_names, true ) );
        $coen_ids   = array_column( array_values( $coens ), 'id' );

        $directors = array_values( array_filter( $all, fn( $d ) => ! in_array( $d->name, $coen_names, true ) ) );

        if ( ! empty( $coens ) ) {
            $directors[] = (object) array(
                'id'          => 'coen-brothers',
                'name'        => 'The Coen Brothers',
                'slug'        => 'coen-brothers',
                'nationality' => null,
            );
            usort( $directors, fn( $a, $b ) => strcmp( $a->name, $b->name ) );
        }

        return array( 'directors' => $directors, 'coen_ids' => $coen_ids );
    }

    /**
     * Run the intersection query for the given director IDs.
     *
     * @param  string[] $ids       Director IDs (ints as strings, or 'coen-brothers').
     * @param  int[]    $coen_ids  Real person IDs for the Coen Brothers.
     * @return array { actors: object[], filmsByActor: array }
     */
    public static function run( $ids, $coen_ids = array() ) {
        global $wpdb;

        $ids = array_values( array_filter( $ids ) );
        if ( empty( $ids ) ) {
            return array( 'actors' => array(), 'filmsByActor' => array() );
        }

        $director_type_id = self::director_type_id();
        $actor_type_id    = self::actor_type_id();

        // Step 1: For each director slot, get the movies they directed.
        $movie_ids_by_director = array();
        $actor_sets            = array();

        foreach ( $ids as $director_id ) {
            if ( 'coen-brothers' === $director_id ) {
                if ( empty( $coen_ids ) ) {
                    $movie_ids_by_director[ $director_id ] = array();
                    $actor_sets[ $director_id ]            = array();
                    continue;
                }
                $ph   = implode( ',', array_fill( 0, count( $coen_ids ), '%d' ) );
                $args = array_merge( array( $director_type_id ), array_map( 'intval', $coen_ids ) );
                $movie_ids = $wpdb->get_col( $wpdb->prepare(
                    "SELECT DISTINCT movie_id FROM {$wpdb->prefix}dc_credits WHERE type_id = %d AND person_id IN ($ph)",
                    ...$args
                ) );
            } else {
                $movie_ids = $wpdb->get_col( $wpdb->prepare(
                    "SELECT movie_id FROM {$wpdb->prefix}dc_credits WHERE type_id = %d AND person_id = %d",
                    $director_type_id,
                    (int) $director_id
                ) );
            }

            $movie_ids_by_director[ $director_id ] = $movie_ids;

            if ( empty( $movie_ids ) ) {
                $actor_sets[ $director_id ] = array();
                continue;
            }

            $ph   = implode( ',', array_fill( 0, count( $movie_ids ), '%d' ) );
            $args = array_merge( array( $actor_type_id ), array_map( 'intval', $movie_ids ) );
            $actor_sets[ $director_id ] = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT person_id FROM {$wpdb->prefix}dc_credits WHERE type_id = %d AND movie_id IN ($ph)",
                ...$args
            ) );
        }

        // Step 2: Intersect all actor sets.
        $shared_ids = null;
        foreach ( $actor_sets as $set ) {
            $shared_ids = ( null === $shared_ids ) ? $set : array_values( array_intersect( $shared_ids, $set ) );
        }

        if ( empty( $shared_ids ) ) {
            return array( 'actors' => array(), 'filmsByActor' => array() );
        }

        // Step 3: Load actor rows.
        $ph     = implode( ',', array_fill( 0, count( $shared_ids ), '%d' ) );
        $actors = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, name, nationality FROM {$wpdb->prefix}dc_people WHERE id IN ($ph) ORDER BY name ASC",
            ...array_map( 'intval', $shared_ids )
        ) );

        // Step 4: Build filmsByActor[actor_id][director_id] = [title, title, ...]
        $films_by_actor = array();

        foreach ( $ids as $director_id ) {
            $movie_ids = $movie_ids_by_director[ $director_id ] ?? array();
            if ( empty( $movie_ids ) ) {
                continue;
            }

            $actor_ph = implode( ',', array_fill( 0, count( $shared_ids ), '%d' ) );
            $movie_ph = implode( ',', array_fill( 0, count( $movie_ids ), '%d' ) );
            $args     = array_merge(
                array( $actor_type_id ),
                array_map( 'intval', $shared_ids ),
                array_map( 'intval', $movie_ids )
            );

            $credits = $wpdb->get_results( $wpdb->prepare(
                "SELECT c.person_id, mo.title
                 FROM {$wpdb->prefix}dc_credits c
                 JOIN {$wpdb->prefix}dc_movies mo ON mo.id = c.movie_id
                 WHERE c.type_id = %d
                   AND c.person_id IN ($actor_ph)
                   AND c.movie_id  IN ($movie_ph)",
                ...$args
            ) );

            foreach ( $credits as $credit ) {
                $films_by_actor[ $credit->person_id ][ $director_id ][] = $credit->title;
            }
        }

        // Deduplicate titles (actor may appear in the same film multiple times).
        foreach ( $films_by_actor as $actor_id => $by_director ) {
            foreach ( $by_director as $dir_id => $titles ) {
                $films_by_actor[ $actor_id ][ $dir_id ] = array_unique( $titles );
            }
        }

        return array( 'actors' => $actors, 'filmsByActor' => $films_by_actor );
    }
}
