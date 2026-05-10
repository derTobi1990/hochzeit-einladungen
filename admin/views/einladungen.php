<?php if ( ! defined( 'ABSPATH' ) ) exit;

$action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
$edit_id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$edit_row = ( $action === 'edit' && $edit_id ) ? HE_Einladung::get( $edit_id ) : null;
$msg      = isset( $_GET['msg'] ) ? $_GET['msg'] : '';
$msgs     = array(
    'added'      => 'Einladung hinzugefügt.',
    'updated'    => 'Einladung aktualisiert.',
    'deleted'    => 'Einladung und zugehörige Rückmeldungen gelöscht.',
    'reassigned' => 'Rückmeldungen erfolgreich zugeordnet, Platzhalter gelöscht.',
);
?>
<div class="wrap he-wrap">
    <h1>Einladungen
        <a href="<?php echo admin_url( 'admin.php?page=hochzeit-einladungen&action=new' ); ?>" class="page-title-action">+ Neu</a>
    </h1>

    <?php if ( $msg && isset( $msgs[ $msg ] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $msgs[ $msg ] ); ?></p></div>
    <?php endif; ?>

    <?php if ( in_array( $action, array( 'new', 'edit' ) ) ) : ?>
        <div class="he-card">
            <h2><?php echo $action === 'edit' ? 'Einladung bearbeiten' : 'Neue Einladung'; ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                <input type="hidden" name="he_action" value="<?php echo $action === 'edit' ? 'edit_einladung' : 'add_einladung'; ?>">
                <?php if ( $edit_id ) : ?>
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>
                <table class="form-table">
                    <tr>
                        <th><label for="name">Name / Familie</label></th>
                        <td><input type="text" id="name" name="name" class="regular-text" required
                            value="<?php echo $edit_row ? esc_attr( $edit_row->name ) : ''; ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="adresse">Adresse</label></th>
                        <td><textarea id="adresse" name="adresse" class="large-text" rows="3"><?php echo $edit_row ? esc_textarea( $edit_row->adresse ) : ''; ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="personen">Erwartete Personenzahl</label></th>
                        <td><input type="number" id="personen" name="personen" min="1" max="20"
                            value="<?php echo $edit_row ? intval( $edit_row->personen ) : 1; ?>"></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Speichern</button>
                    <a href="<?php echo admin_url( 'admin.php?page=hochzeit-einladungen' ); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>

    <?php elseif ( $action === 'zuordnen' && $edit_id ) :
        $auto_inv    = HE_Einladung::get( $edit_id );
        $manuelle    = HE_Einladung::get_all_manual();
        $resp_list   = HE_Rueckmeldung::get_by_einladung( $edit_id );
    ?>
        <div class="he-card he-card--warning">
            <h2>🔗 Rückmeldung zuordnen</h2>
            <p>Die folgende automatisch angelegte Einladung soll einer bestehenden zugeordnet werden.
               Alle Rückmeldungen werden verschoben, der Platzhalter wird anschließend gelöscht.</p>

            <table class="widefat he-table" style="margin-bottom:16px">
                <thead><tr><th>Platzhalter-Name</th><th>Eingegangen am</th><th>Rückmeldungen</th></tr></thead>
                <tbody>
                    <tr class="he-row--auto">
                        <td><strong><?php echo esc_html( $auto_inv->name ); ?></strong></td>
                        <td><?php echo esc_html( date_i18n( 'd.m.Y H:i', strtotime( $auto_inv->erstellt_am ) ) ); ?></td>
                        <td>
                            <?php foreach ( $resp_list as $r ) : ?>
                                <span class="he-badge <?php echo $r->status === 'kommt' ? 'he-badge--green' : 'he-badge--red'; ?>">
                                    <?php echo esc_html( $r->name ); ?> (<?php echo intval( $r->personen ); ?> P.)
                                </span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <form method="post" action="">
                <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                <input type="hidden" name="he_action" value="reassign_einladung">
                <input type="hidden" name="auto_id" value="<?php echo $edit_id; ?>">
                <table class="form-table">
                    <tr>
                        <th><label for="target_id">Zuordnen zu Einladung</label></th>
                        <td>
                            <select id="target_id" name="target_id" required>
                                <option value="">– bitte wählen –</option>
                                <?php foreach ( $manuelle as $inv ) : ?>
                                    <option value="<?php echo $inv->id; ?>"><?php echo esc_html( $inv->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary"
                        onclick="return confirm('Rückmeldungen verschieben und Platzhalter löschen?')">
                        ✔ Zuordnen &amp; Platzhalter löschen
                    </button>
                    <a href="<?php echo admin_url( 'admin.php?page=hochzeit-einladungen' ); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>

    <?php else : ?>

        <?php
        $rows      = HE_Einladung::get_all();
        $auto_count = HE_Einladung::get_auto_count();
        if ( $auto_count > 0 ) : ?>
            <div class="notice notice-warning inline" style="margin:0 0 16px">
                <p>⚠ <strong><?php echo $auto_count; ?> automatisch angelegte Einladung<?php echo $auto_count > 1 ? 'en' : ''; ?></strong>
                (über das Website-Formular) – bitte manuell zuordnen oder löschen.
                <a href="<?php echo admin_url( 'admin.php?page=hochzeit-nicht-zugeordnet' ); ?>">Alle anzeigen →</a></p>
            </div>
        <?php endif; ?>

        <table class="wp-list-table widefat fixed striped he-table">
            <thead>
                <tr>
                    <th>Name / Familie</th>
                    <th>Adresse</th>
                    <th style="width:80px">Personen</th>
                    <th style="width:130px">Rückmeldung</th>
                    <th style="width:200px">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $rows ) ) : ?>
                <tr><td colspan="5">Noch keine Einladungen erfasst.</td></tr>
            <?php else : foreach ( $rows as $row ) :
                $resp = HE_Rueckmeldung::get_by_einladung( $row->id );
                $is_auto = $row->quelle === 'website';
                if ( $resp ) {
                    $r = $resp[0];
                    $status_label = $r->status === 'kommt'
                        ? '<span class="he-badge he-badge--green">✔ kommt (' . $r->personen . ')</span>'
                        : '<span class="he-badge he-badge--red">✘ kommt nicht</span>';
                } else {
                    $status_label = '<span class="he-badge he-badge--yellow">ausstehend</span>';
                }
            ?>
                <tr class="<?php echo $is_auto ? 'he-row--auto' : ''; ?>">
                    <td>
                        <?php if ( $is_auto ) : ?>
                            <span class="he-badge he-badge--auto" title="Automatisch über Website angelegt">🌐 Website</span><br>
                        <?php endif; ?>
                        <strong><?php echo esc_html( $row->name ); ?></strong>
                    </td>
                    <td><?php echo $is_auto ? '<em style="color:#999">automatisch</em>' : nl2br( esc_html( $row->adresse ) ); ?></td>
                    <td style="text-align:center"><?php echo intval( $row->personen ); ?></td>
                    <td><?php echo $status_label; ?></td>
                    <td>
                        <?php if ( $is_auto ) : ?>
                            <a href="<?php echo admin_url( 'admin.php?page=hochzeit-einladungen&action=zuordnen&id=' . $row->id ); ?>"
                               class="button button-small button-primary">🔗 Zuordnen</a>
                        <?php else : ?>
                            <a href="<?php echo admin_url( 'admin.php?page=hochzeit-einladungen&action=edit&id=' . $row->id ); ?>"
                               class="button button-small">Bearbeiten</a>
                        <?php endif; ?>
                        <form method="post" style="display:inline"
                              onsubmit="return confirm('<?php echo $is_auto ? 'Platzhalter und zugehörige Rückmeldungen löschen?' : 'Einladung und alle Rückmeldungen löschen?'; ?>')">
                            <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                            <input type="hidden" name="he_action" value="delete_einladung">
                            <input type="hidden" name="id" value="<?php echo $row->id; ?>">
                            <button type="submit" class="button button-small he-btn-danger">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        <p><strong><?php echo count( $rows ); ?></strong> Einladungen insgesamt.</p>
    <?php endif; ?>
</div>
