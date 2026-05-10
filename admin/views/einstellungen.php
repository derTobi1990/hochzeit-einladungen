<?php if ( ! defined( 'ABSPATH' ) ) exit;
$s   = HE_Settings::get();
$msg = isset( $_GET['msg'] ) ? $_GET['msg'] : '';
?>
<div class="wrap he-wrap">
    <h1>Einstellungen</h1>

    <?php if ( $msg === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p>✔ Einstellungen gespeichert.</p></div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field( 'he_nonce', 'he_nonce_field' ); ?>
        <input type="hidden" name="he_action" value="save_settings">

        <!-- TEXTE -->
        <div class="he-card">
            <h2>📝 Formulartexte</h2>
            <table class="form-table">
                <?php
                $text_fields = array(
                    'form_title'       => 'Überschrift',
                    'form_intro'       => 'Einleitungstext',
                    'label_name'       => 'Label: Name',
                    'placeholder_name' => 'Platzhalter: Name',
                    'label_status'     => 'Label: Statusfrage',
                    'text_kommt'       => 'Button-Text: Kommt',
                    'text_kommt_nicht' => 'Button-Text: Kommt nicht',
                    'label_personen'   => 'Label: Personenzahl',
                    'label_anmerkung'  => 'Label: Anmerkungen',
                    'placeholder_anm'  => 'Platzhalter: Anmerkungen',
                    'btn_submit'       => 'Absende-Button Text',
                    'btn_loading'      => 'Lade-Text beim Senden',
                );
                foreach ( $text_fields as $key => $label ) : ?>
                <tr>
                    <th><label for="<?php echo $key; ?>"><?php echo esc_html( $label ); ?></label></th>
                    <td><input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>"
                        class="regular-text" value="<?php echo esc_attr( $s[ $key ] ); ?>"></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- ERFOLGSMELDUNGEN -->
        <div class="he-card">
            <h2>🎉 Erfolgsmeldungen (nach dem Absenden)</h2>
            <table class="form-table">
                <tr>
                    <th><label for="success_icon">Icon / Emoji</label></th>
                    <td><input type="text" id="success_icon" name="success_icon"
                        class="small-text" value="<?php echo esc_attr( $s['success_icon'] ); ?>"></td>
                </tr>
                <tr>
                    <th><label for="success_title">Überschrift</label></th>
                    <td><input type="text" id="success_title" name="success_title"
                        class="regular-text" value="<?php echo esc_attr( $s['success_title'] ); ?>"></td>
                </tr>
                <tr>
                    <th><label for="msg_kommt">Text bei Zusage</label></th>
                    <td><input type="text" id="msg_kommt" name="msg_kommt"
                        class="large-text" value="<?php echo esc_attr( $s['msg_kommt'] ); ?>"></td>
                </tr>
                <tr>
                    <th><label for="msg_kommt_nicht">Text bei Absage</label></th>
                    <td><input type="text" id="msg_kommt_nicht" name="msg_kommt_nicht"
                        class="large-text" value="<?php echo esc_attr( $s['msg_kommt_nicht'] ); ?>"></td>
                </tr>
            </table>
        </div>

        <!-- FARBEN -->
        <div class="he-card">
            <h2>🎨 Farben (Frontend-Formular)</h2>
            <table class="form-table">
                <?php
                $color_fields = array(
                    'color_bg'           => 'Hintergrundfarbe',
                    'color_accent'       => 'Akzentfarbe (Gold)',
                    'color_text'         => 'Textfarbe',
                    'color_muted'        => 'Gedämpfte Textfarbe',
                    'color_input_border' => 'Rahmenfarbe Eingabefelder',
                    'color_btn_bg'       => 'Button Hintergrund',
                    'color_btn_text'     => 'Button Textfarbe',
                );
                foreach ( $color_fields as $key => $label ) : ?>
                <tr>
                    <th><label for="<?php echo $key; ?>"><?php echo esc_html( $label ); ?></label></th>
                    <td>
                        <input type="color" id="<?php echo $key; ?>" name="<?php echo $key; ?>"
                            value="<?php echo esc_attr( $s[ $key ] ); ?>">
                        <input type="text" name="<?php echo $key; ?>_hex" readonly
                            value="<?php echo esc_attr( $s[ $key ] ); ?>"
                            class="small-text he-color-hex"
                            style="margin-left:8px;font-family:monospace">
                        <script>
                        document.getElementById('<?php echo $key; ?>').addEventListener('input', function() {
                            this.nextElementSibling.value = this.value;
                        });
                        </script>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p class="description">Die Farbvorschau siehst du sofort auf der Frontend-Seite nach dem Speichern.</p>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary button-large">💾 Einstellungen speichern</button>
        </p>
    </form>
</div>
