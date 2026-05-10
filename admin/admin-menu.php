<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HE_Admin {

    public static function init() {
        add_action( 'admin_menu',            array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_init',            array( __CLASS__, 'handle_forms' ) );
    }

    public static function register_menu() {
        add_menu_page(
            'Hochzeit Einladungen', '💍 Hochzeit', 'manage_options',
            'hochzeit-dashboard', array( __CLASS__, 'page_dashboard' ),
            'dashicons-heart', 30
        );
        add_submenu_page( 'hochzeit-dashboard', 'Dashboard', 'Dashboard',
            'manage_options', 'hochzeit-dashboard', array( __CLASS__, 'page_dashboard' ) );
        add_submenu_page( 'hochzeit-dashboard', 'Einladungen', 'Einladungen',
            'manage_options', 'hochzeit-einladungen', array( __CLASS__, 'page_einladungen' ) );
        add_submenu_page( 'hochzeit-dashboard', 'Rückmeldungen', 'Rückmeldungen',
            'manage_options', 'hochzeit-rueckmeldungen', array( __CLASS__, 'page_rueckmeldungen' ) );

        // Dynamic label with count badge for unassigned
        $auto_count = HE_Einladung::get_auto_count();
        $label = $auto_count > 0
            ? 'Nicht zugeordnet <span class="awaiting-mod">' . $auto_count . '</span>'
            : 'Nicht zugeordnet';
        add_submenu_page( 'hochzeit-dashboard', 'Nicht zugeordnete Rückmeldungen', $label,
            'manage_options', 'hochzeit-nicht-zugeordnet', array( __CLASS__, 'page_nicht_zugeordnet' ) );

        add_submenu_page( 'hochzeit-dashboard', 'Einstellungen', '⚙ Einstellungen',
            'manage_options', 'hochzeit-einstellungen', array( __CLASS__, 'page_einstellungen' ) );
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'hochzeit' ) === false ) return;
        wp_enqueue_style( 'he-admin', HE_PLUGIN_URL . 'assets/css/admin.css', array(), HE_VERSION );
    }

    public static function handle_forms() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $action = isset( $_POST['he_action'] ) ? $_POST['he_action'] : '';
        if ( ! $action ) return;
        if ( ! check_admin_referer( 'he_nonce', 'he_nonce_field' ) ) wp_die( 'Sicherheitsfehler' );

        switch ( $action ) {
            case 'repair_db':
                HE_Database::install();
                wp_redirect( admin_url( 'admin.php?page=hochzeit-dashboard&msg=repaired' ) );
                exit;

            case 'save_settings':
                HE_Settings::save( $_POST );
                wp_redirect( admin_url( 'admin.php?page=hochzeit-einstellungen&msg=saved' ) );
                exit;

            case 'add_einladung':
                HE_Einladung::create( $_POST );
                wp_redirect( admin_url( 'admin.php?page=hochzeit-einladungen&msg=added' ) );
                exit;

            case 'edit_einladung':
                HE_Einladung::update( absint( $_POST['id'] ), $_POST );
                wp_redirect( admin_url( 'admin.php?page=hochzeit-einladungen&msg=updated' ) );
                exit;

            case 'delete_einladung':
                HE_Einladung::delete( absint( $_POST['id'] ) );
                // Redirect back to where the user came from
                $ref = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
                if ( strpos( $ref, 'nicht-zugeordnet' ) !== false ) {
                    wp_redirect( admin_url( 'admin.php?page=hochzeit-nicht-zugeordnet&msg=deleted' ) );
                } else {
                    wp_redirect( admin_url( 'admin.php?page=hochzeit-einladungen&msg=deleted' ) );
                }
                exit;

            case 'reassign_einladung':
                $auto_id   = absint( $_POST['auto_id'] );
                $target_id = absint( $_POST['target_id'] );
                if ( $auto_id && $target_id ) {
                    HE_Einladung::reassign_and_delete( $auto_id, $target_id );
                }
                $ref = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
                if ( strpos( $ref, 'nicht-zugeordnet' ) !== false ) {
                    wp_redirect( admin_url( 'admin.php?page=hochzeit-nicht-zugeordnet&msg=reassigned' ) );
                } else {
                    wp_redirect( admin_url( 'admin.php?page=hochzeit-einladungen&msg=reassigned' ) );
                }
                exit;

            case 'add_rueckmeldung':
                HE_Rueckmeldung::create( $_POST );
                wp_redirect( admin_url( 'admin.php?page=hochzeit-rueckmeldungen&msg=added' ) );
                exit;

            case 'edit_rueckmeldung':
                HE_Rueckmeldung::update( absint( $_POST['id'] ), $_POST );
                wp_redirect( admin_url( 'admin.php?page=hochzeit-rueckmeldungen&msg=updated' ) );
                exit;

            case 'delete_rueckmeldung':
                HE_Rueckmeldung::delete( absint( $_POST['id'] ) );
                wp_redirect( admin_url( 'admin.php?page=hochzeit-rueckmeldungen&msg=deleted' ) );
                exit;
        }
    }

    public static function page_dashboard()         { include HE_PLUGIN_DIR . 'admin/views/dashboard.php'; }
    public static function page_einladungen()        { include HE_PLUGIN_DIR . 'admin/views/einladungen.php'; }
    public static function page_rueckmeldungen()     { include HE_PLUGIN_DIR . 'admin/views/rueckmeldungen.php'; }
    public static function page_nicht_zugeordnet()   { include HE_PLUGIN_DIR . 'admin/views/nicht-zugeordnet.php'; }
    public static function page_einstellungen()      { include HE_PLUGIN_DIR . 'admin/views/einstellungen.php'; }
}

HE_Admin::init();
