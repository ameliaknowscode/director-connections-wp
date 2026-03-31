<?php if ( ! defined( 'ABSPATH' ) ) exit;

$action    = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
$credit_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

// Handle delete
if ( 'delete' === $action && $credit_id ) {
    check_admin_referer( 'dc_delete_credit_' . $credit_id );
    DC_Credits::delete( $credit_id );
    wp_redirect( add_query_arg( array( 'page' => 'dc-credits', 'deleted' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
}

// Handle bulk delete
if ( isset( $_POST['action'] ) && 'bulk_delete' === $_POST['action'] && ! empty( $_POST['credits'] ) ) {
    check_admin_referer( 'bulk-credits' );
    foreach ( array_map( 'intval', $_POST['credits'] ) as $id ) {
        DC_Credits::delete( $id );
    }
    wp_redirect( add_query_arg( array( 'page' => 'dc-credits', 'deleted' => count( $_POST['credits'] ) ), admin_url( 'admin.php' ) ) );
    exit;
}

if ( in_array( $action, array( 'add', 'edit' ), true ) ) {
    include __DIR__ . '/credits-form.php';
    return;
}

// --- List view ---
$table = new DC_List_Table_Credits();
$table->prepare_items();

$add_url = add_query_arg( array( 'page' => 'dc-credits', 'action' => 'add' ), admin_url( 'admin.php' ) );
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Credits</h1>
    <a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action">Add New</a>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Credit saved.</p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo (int) $_GET['deleted']; ?> credit(s) deleted.</p></div>
    <?php endif; ?>

    <form method="get">
        <input type="hidden" name="page" value="dc-credits" />
        <?php $table->search_box( 'Search Credits', 'dc-credits-search' ); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>
