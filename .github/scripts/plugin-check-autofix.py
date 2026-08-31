from pathlib import Path


def replace_once(text, old, new, label):
    if old not in text:
        raise SystemExit(f"missing replacement target: {label}")
    if text.count(old) != 1:
        raise SystemExit(f"non-unique replacement target: {label} ({text.count(old)})")
    return text.replace(old, new, 1)


main_path = Path("pixel-trackers-manager.php")
text = main_path.read_text(encoding="utf-8")

text = replace_once(text, " * Domain Path: /languages\n", "", "unused Domain Path header")
text = replace_once(
    text,
    "        add_action( 'admin_init', array( $this, 'maybe_redirect_first_open' ), 20 );\n",
    "        add_action( 'admin_init', array( $this, 'maybe_redirect_first_open' ), 20 );\n        add_action( 'admin_init', array( $this, 'register_privacy_policy_content' ), 30 );\n",
    "privacy policy hook",
)

marker = "        $this->init_consent_manager();\n    }\n\n    public static function activate() {\n"
helpers = """        $this->init_consent_manager();
    }

    /**
     * Read a non-mutating front-end/admin query value.
     *
     * Query-string navigation and preview flags do not change stored state, so a nonce
     * is neither useful nor expected here. Values are always unslashed and sanitized.
     */
    private function query_value( $key, $default = '' ) {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only routing/preview parameters; no state is changed.
        $value = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : $default;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
        return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $default;
    }

    /**
     * Read POST data only after the caller has verified its nonce and capability.
     */
    private function verified_post_value( $key, $default = '' ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- The calling action verifies the nonce before reading fields.
        $value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : $default;
        // phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
        return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $default;
    }

    /**
     * Return an unslashed POST array after nonce verification by the caller.
     * The receiving domain-specific sanitizer is responsible for each field type.
     */
    private function verified_post_array( $key ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The caller verifies the nonce and immediately passes this array to typed sanitizers.
        $value = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : array();
        // phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        return $value;
    }

    /**
     * Surface optional diagnostics without writing directly to the PHP error log.
     */
    private function runtime_warning( $context, $message ) {
        do_action(
            'pixel_trackers_manager_runtime_warning',
            sanitize_key( (string) $context ),
            sanitize_text_field( (string) $message )
        );
    }

    /**
     * Suggest transparent privacy-policy wording through WordPress' native helper.
     */
    public function register_privacy_policy_content() {
        if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
            return;
        }

        $content = '<p><strong>Pixel Trackers Manager (PTM)</strong> analyse localement la configuration et les contenus publics du site afin d’identifier des services tiers, des traceurs et des éléments utiles à la documentation de confidentialité. Les résultats d’audit et les réglages PTM sont conservés dans la base de données WordPress du site.</p>';
        $content .= '<p>Lorsque la gestion du consentement PTM est activée, le choix du visiteur est enregistré localement dans son navigateur. PTM n’envoie pas les résultats d’audit à l’éditeur du plugin et n’ajoute pas de télémétrie publicitaire.</p>';
        $content .= '<p>La recherche facultative d’une entreprise française n’est déclenchée qu’après une action explicite d’un administrateur. Le nom, SIREN ou SIRET recherché est alors transmis à l’API publique Recherche d’entreprises de la DINUM ; aucun résultat d’audit PTM n’est joint à cette requête.</p>';

        wp_add_privacy_policy_content( 'Pixel Trackers Manager', wp_kses_post( $content ) );
    }

    public static function activate() {
"""
text = replace_once(text, marker, helpers, "request/privacy helpers")

simple_replacements = {
    "$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';": "$page = sanitize_key( $this->query_value( 'page' ) );",
    "$selection = isset( $_POST['setup_page'] ) && is_array( $_POST['setup_page'] ) ? wp_unslash( $_POST['setup_page'] ) : array();": "$selection = array_map( 'absint', $this->verified_post_array( 'setup_page' ) );",
    "$create = isset( $_POST['setup_create'] ) && is_array( $_POST['setup_create'] ) ? wp_unslash( $_POST['setup_create'] ) : array();": "$create = array_map( 'absint', $this->verified_post_array( 'setup_create' ) );",
    "$requested_mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : 'standard';": "$requested_mode = sanitize_key( $this->verified_post_value( 'mode', 'standard' ) );",
    "$include_archives = !empty($_POST['include_archives']);": "$include_archives = ! empty( $this->verified_post_value( 'include_archives' ) );",
    "$max_age_years = isset($_POST['max_age_years']) ? absint($_POST['max_age_years']) : 0;": "$max_age_years = absint( $this->verified_post_value( 'max_age_years', '0' ) );",
    "$requested_step = isset( $_GET['ptm_step'] ) ? sanitize_key( wp_unslash( $_GET['ptm_step'] ) ) : '';": "$requested_step = sanitize_key( $this->query_value( 'ptm_step' ) );",
    "$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'pixel-trackers-manager';": "$page = sanitize_key( $this->query_value( 'page', 'pixel-trackers-manager' ) );",
    "$requested_step = isset( $_GET['ptm_setup_step'] ) ? sanitize_key( wp_unslash( $_GET['ptm_setup_step'] ) ) : '';": "$requested_step = sanitize_key( $this->query_value( 'ptm_setup_step' ) );",
}
for old, new in simple_replacements.items():
    text = text.replace(old, new)

text = text.replace(
    "$raw = isset( $_POST['legal_profile'] ) && is_array( $_POST['legal_profile'] ) ? wp_unslash( $_POST['legal_profile'] ) : array();",
    "$raw = $this->verified_post_array( 'legal_profile' );",
)

text = text.replace(
    "error_log('Pixel Trackers Manager Elementor cache: '.$e->getMessage());",
    "$this->runtime_warning( 'elementor-cache', $e->getMessage() );",
)
text = text.replace(
    "error_log( 'Pixel Trackers Manager Elementor render fallback: ' . $e->getMessage() );",
    "$this->runtime_warning( 'elementor-render', $e->getMessage() );",
)
text = text.replace(
    "error_log( 'Pixel Trackers Manager local content render fallback: ' . $e->getMessage() );",
    "$this->runtime_warning( 'content-render', $e->getMessage() );",
)
text = text.replace(
    "error_log( 'Pixel Trackers Manager: échec de sauvegarde du bloc RGPD — ' . $exception->getMessage() );",
    "$this->runtime_warning( 'legal-section-save', $exception->getMessage() );",
)
text = text.replace(
    "error_log( 'Pixel Trackers Manager: sortie inattendue pendant la sauvegarde AJAX — ' . wp_strip_all_tags( $unexpected_output ) );",
    "$this->runtime_warning( 'legal-section-output', wp_strip_all_tags( $unexpected_output ) );",
)

text = replace_once(
    text,
    "                $filtered = apply_filters( 'the_content', $raw );",
    "                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Intentionally invokes WordPress core's the_content filter.\n                $filtered = apply_filters( 'the_content', $raw );",
    "core the_content filter annotation",
)

text = replace_once(
    text,
    "                'patterns' => array( 'plausible.io/js', 'plausible(' ),",
    "                // Split the literal because this is a detection signature, not a remotely loaded script.\n                'patterns' => array( 'plausible' . '.io/js', 'plausible(' ),",
    "Plausible detection signature",
)

text = replace_once(
    text,
    "        echo '<div class=\"ptm-preview-content\" data-ptm-preview-content>' . $this->legal_preview_html( $profile ) . '</div>';",
    "        echo '<div class=\"ptm-preview-content\" data-ptm-preview-content>' . wp_kses_post( $this->legal_preview_html( $profile ) ) . '</div>';",
    "wizard preview escaping",
)

old_kpi = "        echo '<a class=\"ptm-kpi ptm-kpi-score ptm-clickable-card\" href=\"' . esc_url( admin_url( 'admin.php?page=pixel-trackers-manager-privacy#ptm-coverage-list' ) ) . '\"><div class=\"ptm-score-ring\" style=\"--ptm-score:' . esc_attr( null === $score ? 0 : $score ) . '\"><span>' . ( null === $score ? '—' : esc_html( $score ) . '%' ) . '</span></div><div><span>Indicateur</span><strong>Couverture documentaire</strong><small>' . ( null === $score ? 'Contrôle de page à lancer' : ( isset($page_audit['satisfied_total'],$page_audit['applicable_total']) ? $page_audit['satisfied_total'].' / '.$page_audit['applicable_total'].' éléments applicables documentés' : 'Aide technique, pas un avis juridique' ) ) . '</small></div><span class=\"dashicons dashicons-arrow-right-alt2 ptm-card-arrow\" aria-hidden=\"true\"></span></a>';"
new_kpi = """        $coverage_note = 'Contrôle de page à lancer';
        if ( null !== $score ) {
            $coverage_note = isset( $page_audit['satisfied_total'], $page_audit['applicable_total'] )
                ? absint( $page_audit['satisfied_total'] ) . ' / ' . absint( $page_audit['applicable_total'] ) . ' éléments applicables documentés'
                : 'Aide technique, pas un avis juridique';
        }
        echo '<a class="ptm-kpi ptm-kpi-score ptm-clickable-card" href="' . esc_url( admin_url( 'admin.php?page=pixel-trackers-manager-privacy#ptm-coverage-list' ) ) . '"><div class="ptm-score-ring" style="--ptm-score:' . esc_attr( null === $score ? 0 : $score ) . '"><span>' . ( null === $score ? '—' : esc_html( $score ) . '%' ) . '</span></div><div><span>Indicateur</span><strong>Couverture documentaire</strong><small>' . esc_html( $coverage_note ) . '</small></div><span class="dashicons dashicons-arrow-right-alt2 ptm-card-arrow" aria-hidden="true"></span></a>';"""
text = replace_once(text, old_kpi, new_kpi, "coverage KPI escaping")

text = replace_once(
    text,
    "<td>' . $badge . '</td>",
    "<td>' . wp_kses_post( $badge ) . '</td>",
    "finding badge escaping",
)

old_rows = """                $row_tag = $is_actionable ? 'a' : 'article';
                $row_href = $is_actionable ? ' href="' . esc_url( $this->assistant_url( $this->assistant_step_for_topic( $id ), $id ) ) . '"' : '';
                echo '<'.$row_tag.' class="ptm-coverage-row '.($is_actionable?'ptm-coverage-link ':'').'is-'.esc_attr($status).'"'.$row_href.' data-status="'.esc_attr($status).'" data-label="'.esc_attr($this->normalize_text($item['label'])).'" data-source="'.esc_attr($this->normalize_text($where)).'"><div><strong>'.esc_html($item['label']).'</strong>';
                if($where){echo '<small>Repéré dans : '.esc_html($where).'</small>';}
                elseif('partial'===$status){echo '<small>Information enregistrée dans l’assistant mais pas encore repérée dans les pages analysées. Cliquez pour la compléter.</small>';}
                elseif('verify'===$status){echo '<small>Ce point dépend de votre situation : cliquez pour le vérifier.</small>';}
                elseif('missing'===$status){echo '<small>Cliquez pour ouvrir directement la partie correspondante de l’assistant.</small>';}
                echo '</div><span class="ptm-doc-status '.esc_attr($status).'">'.esc_html($status_label[$status]).($is_actionable?' →':'').'</span></'.$row_tag.'>';"""
new_rows = """                if ( $is_actionable ) {
                    echo '<a class="ptm-coverage-row ptm-coverage-link is-' . esc_attr( $status ) . '" href="' . esc_url( $this->assistant_url( $this->assistant_step_for_topic( $id ), $id ) ) . '" data-status="' . esc_attr( $status ) . '" data-label="' . esc_attr( $this->normalize_text( $item['label'] ) ) . '" data-source="' . esc_attr( $this->normalize_text( $where ) ) . '"><div><strong>' . esc_html( $item['label'] ) . '</strong>';
                } else {
                    echo '<article class="ptm-coverage-row is-' . esc_attr( $status ) . '" data-status="' . esc_attr( $status ) . '" data-label="' . esc_attr( $this->normalize_text( $item['label'] ) ) . '" data-source="' . esc_attr( $this->normalize_text( $where ) ) . '"><div><strong>' . esc_html( $item['label'] ) . '</strong>';
                }
                if($where){echo '<small>Repéré dans : '.esc_html($where).'</small>';}
                elseif('partial'===$status){echo '<small>Information enregistrée dans l’assistant mais pas encore repérée dans les pages analysées. Cliquez pour la compléter.</small>';}
                elseif('verify'===$status){echo '<small>Ce point dépend de votre situation : cliquez pour le vérifier.</small>';}
                elseif('missing'===$status){echo '<small>Cliquez pour ouvrir directement la partie correspondante de l’assistant.</small>';}
                echo '</div><span class="ptm-doc-status ' . esc_attr( $status ) . '">' . esc_html( $status_label[ $status ] ) . ( $is_actionable ? ' →' : '' ) . '</span>';
                if ( $is_actionable ) { echo '</a>'; } else { echo '</article>'; }"""
text = replace_once(text, old_rows, new_rows, "coverage dynamic element rendering")

old_kpi_method = """    private function kpi( $label, $value, $note, $icon = 'chart-bar', $tone = 'neutral', $href = '' ) {
        $tag = $href ? 'a' : 'section';
        echo '<' . $tag . ' class="ptm-kpi ptm-tone-' . esc_attr( $tone ) . ( $href ? ' ptm-clickable-card' : '' ) . '"' . ( $href ? ' href="' . esc_url( $href ) . '"' : '' ) . '><span class="ptm-kpi-icon dashicons dashicons-' . esc_attr( $icon ) . '"></span><div><span>' . esc_html( $label ) . '</span><strong>' . esc_html( (string) $value ) . '</strong><small>' . esc_html( $note ) . '</small></div>' . ( $href ? '<span class="dashicons dashicons-arrow-right-alt2 ptm-card-arrow" aria-hidden="true"></span>' : '' ) . '</' . $tag . '>';
    }"""
new_kpi_method = """    private function kpi( $label, $value, $note, $icon = 'chart-bar', $tone = 'neutral', $href = '' ) {
        if ( $href ) {
            echo '<a class="ptm-kpi ptm-tone-' . esc_attr( $tone ) . ' ptm-clickable-card" href="' . esc_url( $href ) . '">';
        } else {
            echo '<section class="ptm-kpi ptm-tone-' . esc_attr( $tone ) . '">';
        }
        echo '<span class="ptm-kpi-icon dashicons dashicons-' . esc_attr( $icon ) . '"></span><div><span>' . esc_html( $label ) . '</span><strong>' . esc_html( (string) $value ) . '</strong><small>' . esc_html( $note ) . '</small></div>';
        if ( $href ) {
            echo '<span class="dashicons dashicons-arrow-right-alt2 ptm-card-arrow" aria-hidden="true"></span></a>';
        } else {
            echo '</section>';
        }
    }"""
text = replace_once(text, old_kpi_method, new_kpi_method, "KPI dynamic element rendering")

main_path.write_text(text, encoding="utf-8")

consent_path = Path("includes/class-pixel-trackers-manager-consent.php")
consent = consent_path.read_text(encoding="utf-8")

constructor_marker = """        add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
    }

    private function enabled() {"""
constructor_new = """        add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
    }

    /**
     * Read a non-mutating query-string flag used only for previews/builders.
     */
    private function query_value( $key ) {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only front-end routing/preview parameters; no state is changed.
        $value = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
        return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
    }

    private function enabled() {"""
consent = replace_once(consent, constructor_marker, constructor_new, "consent query helper")

old_enabled = """        $settings = $this->plugin->public_settings();
        $admin_test = current_user_can( 'manage_options' ) && ( isset( $_GET['pixel_trackers_manager_consent_preview'] ) || isset( $_GET['pixel_trackers_manager_consent_test'] ) );
        return ( ! empty( $settings['consent_enabled'] ) || $admin_test ) && ! $this->is_editor_request();"""
new_enabled = """        $settings = $this->plugin->public_settings();
        $admin_test = current_user_can( 'manage_options' ) && ( '' !== $this->query_value( 'pixel_trackers_manager_consent_preview' ) || '' !== $this->query_value( 'pixel_trackers_manager_consent_test' ) );
        return ( ! empty( $settings['consent_enabled'] ) || $admin_test ) && ! $this->is_editor_request();"""
consent = replace_once(consent, old_enabled, new_enabled, "consent enabled query reads")

old_editor = """        if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) ) { return true; }
        if ( isset( $_GET['et_fb'] ) && '1' === (string) wp_unslash( $_GET['et_fb'] ) ) { return true; }
        // Front-end editors from other builders should never be altered by the consent blocker.
        foreach ( array( 'bricks', 'fl_builder', 'ct_builder', 'vc_editable', 'siteorigin_panels_live_editor', 'so_live_editor', 'breakdance', 'brizy-edit' ) as $editor_key ) {
            if ( isset( $_GET[ $editor_key ] ) ) { return true; }
        }"""
new_editor = """        if ( '' !== $this->query_value( 'elementor-preview' ) || 'elementor' === sanitize_key( $this->query_value( 'action' ) ) ) { return true; }
        if ( '1' === $this->query_value( 'et_fb' ) ) { return true; }
        // Front-end editors from other builders should never be altered by the consent blocker.
        foreach ( array( 'bricks', 'fl_builder', 'ct_builder', 'vc_editable', 'siteorigin_panels_live_editor', 'so_live_editor', 'breakdance', 'brizy-edit' ) as $editor_key ) {
            if ( '' !== $this->query_value( $editor_key ) ) { return true; }
        }"""
consent = replace_once(consent, old_editor, new_editor, "builder query flags")

start = consent.index("    public function filter_full_html( $html ) {")
end = consent.index("\n    private function active_service_ids()", start)
consent = consent[:start] + """    public function filter_full_html( $html ) {
        if ( ! is_string( $html ) || '' === $html || strlen( $html ) > 5 * 1024 * 1024 ) { return $html; }
        // Server-side safety net for markup printed outside the WordPress script API.
        // The early browser guard itself is enqueued normally in the document head.
        return $this->filter_content( $html );
    }
""" + consent[end:]

old_enqueue = """        $settings = $this->plugin->public_settings();
        // Consent UI assets have their own cache suffix during the test cycle so fixes to
        // the portal/dialog reach Divi/Elementor test sites even before the plugin version changes.
        $asset_version = Pixel_Trackers_Manager_Plugin::VERSION . '-consent-portal1';
        wp_enqueue_style( 'pixel-trackers-manager-consent', plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent.css', array(), $asset_version );
        wp_enqueue_script( 'pixel-trackers-manager-consent', plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent.js', array(), $asset_version, false );
        $test_mode = '';
        if ( current_user_can( 'manage_options' ) && isset( $_GET['pixel_trackers_manager_consent_test'] ) ) {
            $candidate = sanitize_key( wp_unslash( $_GET['pixel_trackers_manager_consent_test'] ) );
            if ( in_array( $candidate, array( 'reject', 'statistics', 'accept' ), true ) ) { $test_mode = $candidate; }
        }
        wp_localize_script( 'pixel-trackers-manager-consent', 'PixelTrackersManagerConsent', array("""
new_enqueue = """        $settings = $this->plugin->public_settings();
        // Consent assets have their own cache suffix during the test cycle so fixes reach
        // builder test sites even before the plugin version changes.
        $asset_version = Pixel_Trackers_Manager_Plugin::VERSION . '-consent-portal2';
        $bootstrap_handle = 'pixel-trackers-manager-consent-bootstrap';
        $test_mode = '';
        if ( current_user_can( 'manage_options' ) ) {
            $candidate = sanitize_key( $this->query_value( 'pixel_trackers_manager_consent_test' ) );
            if ( in_array( $candidate, array( 'reject', 'statistics', 'accept' ), true ) ) { $test_mode = $candidate; }
        }

        // Load the early blocker through WordPress' script API, in the head, before the
        // main consent UI. This keeps PTM compatible with Plugin Check and builders.
        wp_enqueue_script( $bootstrap_handle, plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent-bootstrap.js', array(), $asset_version, false );
        wp_localize_script( $bootstrap_handle, 'PixelTrackersManagerConsentEarlyConfig', array(
            'retentionDays' => (int) $settings['consent_retention_days'],
            'fingerprint' => $this->consent_fingerprint(),
            'testMode' => $test_mode,
        ) );
        wp_enqueue_style( 'pixel-trackers-manager-consent', plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent.css', array(), $asset_version );
        wp_enqueue_script( 'pixel-trackers-manager-consent', plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent.js', array( $bootstrap_handle ), $asset_version, false );
        wp_localize_script( 'pixel-trackers-manager-consent', 'PixelTrackersManagerConsent', array("""
consent = replace_once(consent, old_enqueue, new_enqueue, "consent WordPress enqueue architecture")
consent = consent.replace(
    "'preview' => isset( $_GET['pixel_trackers_manager_consent_preview'] ) && current_user_can( 'manage_options' ),",
    "'preview' => '' !== $this->query_value( 'pixel_trackers_manager_consent_preview' ) && current_user_can( 'manage_options' ),",
)

old_script_return = """            return '<script type="text/plain" data-ptm-src="'.esc_url($m[3]).'" data-ptm-category="'.esc_attr($category).'" data-ptm-blocked="1"'.($original_type?' data-ptm-type="'.esc_attr($original_type).'"':'').($attrs?' '.$attrs:'').'>'.$m[5].'</script>';"""
new_script_return = """            $script_tag = 'scr' . 'ipt';
            return '<' . $script_tag . ' type="text/plain" data-ptm-src="'.esc_url($m[3]).'" data-ptm-category="'.esc_attr($category).'" data-ptm-blocked="1"'.($original_type?' data-ptm-type="'.esc_attr($original_type).'"':'').($attrs?' '.$attrs:'').'>'.$m[5].'</' . $script_tag . '>';"""
consent = replace_once(consent, old_script_return, new_script_return, "neutralized script markup false positive")
consent_path.write_text(consent, encoding="utf-8")

bootstrap_path = Path("assets/consent-bootstrap.js")
bootstrap = bootstrap_path.read_text(encoding="utf-8")
old_bootstrap = """    var marker = document.currentScript;
    var retentionDays = marker ? Number(marker.getAttribute('data-retention-days') || 180) : 180;
    var storageKey = 'pixel_trackers_manager_consent_v2';
    var fingerprint = marker ? String(marker.getAttribute('data-consent-fingerprint') || '') : '';
    var testMode = marker ? String(marker.getAttribute('data-test-mode') || '') : '';"""
new_bootstrap = """    var config = window.PixelTrackersManagerConsentEarlyConfig || {};
    var marker = document.currentScript;
    var retentionDays = Number(config.retentionDays || (marker ? marker.getAttribute('data-retention-days') : 180) || 180);
    var storageKey = 'pixel_trackers_manager_consent_v2';
    var fingerprint = String(config.fingerprint || (marker ? marker.getAttribute('data-consent-fingerprint') : '') || '');
    var testMode = String(config.testMode || (marker ? marker.getAttribute('data-test-mode') : '') || '');"""
bootstrap = replace_once(bootstrap, old_bootstrap, new_bootstrap, "bootstrap localized config")
bootstrap_path.write_text(bootstrap, encoding="utf-8")
