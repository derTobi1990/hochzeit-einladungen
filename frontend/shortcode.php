<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HE_Shortcode {

    public static function init() {
        add_shortcode( 'hochzeit_rueckmeldung', array( __CLASS__, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_he_rueckmeldung',        array( __CLASS__, 'handle_ajax' ) );
        add_action( 'wp_ajax_nopriv_he_rueckmeldung', array( __CLASS__, 'handle_ajax' ) );

        // Avada / Fusion Builder compatibility
        add_filter( 'fusion_builder_shortcode_output', 'do_shortcode', 11 );
        add_filter( 'the_content',                     'do_shortcode', 11 );
        add_filter( 'widget_text',                     'do_shortcode', 11 );
    }

    public static function enqueue_assets() {
        wp_enqueue_style( 'he-frontend', HE_PLUGIN_URL . 'assets/css/frontend.css', array(), HE_VERSION );
        wp_enqueue_script( 'he-frontend', HE_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery' ), HE_VERSION, true );
        wp_localize_script( 'he-frontend', 'HE', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'he_frontend_nonce' ),
        ) );
    }

    public static function render( $atts ) {
        $s = HE_Settings::get();
        ob_start();
        echo HE_Settings::inline_css();
        ?>
        <div class="he-form-wrap" id="he-rsvp-wrap">
            <div class="he-form-inner">
                <h2 class="he-form-title"><?php echo esc_html( $s['form_title'] ); ?></h2>
                <p class="he-form-intro"><?php echo esc_html( $s['form_intro'] ); ?></p>
                <form id="he-rsvp-form" novalidate>
                    <div class="he-field">
                        <label for="he-name"><?php echo esc_html( $s['label_name'] ); ?> *</label>
                        <input type="text" id="he-name" name="name" placeholder="<?php echo esc_attr( $s['placeholder_name'] ); ?>" required>
                    </div>
                    <div class="he-field he-field--radio">
                        <label><?php echo esc_html( $s['label_status'] ); ?></label>
                        <div class="he-radio-group">
                            <label class="he-radio-label he-radio--yes">
                                <input type="radio" name="status" value="kommt" checked>
                                <span class="he-radio-btn"><?php echo esc_html( $s['text_kommt'] ); ?></span>
                            </label>
                            <label class="he-radio-label he-radio--no">
                                <input type="radio" name="status" value="kommt_nicht">
                                <span class="he-radio-btn"><?php echo esc_html( $s['text_kommt_nicht'] ); ?></span>
                            </label>
                        </div>
                    </div>
                    <div class="he-field" id="he-personen-field">
                        <label for="he-personen"><?php echo esc_html( $s['label_personen'] ); ?> *</label>
                        <input type="number" id="he-personen" name="personen" min="1" max="20" value="1">
                    </div>
                    <div class="he-field">
                        <label for="he-anmerkung"><?php echo esc_html( $s['label_anmerkung'] ); ?></label>
                        <textarea id="he-anmerkung" name="anmerkung" rows="3" placeholder="<?php echo esc_attr( $s['placeholder_anm'] ); ?>"></textarea>
                    </div>
                    <div class="he-notice he-notice--error" id="he-error" style="display:none"></div>
                    <button type="submit" class="he-submit" id="he-submit">
                        <span class="he-submit-text"><?php echo esc_html( $s['btn_submit'] ); ?></span>
                        <span class="he-submit-loading" style="display:none"><?php echo esc_html( $s['btn_loading'] ); ?></span>
                    </button>
                </form>
                <div class="he-success" id="he-success" style="display:none">
                    <div class="he-success-icon"><?php echo esc_html( $s['success_icon'] ); ?></div>
                    <h3><?php echo esc_html( $s['success_title'] ); ?></h3>
                    <p id="he-success-msg"></p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function handle_ajax() {
        check_ajax_referer( 'he_frontend_nonce', 'nonce' );

        $name      = isset( $_POST['name'] )     ? sanitize_text_field( $_POST['name'] )          : '';
        $status    = isset( $_POST['status'] )   ? sanitize_text_field( $_POST['status'] )        : 'kommt';
        $personen  = isset( $_POST['personen'] ) ? absint( $_POST['personen'] )                   : 1;
        $anmerkung = isset( $_POST['anmerkung']) ? sanitize_textarea_field( $_POST['anmerkung'] ) : '';

        if ( ! in_array( $status, array( 'kommt', 'kommt_nicht' ) ) ) $status = 'kommt';

        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => 'Bitte gib deinen Namen ein.' ) );
        }
        // Only validate Personenzahl when actually coming
        if ( $status === 'kommt' && ( $personen < 1 || $personen > 20 ) ) {
            wp_send_json_error( array( 'message' => 'Bitte gib eine gültige Personenanzahl ein (1–20).' ) );
        }
        if ( $status === 'kommt_nicht' ) $personen = 0;

        $matches      = HE_Einladung::find_by_name( $name );
        $einladung_id = count( $matches ) === 1
            ? $matches[0]->id
            : HE_Einladung::create( array( 'name' => $name, 'adresse' => '(über Website)', 'personen' => $personen, 'quelle' => 'website' ) );

        HE_Rueckmeldung::create( array(
            'einladung_id' => $einladung_id,
            'name'         => $name,
            'personen'     => $personen,
            'status'       => $status,
            'anmerkung'    => $anmerkung,
        ) );

        $s   = HE_Settings::get();
        $msg = $status === 'kommt' ? $s['msg_kommt'] : $s['msg_kommt_nicht'];
        wp_send_json_success( array( 'message' => $msg ) );
    }
}

HE_Shortcode::init();
