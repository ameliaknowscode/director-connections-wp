<?php if ( ! defined( 'ABSPATH' ) ) exit;

$is_edit   = ( 'edit' === $action && $credit_id > 0 );
$credit    = $is_edit ? DC_Credits::find( $credit_id ) : null;
$list_url  = add_query_arg( array( 'page' => 'dc-credits' ), admin_url( 'admin.php' ) );
$error     = DC_Admin::get_error();

if ( $is_edit && ! $credit ) {
    echo '<div class="wrap"><div class="notice notice-error"><p>Credit not found.</p></div></div>';
    return;
}

$sel_person_id      = $credit->person_id ?? 0;
$sel_movie_id       = $credit->movie_id ?? 0;
$sel_type_id        = $credit->type_id ?? 0;
$sel_character_name = $credit->character_name ?? '';

$people = DC_People::all( array( 'limit' => 9999 ) );
$movies = DC_Movies::all( array( 'limit' => 9999 ) );
$types  = DC_Credits::get_types();
?>
<div class="wrap">
    <h1><?php echo $is_edit ? 'Edit Credit' : 'Add Credit'; ?></h1>
    <a href="<?php echo esc_url( $list_url ); ?>">&larr; Back to Credits</a>

    <?php if ( $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>

    <form method="post" class="dc-form">
        <?php wp_nonce_field( 'dc_save_credit' ); ?>
        <input type="hidden" name="dc_save_credit" value="1" />
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="credit_id" value="<?php echo (int) $credit_id; ?>" />
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th><label for="person_id">Person <span class="required">*</span></label></th>
                <td>
                    <select id="person_id" name="person_id" required>
                        <option value="">— Select Person —</option>
                        <?php foreach ( $people as $p ) : ?>
                            <option value="<?php echo (int) $p->id; ?>" <?php selected( $sel_person_id, $p->id ); ?>>
                                <?php echo esc_html( $p->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="movie_id">Movie <span class="required">*</span></label></th>
                <td>
                    <select id="movie_id" name="movie_id" required>
                        <option value="">— Select Movie —</option>
                        <?php foreach ( $movies as $m ) : ?>
                            <option value="<?php echo (int) $m->id; ?>" <?php selected( $sel_movie_id, $m->id ); ?>>
                                <?php echo esc_html( $m->title ); ?>
                                <?php if ( $m->release_year ) echo '(' . (int) $m->release_year . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="type_id">Role <span class="required">*</span></label></th>
                <td>
                    <select id="type_id" name="type_id" required>
                        <option value="">— Select Role —</option>
                        <?php foreach ( $types as $t ) : ?>
                            <option value="<?php echo (int) $t->id; ?>" <?php selected( $sel_type_id, $t->id ); ?>>
                                <?php echo esc_html( $t->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="character_name">Character Name</label></th>
                <td>
                    <input type="text" id="character_name" name="character_name" value="<?php echo esc_attr( $sel_character_name ); ?>" class="regular-text" />
                    <p class="description">Only required for acting roles.</p>
                </td>
            </tr>
        </table>

        <?php submit_button( $is_edit ? 'Update Credit' : 'Add Credit' ); ?>
    </form>
</div>
