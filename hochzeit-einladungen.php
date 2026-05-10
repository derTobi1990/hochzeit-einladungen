<?php
/**
 * Plugin Name: Hochzeit Einladungen
 * Plugin URI:  https://alinaundtobias.de
 * Description: Verwaltung von Hochzeitseinladungen und Rückmeldungen für Alina & Tobias
 * Version:     1.0.0
 * Author:      Tobias Hirche
 * Text Domain: hochzeit-einladungen
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HE_VERSION',     '1.0.0' );
define( 'HE_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'HE_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'HE_TABLE_INV',   'hochzeit_einladungen' );
define( 'HE_TABLE_RESP',  'hochzeit_rueckmeldungen' );

// ---------------------------------------------------------------
//  GitHub Auto-Updater Konfiguration
//  → HE_GITHUB_REPO auf dein Repository setzen, z.B.:
//    define( 'HE_GITHUB_REPO', 'tobias-hirche/hochzeit-einladungen' );
//  → Bei privatem Repository zusätzlich ein Personal Access Token:
//    define( 'HE_GITHUB_TOKEN', 'github_pat_xxxx...' );
// ---------------------------------------------------------------
if ( ! defined( 'HE_GITHUB_REPO' ) )  define( 'HE_GITHUB_REPO',  '' );
if ( ! defined( 'HE_GITHUB_TOKEN' ) ) define( 'HE_GITHUB_TOKEN', '' );

require_once HE_PLUGIN_DIR . 'includes/class-database.php';
require_once HE_PLUGIN_DIR . 'includes/class-einladung.php';
require_once HE_PLUGIN_DIR . 'includes/class-rueckmeldung.php';
require_once HE_PLUGIN_DIR . 'includes/class-settings.php';
require_once HE_PLUGIN_DIR . 'includes/class-updater.php';
require_once HE_PLUGIN_DIR . 'admin/admin-menu.php';
require_once HE_PLUGIN_DIR . 'frontend/shortcode.php';

register_activation_hook( __FILE__, array( 'HE_Database', 'install' ) );
register_uninstall_hook( __FILE__, array( 'HE_Database', 'uninstall' ) );

// Updater nur starten wenn ein Repo konfiguriert ist
if ( HE_GITHUB_REPO && is_admin() ) {
    new HE_Updater( __FILE__, HE_GITHUB_REPO, HE_GITHUB_TOKEN );
}
