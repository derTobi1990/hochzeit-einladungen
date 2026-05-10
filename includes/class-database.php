<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HE_Database {

    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $inv     = $wpdb->prefix . HE_TABLE_INV;
        $resp    = $wpdb->prefix . HE_TABLE_RESP;

        // quelle: 'manuell' = im Backend angelegt, 'website' = über Frontend-Formular
        dbDelta( "CREATE TABLE {$inv} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name        VARCHAR(200) NOT NULL,
            adresse     TEXT NOT NULL,
            personen    TINYINT UNSIGNED NOT NULL DEFAULT 1,
            quelle      VARCHAR(10) NOT NULL DEFAULT 'manuell',
            erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset};" );

        dbDelta( "CREATE TABLE {$resp} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            einladung_id  BIGINT(20) UNSIGNED NOT NULL,
            name          VARCHAR(200) NOT NULL,
            personen      TINYINT UNSIGNED NOT NULL DEFAULT 1,
            status        VARCHAR(20) NOT NULL DEFAULT 'kommt',
            anmerkung     TEXT,
            gemeldet_am   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY einladung_id (einladung_id)
        ) {$charset};" );

        // Migrate existing auto-entries (adresse = '(über Website)') if column was just added
        $col_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$inv} LIKE 'quelle'" );
        if ( empty( $col_exists ) ) {
            $wpdb->query( "ALTER TABLE {$inv} ADD COLUMN quelle VARCHAR(10) NOT NULL DEFAULT 'manuell'" );
        }
        $wpdb->query( "UPDATE {$inv} SET quelle='website' WHERE adresse='(über Website)' AND quelle='manuell'" );

        update_option( 'he_db_version', HE_VERSION );
    }

    /** Check whether both tables exist */
    public static function tables_exist() {
        global $wpdb;
        $inv  = $wpdb->prefix . HE_TABLE_INV;
        $resp = $wpdb->prefix . HE_TABLE_RESP;
        $inv_ok  = $wpdb->get_var( "SHOW TABLES LIKE '{$inv}'"  ) === $inv;
        $resp_ok = $wpdb->get_var( "SHOW TABLES LIKE '{$resp}'" ) === $resp;
        return array( 'einladungen' => $inv_ok, 'rueckmeldungen' => $resp_ok );
    }

    public static function uninstall() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . HE_TABLE_RESP );
        $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . HE_TABLE_INV );
        delete_option( 'he_db_version' );
    }
}
