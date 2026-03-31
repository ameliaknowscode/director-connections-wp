<?php if ( ! defined( 'ABSPATH' ) ) exit;

$action   = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
$movie_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

// Handle delete
if ( 'delete' === $action && $movie_id ) {
    check_admin_referer( 'dc_delete_movie_' . $movie_id );
    DC_Movies::delete( $movie_id );
    wp_redirect( add_query_arg( array( 'page' => 'dc-movies', 'deleted' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
}

// Handle bulk delete
if ( isset( $_POST['action'] ) && 'bulk_delete' === $_POST['action'] && ! empty( $_POST['movies'] ) ) {
    check_admin_referer( 'bulk-movies' );
    foreach ( array_map( 'intval', $_POST['movies'] ) as $id ) {
        DC_Movies::delete( $id );
    }
    wp_redirect( add_query_arg( array( 'page' => 'dc-movies', 'deleted' => count( $_POST['movies'] ) ), admin_url( 'admin.php' ) ) );
    exit;
}

if ( in_array( $action, array( 'add', 'edit' ), true ) ) {
    include __DIR__ . '/movies-form.php';
    return;
}

// --- List view ---
$table = new DC_List_Table_Movies();
$table->prepare_items();

$add_url = add_query_arg( array( 'page' => 'dc-movies', 'action' => 'add' ), admin_url( 'admin.php' ) );
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Movies</h1>
    <a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action">Add New</a>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Movie saved.</p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo (int) $_GET['deleted']; ?> movie(s) deleted.</p></div>
    <?php endif; ?>

    <form method="get">
        <input type="hidden" name="page" value="dc-movies" />
        <?php $table->search_box( 'Search Movies', 'dc-movies-search' ); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>
