<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HE_Einladung {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . HE_TABLE_INV;
    }

    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY quelle DESC, name ASC' );
    }

    /** Only manually created invitations (for dropdown selects) */
    public static function get_all_manual() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM " . self::table() . " WHERE quelle = 'manuell' ORDER BY name ASC"
        );
    }

    /** Only auto-created (website) invitations */
    public static function get_auto() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM " . self::table() . " WHERE quelle = 'website' ORDER BY erstellt_am DESC"
        );
    }

    public static function get_auto_count() {
        global $wpdb;
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM " . self::table() . " WHERE quelle = 'website'"
        );
    }

    public static function get( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id
        ) );
    }

    public static function create( $data ) {
        global $wpdb;
        $wpdb->insert( self::table(), array(
            'name'     => sanitize_text_field( $data['name'] ),
            'adresse'  => sanitize_textarea_field( $data['adresse'] ),
            'personen' => absint( $data['personen'] ),
            'quelle'   => isset( $data['quelle'] ) && $data['quelle'] === 'website' ? 'website' : 'manuell',
        ), array( '%s', '%s', '%d', '%s' ) );
        return $wpdb->insert_id;
    }

    public static function update( $id, $data ) {
        global $wpdb;
        return $wpdb->update( self::table(), array(
            'name'     => sanitize_text_field( $data['name'] ),
            'adresse'  => sanitize_textarea_field( $data['adresse'] ),
            'personen' => absint( $data['personen'] ),
        ), array( 'id' => $id ), array( '%s', '%s', '%d' ), array( '%d' ) );
    }

    /**
     * Reassign all Rückmeldungen from $auto_id to $target_id,
     * then delete the auto-created invitation.
     */
    public static function reassign_and_delete( $auto_id, $target_id ) {
        global $wpdb;
        $resp_table = $wpdb->prefix . HE_TABLE_RESP;

        // Move all responses to target invitation
        $wpdb->update(
            $resp_table,
            array( 'einladung_id' => $target_id ),
            array( 'einladung_id' => $auto_id ),
            array( '%d' ),
            array( '%d' )
        );

        // Delete the auto-created placeholder (no cascade needed, responses already moved)
        return $wpdb->delete( self::table(), array( 'id' => $auto_id ), array( '%d' ) );
    }

    public static function delete( $id ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . HE_TABLE_RESP, array( 'einladung_id' => $id ), array( '%d' ) );
        return $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
    }

    public static function get_stats() {
        global $wpdb;
        $t_inv  = self::table();
        $t_resp = $wpdb->prefix . HE_TABLE_RESP;

        $stats = array();
        $stats['eingeladen_gruppen']   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_inv} WHERE quelle='manuell'" );
        $stats['eingeladen_personen']  = (int) $wpdb->get_var( "SELECT SUM(personen) FROM {$t_inv} WHERE quelle='manuell'" );
        $stats['kommt_gruppen']        = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_resp} WHERE status='kommt'" );
        $stats['kommt_nicht_gruppen']  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_resp} WHERE status='kommt_nicht'" );
        $stats['kommt_personen']       = (int) $wpdb->get_var( "SELECT SUM(personen) FROM {$t_resp} WHERE status='kommt'" );
        $stats['kommt_nicht_personen'] = (int) $wpdb->get_var( "SELECT SUM(personen) FROM {$t_resp} WHERE status='kommt_nicht'" );
        $stats['keine_rueckmeldung']   = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$t_inv} i
             WHERE quelle='manuell'
             AND NOT EXISTS (SELECT 1 FROM {$t_resp} r WHERE r.einladung_id = i.id)"
        );
        $stats['auto_einladungen'] = self::get_auto_count();

        return $stats;
    }

    public static function find_by_name( $name ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::table() . " WHERE name LIKE %s AND quelle='manuell' ORDER BY name ASC",
            '%' . $wpdb->esc_like( $name ) . '%'
        ) );
    }
}
