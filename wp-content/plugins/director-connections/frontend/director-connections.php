<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Variables available from DC_Shortcode::render():
 * $all_directors      array of director objects (id, name)
 * $ids                sanitized selected director IDs from GET
 * $selected_directors array of selected director objects
 * $actors             array of actor objects (id, name, nationality)
 * $films_by_actor     array [actor_id][director_id] => string[]
 */

// Ensure at least 2 slots in the form.
$initial = array_values( $ids );
while ( count( $initial ) < 2 ) {
    $initial[] = '';
}
?>
<div class="dc-wrap">

    <div class="dc-form-card">
        <p class="dc-intro">
            Pick two or more directors to find every actor who appeared in at least one film by <em>each</em> of them.
        </p>

        <form
            method="GET"
            action=""
            x-data="dcForm(<?php echo wp_json_encode( array_map( 'strval', $initial ) ); ?>)"
        >
            <div class="dc-slots" x-ref="slots">
                <template x-for="(val, idx) in directors" :key="idx">
                    <div class="dc-slot">
                        <label class="dc-slot-label" x-text="'Director ' + (idx + 1)"></label>
                        <select
                            :name="'directors[' + idx + ']'"
                            x-model="directors[idx]"
                            class="dc-select"
                        >
                            <option value="">— Select a director —</option>
                            <?php foreach ( $all_directors as $dir ) : ?>
                            <option
                                value="<?php echo esc_attr( (string) $dir->id ); ?>"
                                x-show="!directors.some((v, j) => j !== idx && v === <?php echo wp_json_encode( (string) $dir->id ); ?>)"
                            ><?php echo esc_html( $dir->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button
                            type="button"
                            class="dc-remove-btn"
                            @click="remove(idx)"
                            x-show="directors.length > 1"
                            title="Remove"
                            aria-label="Remove director"
                        >&times;</button>
                    </div>
                </template>
            </div>

            <div class="dc-form-actions">
                <button
                    type="button"
                    class="dc-btn dc-btn-secondary"
                    @click="add()"
                    x-show="directors.length < <?php echo count( $all_directors ); ?>"
                >+ Add director</button>
                <button type="submit" class="dc-btn dc-btn-primary">Find connections</button>
            </div>
        </form>
    </div>

    <?php if ( ! empty( $selected_directors ) ) : ?>
    <div class="dc-results-card">

        <div class="dc-results-header">
            <h3 class="dc-results-title">
                Actors in films by <?php echo esc_html( implode( ' &amp; ', array_column( $selected_directors, 'name' ) ) ); ?>
            </h3>
            <?php if ( ! empty( $actors ) ) : ?>
            <p class="dc-results-count">
                <?php echo count( $actors ); ?> <?php echo count( $actors ) === 1 ? 'actor' : 'actors'; ?> appeared in at least one film by each director.
            </p>
            <?php endif; ?>
        </div>

        <div class="dc-results-body">
            <?php if ( empty( $actors ) ) : ?>
            <div class="dc-no-results">
                <p class="dc-no-results-heading">No actors in common.</p>
                <p class="dc-no-results-sub">These directors haven't shared any cast members. Try a different combination.</p>
            </div>
            <?php else : ?>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead>
                        <tr>
                            <th>Actor</th>
                            <?php foreach ( $selected_directors as $dir ) : ?>
                            <th><?php echo esc_html( $dir->name ); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $actors as $actor ) : ?>
                        <tr>
                            <td class="dc-actor-cell">
                                <span class="dc-actor-name"><?php echo esc_html( $actor->name ); ?></span>
                                <?php if ( $actor->nationality ) : ?>
                                <span class="dc-actor-nationality"><?php echo esc_html( $actor->nationality ); ?></span>
                                <?php endif; ?>
                            </td>
                            <?php foreach ( $selected_directors as $dir ) : ?>
                            <td class="dc-films-cell">
                                <?php
                                $titles = $films_by_actor[ $actor->id ][ (string) $dir->id ] ?? array();
                                if ( $titles ) {
                                    echo esc_html( implode( ', ', $titles ) );
                                } else {
                                    echo '<span class="dc-dash">&mdash;</span>';
                                }
                                ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div>
    <?php endif; ?>

</div>

<script>
function dcForm(initial) {
    return {
        directors: initial,
        add()       { this.directors.push(''); },
        remove(idx) { this.directors.splice(idx, 1); }
    };
}
</script>
