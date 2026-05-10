<?php if ( ! defined( 'ABSPATH' ) ) exit;
$stats  = HE_Einladung::get_stats();
$tables = HE_Database::tables_exist();
$msg    = isset( $_GET['msg'] ) ? $_GET['msg'] : '';
?>
<div class="wrap he-wrap">
    <h1>💍 Hochzeit Alina &amp; Tobias – Dashboard</h1>
    <p class="he-subtitle">5. September 2026</p>

    <?php if ( $msg === 'repaired' ) : ?>
        <div class="notice notice-success is-dismissible"><p>✔ Datenbank-Tabellen wurden erfolgreich angelegt/repariert.</p></div>
    <?php endif; ?>

    <?php if ( ! $tables['einladungen'] || ! $tables['rueckmeldungen'] ) : ?>
        <div class="notice notice-error">
            <p><strong>⚠ Datenbank-Problem:</strong>
                Tabelle <code>einladungen</code>: <?php echo $tables['einladungen'] ? '✔ OK' : '✘ fehlt'; ?> &nbsp;|&nbsp;
                Tabelle <code>rueckmeldungen</code>: <?php echo $tables['rueckmeldungen'] ? '✔ OK' : '✘ fehlt'; ?>
            </p>
            <form method="post">
                <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
                <input type="hidden" name="he_action" value="repair_db">
                <button type="submit" class="button button-primary">🔧 Datenbank jetzt reparieren</button>
            </form>
        </div>
    <?php else : ?>
        <div class="notice notice-success inline" style="margin:0 0 16px"><p>✔ Datenbank OK</p></div>
    <?php endif; ?>

    <div class="he-stats-grid">
        <div class="he-stat he-stat--neutral">
            <span class="he-stat__num"><?php echo $stats['eingeladen_gruppen']; ?></span>
            <span class="he-stat__label">Einladungen versendet</span>
            <span class="he-stat__sub"><?php echo $stats['eingeladen_personen']; ?> Personen erwartet</span>
        </div>
        <div class="he-stat he-stat--green">
            <span class="he-stat__num"><?php echo $stats['kommt_gruppen']; ?></span>
            <span class="he-stat__label">Zusagen</span>
            <span class="he-stat__sub"><?php echo $stats['kommt_personen']; ?> Personen kommen</span>
        </div>
        <div class="he-stat he-stat--red">
            <span class="he-stat__num"><?php echo $stats['kommt_nicht_gruppen']; ?></span>
            <span class="he-stat__label">Absagen</span>
            <span class="he-stat__sub"><?php echo $stats['kommt_nicht_personen']; ?> Personen</span>
        </div>
        <div class="he-stat he-stat--yellow">
            <span class="he-stat__num"><?php echo $stats['keine_rueckmeldung']; ?></span>
            <span class="he-stat__label">Ausstehend</span>
            <span class="he-stat__sub">Keine Rückmeldung</span>
        </div>
    </div>

    <?php
    $eingeladen = $stats['eingeladen_personen'] ?: 1;
    $pct_kommt  = round( $stats['kommt_personen'] / $eingeladen * 100 );
    ?>
    <div class="he-progress-section">
        <h2>Rücklauf Personen</h2>
        <div class="he-progress-bar">
            <div class="he-progress-fill he-progress-fill--green" style="width:<?php echo $pct_kommt; ?>%">
                <?php echo $pct_kommt; ?>% kommen
            </div>
        </div>
        <p class="he-progress-legend">
            <strong><?php echo $stats['kommt_personen']; ?></strong> von
            <strong><?php echo $stats['eingeladen_personen']; ?></strong> eingeladenen Personen haben zugesagt.
        </p>
    </div>

    <div class="he-quick-links">
        <a href="<?php echo admin_url( 'admin.php?page=hochzeit-einladungen&action=new' ); ?>" class="button button-primary">+ Einladung hinzufügen</a>
        <a href="<?php echo admin_url( 'admin.php?page=hochzeit-rueckmeldungen&action=new' ); ?>" class="button button-secondary">+ Rückmeldung eintragen</a>
    </div>
</div>
