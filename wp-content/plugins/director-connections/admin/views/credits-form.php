<?php if ( ! defined( 'ABSPATH' ) ) exit;

$is_edit   = ( 'edit' === $action && $credit_id > 0 );
$credit    = $is_edit ? DC_Credits::find( $credit_id ) : null;
$list_url  = add_query_arg( array( 'page' => 'dc-credits' ), admin_url( 'admin.php' ) );

if ( $is_edit && ! $credit ) {
    echo '<div class="wrap"><div class="notice notice-error"><p>Credit not found.</p></div></div>';
    return;
}

// Handle save
if ( isset( $_POST['dc_save_credit'] ) ) {
    check_admin_referer( 'dc_save_credit' );

    $person_id      = absint( $_POST['person_id'] ?? 0 );
    $movie_id       = absint( $_POST['movie_id'] ?? 0 );
    $type_id        = absint( $_POST['type_id'] ?? 0 );
    $character_name = sanitize_text_field( wp_unslash( $_POST['character_name'] ?? '' ) );

    if ( ! $person_id || ! $movie_id || ! $type_id ) {
        $error = 'Person, movie, and role are all required.';
    } else {
        $data = compact( 'person_id', 'movie_id', 'type_id', 'character_name' );

        if ( $is_edit ) {
            DC_Credits::update( $credit_id, $data );
        } else {
            DC_Credits::insert( $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'dc-credits', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
        exit;
    }
}

// Populate field values (re-show posted values on error, otherwise existing record)
$sel_person_id      = isset( $error ) ? absint( $_POST['person_id'] ?? 0 )   : ( $credit->person_id ?? 0 );
$sel_movie_id       = isset( $error ) ? absint( $_POST['movie_id'] ?? 0 )    : ( $credit->movie_id ?? 0 );
$sel_type_id        = isset( $error ) ? absint( $_POST['type_id'] ?? 0 )     : ( $credit->type_id ?? 0 );
$sel_character_name = isset( $error ) ? sanitize_text_field( wp_unslash( $_POST['character_name'] ?? '' ) ) : ( $credit->character_name ?? '' );

// Load dropdown options
$people = DC_People::all( array( 'limit' => 9999 ) );
$movies = DC_Movies::all( array( 'limit' => 9999 ) );
$types  = DC_Credits::get_types();
?>
<div class="wrap">
    <h1><?php echo $is_edit ? 'Edit Credit' : 'Add Credit'; ?></h1>
    <a href="<?php echo esc_url( $list_url ); ?>">&larr; Back to Credits</a>

    <?php if ( isset( $error ) ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>

    <form method="post" class="dc-form">
        <?php wp_nonce_field( 'dc_save_credit' ); ?>
        <input type="hidden" name="dc_save_credit" value="1" />

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
