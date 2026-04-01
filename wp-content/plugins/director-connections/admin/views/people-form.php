<?php if ( ! defined( 'ABSPATH' ) ) exit;

$is_edit  = ( 'edit' === $action && $person_id > 0 );
$person   = $is_edit ? DC_People::find( $person_id ) : null;
$list_url = add_query_arg( array( 'page' => 'dc-people' ), admin_url( 'admin.php' ) );
$error    = DC_Admin::get_error();

if ( $is_edit && ! $person ) {
    echo '<div class="wrap"><div class="notice notice-error"><p>Person not found.</p></div></div>';
    return;
}

$name        = $person->name ?? '';
$nationality = $person->nationality ?? '';
?>
<div class="wrap">
    <h1><?php echo $is_edit ? 'Edit Person' : 'Add Person'; ?></h1>
    <a href="<?php echo esc_url( $list_url ); ?>">&larr; Back to People</a>

    <?php if ( $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>

    <form method="post" class="dc-form">
        <?php wp_nonce_field( 'dc_save_person' ); ?>
        <input type="hidden" name="dc_save_person" value="1" />
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="person_id" value="<?php echo (int) $person_id; ?>" />
        <?php endif; ?>

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
