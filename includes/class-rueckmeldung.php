<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HE_Rueckmeldung {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . HE_TABLE_RESP;
    }

    public static function get_all() {
        global $wpdb;
        $t_resp = self::table();
        $t_inv  = $wpdb->prefix . HE_TABLE_INV;
        return $wpdb->get_results(
            "SELECT r.*, i.name AS einladung_name
             FROM {$t_resp} r
             LEFT JOIN {$t_inv} i ON i.id = r.einladung_id
             ORDER BY r.gemeldet_am DESC"
        );
    }

    public static function get_by_einladung( $einladung_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            'SELECT * FROM ' . self::table() . ' WHERE einladung_id = %d ORDER BY gemeldet_am DESC',
            $einladung_id
        ) );
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
            'einladung_id' => absint( $data['einladung_id'] ),
            'name'         => sanitize_text_field( $data['name'] ),
            'personen'     => absint( $data['personen'] ),
            'status'       => in_array( $data['status'], array( 'kommt', 'kommt_nicht' ) ) ? $data['status'] : 'kommt',
            'anmerkung'    => isset( $data['anmerkung'] ) ? sanitize_textarea_field( $data['anmerkung'] ) : '',
        ), array( '%d', '%s', '%d', '%s', '%s' ) );
        return $wpdb->insert_id;
    }

    public static function update( $id, $data ) {
        global $wpdb;
        return $wpdb->update( self::table(), array(
            'name'      => sanitize_text_field( $data['name'] ),
            'personen'  => absint( $data['personen'] ),
            'status'    => in_array( $data['status'], array( 'kommt', 'kommt_nicht' ) ) ? $data['status'] : 'kommt',
            'anmerkung' => isset( $data['anmerkung'] ) ? sanitize_textarea_field( $data['anmerkung'] ) : '',
        ), array( 'id' => $id ), array( '%s', '%d', '%s', '%s' ), array( '%d' ) );
    }

    public static function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
    }
}
