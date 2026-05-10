<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Automatische Updates über GitHub Releases.
 *
 * Einrichtung:
 *  1. Plugin-Ordner auf GitHub in ein Repository pushen.
 *  2. Für jede neue Version ein GitHub Release anlegen mit:
 *     - Tag:   z.B. "1.1.0"
 *     - Asset: die Plugin-ZIP hochladen (Name egal, muss aber eine ZIP sein)
 *  3. HE_GITHUB_REPO in hochzeit-einladungen.php auf "dein-user/dein-repo" setzen.
 *  4. Bei privatem Repository: HE_GITHUB_TOKEN mit einem Personal Access Token setzen.
 */
class HE_Updater {

    private $plugin_slug;   // hochzeit-einladungen/hochzeit-einladungen.php
    private $plugin_file;   // absoluter Pfad zur Haupt-PHP-Datei
    private $github_repo;   // "username/repository"
    private $github_token;  // optional, für private Repos
    private $current_version;

    public function __construct( $plugin_file, $github_repo, $github_token = '' ) {
        $this->plugin_file      = $plugin_file;
        $this->plugin_slug      = plugin_basename( $plugin_file );
        $this->github_repo      = $github_repo;
        $this->github_token     = $github_token;
        $this->current_version  = HE_VERSION;

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api',                           array( $this, 'plugin_info' ), 10, 3 );
        add_filter( 'upgrader_post_install',                 array( $this, 'after_install' ), 10, 3 );
    }

    /** Fetch latest release info from GitHub API (cached 12h) */
    private function get_release() {
        $transient_key = 'he_github_release_' . md5( $this->github_repo );
        $cached        = get_transient( $transient_key );
        if ( $cached !== false ) return $cached;

        $url     = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        $args    = array(
            'headers' => array(
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
            ),
            'timeout' => 10,
        );
        if ( $this->github_token ) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->github_token;
        }

        $response = wp_remote_get( $url, $args );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            set_transient( $transient_key, null, HOUR_IN_SECONDS );
            return null;
        }

        $release = json_decode( wp_remote_retrieve_body( $response ) );
        set_transient( $transient_key, $release, 12 * HOUR_IN_SECONDS );
        return $release;
    }

    /** Get ZIP download URL from release assets, fall back to zipball */
    private function get_zip_url( $release ) {
        if ( ! empty( $release->assets ) ) {
            foreach ( $release->assets as $asset ) {
                if ( $asset->content_type === 'application/zip' ||
                     str_ends_with( $asset->name, '.zip' ) ) {
                    return $asset->browser_download_url;
                }
            }
        }
        // Fallback: GitHub auto-generated source ZIP
        return $release->zipball_url ?? null;
    }

    /** Normalise "v1.2.3" → "1.2.3" */
    private function clean_version( $tag ) {
        return ltrim( $tag, 'vV' );
    }

    /** Hook: inject update info into WordPress update transient */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $release = $this->get_release();
        if ( ! $release ) return $transient;

        $latest = $this->clean_version( $release->tag_name );
        if ( version_compare( $latest, $this->current_version, '>' ) ) {
            $zip_url = $this->get_zip_url( $release );
            if ( $zip_url ) {
                $transient->response[ $this->plugin_slug ] = (object) array(
                    'slug'        => dirname( $this->plugin_slug ),
                    'plugin'      => $this->plugin_slug,
                    'new_version' => $latest,
                    'url'         => "https://github.com/{$this->github_repo}",
                    'package'     => $zip_url,
                    'icons'       => array(),
                    'banners'     => array(),
                    'tested'      => get_bloginfo( 'version' ),
                    'requires_php'=> '7.4',
                );
            }
        }
        return $transient;
    }

    /** Hook: show plugin info popup in WordPress update screen */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) return $result;
        if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->plugin_slug ) ) return $result;

        $release = $this->get_release();
        if ( ! $release ) return $result;

        $latest = $this->clean_version( $release->tag_name );
        return (object) array(
            'name'          => 'Hochzeit Einladungen',
            'slug'          => dirname( $this->plugin_slug ),
            'version'       => $latest,
            'author'        => 'Tobias Hirche',
            'homepage'      => "https://github.com/{$this->github_repo}",
            'requires'      => '5.8',
            'requires_php'  => '7.4',
            'tested'        => get_bloginfo( 'version' ),
            'last_updated'  => $release->published_at ?? '',
            'sections'      => array(
                'description' => 'Verwaltung von Hochzeitseinladungen und Rückmeldungen für Alina &amp; Tobias.',
                'changelog'   => nl2br( esc_html( $release->body ?? '' ) ),
            ),
            'download_link' => $this->get_zip_url( $release ),
        );
    }

    /** Hook: rename extracted folder to match plugin slug after install */
    public function after_install( $response, $hook_extra, $result ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
            return $response;
        }

        global $wp_filesystem;
        $target = WP_PLUGIN_DIR . '/' . dirname( $this->plugin_slug );

        // Move extracted folder to correct plugin directory name
        if ( $result['destination'] !== $target ) {
            $wp_filesystem->move( $result['destination'], $target, true );
            $result['destination'] = $target;
        }

        // Reactivate plugin
        activate_plugin( $this->plugin_slug );

        // Clear cached release info so next check is fresh
        delete_transient( 'he_github_release_' . md5( $this->github_repo ) );

        return $result;
    }
}
