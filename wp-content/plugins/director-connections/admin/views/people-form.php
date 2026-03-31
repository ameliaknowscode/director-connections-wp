<?php if ( ! defined( 'ABSPATH' ) ) exit;

$is_edit  = ( 'edit' === $action && $person_id > 0 );
$person   = $is_edit ? DC_People::find( $person_id ) : null;
$list_url = add_query_arg( array( 'page' => 'dc-people' ), admin_url( 'admin.php' ) );

if ( $is_edit && ! $person ) {
    echo '<div class="wrap"><div class="notice notice-error"><p>Person not found.</p></div></div>';
    return;
}

// Handle save
if ( isset( $_POST['dc_save_person'] ) ) {
    check_admin_referer( 'dc_save_person' );

    $name        = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
    $nationality = sanitize_text_field( wp_unslash( $_POST['nationality'] ?? '' ) );

    if ( empty( $name ) ) {
        $error = 'Name is required.';
    } else {
        $data = array( 'name' => $name, 'nationality' => $nationality );

        if ( $is_edit ) {
            DC_People::update( $person_id, $data );
        } else {
            DC_People::insert( $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'dc-people', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
        exit;
    }
}

$name        = isset( $error ) ? sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ) : ( $person->name ?? '' );
$nationality = isset( $error ) ? sanitize_text_field( wp_unslash( $_POST['nationality'] ?? '' ) ) : ( $person->nationality ?? '' );
?>
<div class="wrap">
    <h1><?php echo $is_edit ? 'Edit Person' : 'Add Person'; ?></h1>
    <a href="<?php echo esc_url( $list_url ); ?>">&larr; Back to People</a>

    <?php if ( isset( $error ) ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>

    <form method="post" class="dc-form">
        <?php wp_nonce_field( 'dc_save_person' ); ?>
        <input type="hidden" name="dc_save_person" value="1" />

        <table class="form-table">
            <tr>
                <th><label for="name">Name <span class="required">*</span></label></th>
                <td><input type="text" id="name" name="name" value="<?php echo esc_attr( $name ); ?>" class="regular-text" required /></td>
            </tr>
            <tr>
                <th><label for="nationality">Nationality</label></th>
                <td><input type="text" id="nationality" name="nationality" value="<?php echo esc_attr( $nationality ); ?>" class="regular-text" /></td>
            </tr>
        </table>

        <?php submit_button( $is_edit ? 'Update Person' : 'Add Person' ); ?>
    </form>
</div>
