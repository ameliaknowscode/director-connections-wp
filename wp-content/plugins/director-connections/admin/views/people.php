<?php if ( ! defined( 'ABSPATH' ) ) exit;

$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
$person_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

// Handle delete
if ( 'delete' === $action && $person_id ) {
    check_admin_referer( 'dc_delete_person_' . $person_id );
    DC_People::delete( $person_id );
    wp_redirect( add_query_arg( array( 'page' => 'dc-people', 'deleted' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
}

// Handle bulk delete
if ( isset( $_POST['action'] ) && 'bulk_delete' === $_POST['action'] && ! empty( $_POST['people'] ) ) {
    check_admin_referer( 'bulk-people' );
    foreach ( array_map( 'intval', $_POST['people'] ) as $id ) {
        DC_People::delete( $id );
    }
    wp_redirect( add_query_arg( array( 'page' => 'dc-people', 'deleted' => count( $_POST['people'] ) ), admin_url( 'admin.php' ) ) );
    exit;
}

if ( in_array( $action, array( 'add', 'edit' ), true ) ) {
    include __DIR__ . '/people-form.php';
    return;
}

// --- List view ---
$table = new DC_List_Table_People();
$table->prepare_items();

$add_url = add_query_arg( array( 'page' => 'dc-people', 'action' => 'add' ), admin_url( 'admin.php' ) );
?>
<div class="wrap">
    <h1 class="wp-heading-inline">People</h1>
    <a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action">Add New</a>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Person saved.</p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo (int) $_GET['deleted']; ?> person(s) deleted.</p></div>
    <?php endif; ?>

    <form method="get">
        <input type="hidden" name="page" value="dc-people" />
        <?php $table->search_box( 'Search People', 'dc-people-search' ); ?>
    </form>

    <form method="post">
        <?php
        $table->display();
        ?>
    </form>
</div>
