<?php if ( ! defined( 'ABSPATH' ) ) exit;

$msg  = isset( $_GET['msg'] ) ? $_GET['msg'] : '';
$msgs = array(
    'reassigned' => 'Rückmeldungen erfolgreich zugeordnet, Platzhalter gelöscht.',
    'deleted'    => 'Platzhalter gelöscht.',
);

$auto_list = HE_Einladung::get_auto();
$manuelle  = HE_Einladung::get_all_manual();
?>
<div class="wrap he-wrap">
    <h1>🌐 Nicht zugeordnete Rückmeldungen</h1>
    <p class="he-subtitle">Rückmeldungen die über das Website-Formular eingegangen sind und noch keiner manuellen Einladung zugeordnet wurden.</p>

    <?php if ( $msg && isset( $msgs[ $msg ] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $msgs[ $msg ] ); ?></p></div>
    <?php endif; ?>

    <?php if ( empty( $auto_list ) ) : ?>
        <div class="he-card">
            <p>✅ Alle Rückmeldungen sind zugeordnet – hier gibt es nichts zu tun!</p>
        </div>
    <?php else : ?>
        <p>Es gibt <strong><?php echo count( $auto_list ); ?></strong> nicht zugeordnete Eingang<?php echo count( $auto_list ) > 1 ? 'änge' : ''; ?>.
           Bitte jede Rückmeldung der richtigen Einladung zuweisen oder löschen.</p>

        <?php foreach ( $auto_list as $auto ) :
            $resp_list = HE_Rueckmeldung::get_by_einladung( $auto->id );
        ?>
        <div class="he-card he-card--warning">
            <div class="he-card-header">
                <div>
                    <span class="he-badge he-badge--auto">🌐 Website</span>
                    <strong style="font-size:16px;margin-left:8px"><?php echo esc_html( $auto->name ); ?></strong>
                    <span style="color:#888;font-size:12px;margin-left:8px">
                        eingegangen am <?php echo esc_html( date_i18n( 'd.m.Y \u\m H:i \U\h\r', strtotime( $auto->erstellt_am ) ) ); ?>
                    </span>
                </div>
            </div>

            <?php if ( $resp_list ) : ?>
                <table class="widefat he-table" style="margin:12px 0">
                    <thead><tr><th>Name</th><th style="width:80px">Personen</th><th style="width:110px">Status</th><th>Anmerkung</th></tr></thead>
                    <tbody>
                    <?php foreach ( $resp_list as $r ) : ?>
                        <tr>
                            <td><?php echo esc_html( $r->name ); ?></td>
                            <td style="text-align:center"><?php echo intval( $r->personen ); ?></td>
                            <td>
                                <span class="he-badge <?php echo $r->status === 'kommt' ? 'he-badge--green' : 'he-badge--red'; ?>">
                                    <?php echo $r->status === 'kommt' ? '✔ Kommt' : '✘ Kommt nicht'; ?>
                                </span>
                            </td>
                            <td><?php echo esc_html( $r->anmerkung ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p style="color:#888;font-style:italic;margin:8px 0">Keine Rückmeldungen zu diesem Eintrag.</p>
            <?php endif; ?>

            <div class="he-card-actions">
                <!-- Zuordnen-Formular -->
                <form method="post" style="display:inline-flex;align-items:center;gap:8px"
                      onsubmit="return confirm('Rückmeldungen verschieben und Platzhalter löschen?')">
                    <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                    <input type="hidden" name="he_action" value="reassign_einladung">
                    <input type="hidden" name="auto_id" value="<?php echo $auto->id; ?>">
                    <select name="target_id" required style="max-width:220px">
                        <option value="">– Einladung wählen –</option>
                        <?php foreach ( $manuelle as $inv ) : ?>
                            <option value="<?php echo $inv->id; ?>"><?php echo esc_html( $inv->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button button-primary">🔗 Zuordnen &amp; Platzhalter löschen</button>
                </form>

                <!-- Löschen -->
                <form method="post" style="display:inline"
                      onsubmit="return confirm('Platzhalter und alle zugehörigen Rückmeldungen löschen?')">
                    <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                    <input type="hidden" name="he_action" value="delete_einladung">
                    <input type="hidden" name="id" value="<?php echo $auto->id; ?>">
                    <button type="submit" class="button he-btn-danger">Löschen</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
