<?php if ( ! defined( 'ABSPATH' ) ) exit;

$action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
$edit_id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$edit_row = ( $action === 'edit' && $edit_id ) ? HE_Rueckmeldung::get( $edit_id ) : null;
$msg      = isset( $_GET['msg'] ) ? $_GET['msg'] : '';
$msgs     = array(
    'added'   => 'Rückmeldung eingetragen.',
    'updated' => 'Rückmeldung aktualisiert.',
    'deleted' => 'Rückmeldung gelöscht.',
);
$einladungen = HE_Einladung::get_all();
?>
<div class="wrap he-wrap">
    <h1>Rückmeldungen
        <a href="<?php echo admin_url( 'admin.php?page=hochzeit-rueckmeldungen&action=new' ); ?>" class="page-title-action">+ Neu</a>
    </h1>

    <?php if ( $msg && isset( $msgs[ $msg ] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $msgs[ $msg ] ); ?></p></div>
    <?php endif; ?>

    <?php if ( in_array( $action, array( 'new', 'edit' ) ) ) : ?>
        <div class="he-card">
            <h2><?php echo $action === 'edit' ? 'Rückmeldung bearbeiten' : 'Neue Rückmeldung eintragen'; ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                <input type="hidden" name="he_action" value="<?php echo $action === 'edit' ? 'edit_rueckmeldung' : 'add_rueckmeldung'; ?>">
                <?php if ( $edit_id ) : ?>
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>
                <table class="form-table">
                    <tr>
                        <th><label for="einladung_id">Einladung</label></th>
                        <td>
                            <select id="einladung_id" name="einladung_id" required>
                                <option value="">– bitte wählen –</option>
                                <?php foreach ( $einladungen as $inv ) : ?>
                                    <option value="<?php echo $inv->id; ?>"
                                        <?php selected( $edit_row ? $edit_row->einladung_id : '', $inv->id ); ?>>
                                        <?php echo esc_html( $inv->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="name">Name (Kontaktperson)</label></th>
                        <td><input type="text" id="name" name="name" class="regular-text" required
                            value="<?php echo $edit_row ? esc_attr( $edit_row->name ) : ''; ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="personen">Anzahl Personen</label></th>
                        <td><input type="number" id="personen" name="personen" min="0" max="20"
                            value="<?php echo $edit_row ? intval( $edit_row->personen ) : 1; ?>"></td>
                    </tr>
                    <tr>
                        <th><label>Status</label></th>
                        <td>
                            <label>
                                <input type="radio" name="status" value="kommt"
                                    <?php checked( $edit_row ? $edit_row->status : 'kommt', 'kommt' ); ?>> ✔ Kommt
                            </label> &nbsp;
                            <label>
                                <input type="radio" name="status" value="kommt_nicht"
                                    <?php checked( $edit_row ? $edit_row->status : '', 'kommt_nicht' ); ?>> ✘ Kommt nicht
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="anmerkung">Anmerkung</label></th>
                        <td><textarea id="anmerkung" name="anmerkung" class="large-text" rows="3"><?php echo $edit_row ? esc_textarea( $edit_row->anmerkung ) : ''; ?></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Speichern</button>
                    <a href="<?php echo admin_url( 'admin.php?page=hochzeit-rueckmeldungen' ); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>
    <?php else : ?>

        <?php $rows = HE_Rueckmeldung::get_all(); ?>
        <table class="wp-list-table widefat fixed striped he-table">
            <thead>
                <tr>
                    <th>Einladung</th>
                    <th>Name</th>
                    <th style="width:80px">Personen</th>
                    <th style="width:110px">Status</th>
                    <th>Anmerkung</th>
                    <th style="width:130px">Datum</th>
                    <th style="width:160px">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $rows ) ) : ?>
                <tr><td colspan="7">Noch keine Rückmeldungen vorhanden.</td></tr>
            <?php else : foreach ( $rows as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row->einladung_name ); ?></td>
                    <td><?php echo esc_html( $row->name ); ?></td>
                    <td style="text-align:center"><?php echo intval( $row->personen ); ?></td>
                    <td>
                        <?php if ( $row->status === 'kommt' ) : ?>
                            <span class="he-badge he-badge--green">✔ Kommt</span>
                        <?php else : ?>
                            <span class="he-badge he-badge--red">✘ Kommt nicht</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html( $row->anmerkung ); ?></td>
                    <td><?php echo esc_html( date_i18n( 'd.m.Y H:i', strtotime( $row->gemeldet_am ) ) ); ?></td>
                    <td>
                        <a href="<?php echo admin_url( 'admin.php?page=hochzeit-rueckmeldungen&action=edit&id=' . $row->id ); ?>" class="button button-small">Bearbeiten</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Rückmeldung löschen?')">
                            <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                            <input type="hidden" name="he_action" value="delete_rueckmeldung">
                            <input type="hidden" name="id" value="<?php echo $row->id; ?>">
                            <button type="submit" class="button button-small he-btn-danger">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        <p><strong><?php echo count( $rows ); ?></strong> Rückmeldungen gesamt.</p>
    <?php endif; ?>
</div>
