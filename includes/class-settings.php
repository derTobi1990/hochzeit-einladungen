<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HE_Settings {

    const OPTION = 'he_settings';

    public static function defaults() {
        return array(
            // Texte
            'form_title'        => 'Rückmeldung geben',
            'form_intro'        => 'Wir freuen uns auf deine Antwort! Bitte gib deinen Namen und die Anzahl der Personen an.',
            'label_name'        => 'Dein Name / Familie',
            'placeholder_name'  => 'z.B. Familie Müller',
            'label_status'      => 'Wir / Ich …',
            'text_kommt'        => '💚 Wir kommen!',
            'text_kommt_nicht'  => '😢 Wir können leider nicht',
            'label_personen'    => 'Anzahl Personen',
            'label_anmerkung'   => 'Anmerkungen (optional)',
            'placeholder_anm'   => 'Allergien, besondere Wünsche, …',
            'btn_submit'        => 'Rückmeldung senden',
            'btn_loading'       => 'Wird gesendet…',
            'success_title'     => 'Vielen Dank!',
            'success_icon'      => '🎉',
            'msg_kommt'         => 'Wir freuen uns darauf, dich / euch am 5. September bei uns feiern zu dürfen! 🥂',
            'msg_kommt_nicht'   => 'Schade, dass es nicht klappt – wir denken trotzdem an dich / euch! 💛',
            // Farben
            'color_bg'          => '#1b3a3a',
            'color_accent'      => '#c9a94d',
            'color_text'        => '#f0e8d4',
            'color_muted'       => '#a8c0b0',
            'color_input_border'=> '#c9a94d',
            'color_btn_bg'      => '#c9a94d',
            'color_btn_text'    => '#1b2e2e',
        );
    }

    public static function get( $key = null ) {
        $saved    = get_option( self::OPTION, array() );
        $settings = wp_parse_args( $saved, self::defaults() );
        if ( $key !== null ) {
            return isset( $settings[ $key ] ) ? $settings[ $key ] : '';
        }
        return $settings;
    }

    public static function save( $data ) {
        $defaults = self::defaults();
        $clean    = array();
        foreach ( $defaults as $key => $default ) {
            if ( isset( $data[ $key ] ) ) {
                $clean[ $key ] = strpos( $key, 'color_' ) === 0
                    ? sanitize_hex_color( $data[ $key ] ) ?: $default
                    : sanitize_text_field( $data[ $key ] );
            } else {
                $clean[ $key ] = $default;
            }
        }
        update_option( self::OPTION, $clean );
    }

    /** Output inline CSS with current color variables */
    public static function inline_css() {
        $s = self::get();
        return "
        <style>
        .he-form-inner {
            background: {$s['color_bg']} !important;
        }
        .he-form-title,
        .he-field label,
        .he-field > label {
            color: {$s['color_accent']} !important;
        }
        .he-form-intro,
        .he-success p {
            color: {$s['color_muted']} !important;
        }
        .he-form-inner,
        .he-field input[type='text'],
        .he-field input[type='number'],
        .he-field textarea {
            color: {$s['color_text']} !important;
        }
        .he-field input[type='text'],
        .he-field input[type='number'],
        .he-field textarea {
            border-color: " . he_hex_to_rgba( $s['color_input_border'], 0.3 ) . " !important;
        }
        .he-field input[type='text']:focus,
        .he-field input[type='number']:focus,
        .he-field textarea:focus {
            border-color: {$s['color_input_border']} !important;
        }
        .he-radio-btn {
            border-color: " . he_hex_to_rgba( $s['color_accent'], 0.3 ) . ";
            color: {$s['color_muted']};
        }
        .he-radio-label input[type='radio']:checked + .he-radio-btn {
            border-color: {$s['color_accent']} !important;
            color: {$s['color_accent']} !important;
            background: " . he_hex_to_rgba( $s['color_accent'], 0.12 ) . " !important;
        }
        .he-submit {
            background: {$s['color_btn_bg']} !important;
            color: {$s['color_btn_text']} !important;
        }
        .he-success h3 { color: {$s['color_accent']} !important; }
        </style>";
    }
}

/** Helper: hex → rgba() string */
function he_hex_to_rgba( $hex, $alpha = 1 ) {
    $hex = ltrim( $hex, '#' );
    if ( strlen( $hex ) === 3 ) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );
    return "rgba({$r},{$g},{$b},{$alpha})";
}
