<?php if ( ! defined( 'ABSPATH' ) ) exit;

$is_edit  = ( 'edit' === $action && $movie_id > 0 );
$movie    = $is_edit ? DC_Movies::find( $movie_id ) : null;
$list_url = add_query_arg( array( 'page' => 'dc-movies' ), admin_url( 'admin.php' ) );
$error    = DC_Admin::get_error();

if ( $is_edit && ! $movie ) {
    echo '<div class="wrap"><div class="notice notice-error"><p>Movie not found.</p></div></div>';
    return;
}

$title        = $movie->title ?? '';
$release_year = $movie->release_year ?? '';
?>
<div class="wrap">
    <h1><?php echo $is_edit ? 'Edit Movie' : 'Add Movie'; ?></h1>
    <a href="<?php echo esc_url( $list_url ); ?>">&larr; Back to Movies</a>

    <?php if ( $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>

    <form method="post" class="dc-form">
        <?php wp_nonce_field( 'dc_save_movie' ); ?>
        <input type="hidden" name="dc_save_movie" value="1" />
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="movie_id" value="<?php echo (int) $movie_id; ?>" />
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th><label for="title">Title <span class="required">*</span></label></th>
                <td><input type="text" id="title" name="title" value="<?php echo esc_attr( $title ); ?>" class="regular-text" required /></td>
            </tr>
            <tr>
                <th><label for="release_year">Release Year</label></th>
                <td><input type="number" id="release_year" name="release_year" value="<?php echo esc_attr( $release_year ); ?>" class="small-text" min="1888" max="2099" /></td>
            </tr>
        </table>

        <?php submit_button( $is_edit ? 'Update Movie' : 'Add Movie' ); ?>
    </form>
</div>
