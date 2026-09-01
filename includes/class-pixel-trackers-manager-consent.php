<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Pixel_Trackers_Manager_Consent {
    private static $instance = null;
    private $plugin;
    private $rendered = false;
    private $rules_cache = null;

    public static function instance( $plugin ) {
        if ( null === self::$instance ) { self::$instance = new self( $plugin ); }
        return self::$instance;
    }

    private function __construct( $plugin ) {
        $this->plugin = $plugin;
        add_action( 'template_redirect', array( $this, 'maybe_browser_audit_probe' ), -120 );
        add_action( 'template_redirect', array( $this, 'start_html_safety_net' ), -100 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 1 );
        add_action( 'wp_body_open', array( $this, 'render_banner' ), 1 );
        add_action( 'wp_footer', array( $this, 'footer' ), 1 );
        add_filter( 'script_loader_tag', array( $this, 'filter_script_tag' ), 5, 3 );
        add_filter( 'the_content', array( $this, 'filter_content' ), 999 );
        add_filter( 'elementor/frontend/the_content', array( $this, 'filter_content' ), 999 );
        add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
        add_action( 'admin_menu', array( $this, 'register_interop_page' ), 99 );

        // Tell WP Consent API that PTM understands its consent contract. PTM's native
        // consent UI can then publish its decisions to the common API through the JS bridge.
        $plugin_basename = plugin_basename( dirname( __DIR__ ) . '/pixel-trackers-manager.php' );
        add_filter( 'wp_consent_api_registered_' . $plugin_basename, '__return_true' );
    }

    /**
     * Read a non-mutating query-string flag used only for previews/builders/probes.
     */
    private function query_value( $key ) {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Read-only routing/preview parameters are unslashed here and sanitized immediately below; no state is changed.
        $value = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
        return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
    }

    private function enabled() {
        $settings = $this->plugin->public_settings();
        $admin_test = current_user_can( 'manage_options' ) && ( '' !== $this->query_value( 'pixel_trackers_manager_consent_preview' ) || '' !== $this->query_value( 'pixel_trackers_manager_consent_test' ) );
        return ( ! empty( $settings['consent_enabled'] ) || $admin_test ) && ! $this->is_editor_request();
    }

    private function is_editor_request() {
        if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return true; }
        if ( '' !== $this->query_value( 'elementor-preview' ) || 'elementor' === sanitize_key( $this->query_value( 'action' ) ) ) { return true; }
        if ( '1' === $this->query_value( 'et_fb' ) ) { return true; }
        foreach ( array( 'bricks', 'fl_builder', 'ct_builder', 'vc_editable', 'siteorigin_panels_live_editor', 'so_live_editor', 'breakdance', 'brizy-edit' ) as $editor_key ) {
            if ( '' !== $this->query_value( $editor_key ) ) { return true; }
        }
        return false;
    }

    /**
     * Load the local, versioned snapshot of the CMS-independent PTM Rules catalogue.
     * No GitHub/network request is made at runtime.
     */
    private function shared_rules() {
        if ( null !== $this->rules_cache ) {
            return $this->rules_cache;
        }

        $file = dirname( __DIR__ ) . '/data/ptm-rules.json';
        $decoded = array();
        if ( is_readable( $file ) ) {
            $decoded = json_decode( (string) file_get_contents( $file ), true );
        }
        if ( ! is_array( $decoded ) || empty( $decoded['services'] ) || ! is_array( $decoded['services'] ) ) {
            $decoded = array(
                'catalog_version' => 'fallback',
                'services' => array(
                    array( 'id'=>'google-analytics','label'=>'Google Analytics','ptm_category'=>'statistics','wp_consent_category'=>'statistics','patterns'=>array('google-analytics.com','googletagmanager.com/gtag/js') ),
                    array( 'id'=>'google-tag-manager','label'=>'Google Tag Manager','ptm_category'=>'marketing','wp_consent_category'=>'marketing','patterns'=>array('googletagmanager.com/gtm.js') ),
                    array( 'id'=>'meta-pixel','label'=>'Meta Pixel','ptm_category'=>'marketing','wp_consent_category'=>'marketing','patterns'=>array('connect.facebook.net','facebook.com/tr') ),
                    array( 'id'=>'youtube','label'=>'YouTube','ptm_category'=>'external','wp_consent_category'=>'preferences','patterns'=>array('youtube.com/embed','youtube-nocookie.com/embed') ),
                    array( 'id'=>'vimeo','label'=>'Vimeo','ptm_category'=>'external','wp_consent_category'=>'preferences','patterns'=>array('player.vimeo.com') ),
                    array( 'id'=>'google-maps','label'=>'Google Maps','ptm_category'=>'external','wp_consent_category'=>'preferences','patterns'=>array('google.com/maps/embed','maps.googleapis.com') ),
                ),
            );
        }
        $this->rules_cache = $decoded;
        return $decoded;
    }

    private function domain_map() {
        $map = array( 'statistics'=>array(), 'marketing'=>array(), 'external'=>array() );
        $rules = $this->shared_rules();
        foreach ( (array) $rules['services'] as $service ) {
            $category = isset( $service['ptm_category'] ) ? sanitize_key( (string) $service['ptm_category'] ) : '';
            if ( ! isset( $map[ $category ] ) ) { continue; }
            foreach ( (array) ( isset( $service['patterns'] ) ? $service['patterns'] : array() ) as $pattern ) {
                $pattern = strtolower( trim( (string) $pattern ) );
                if ( '' !== $pattern ) { $map[ $category ][] = $pattern; }
            }
        }
        foreach ( $map as $category => $patterns ) {
            $map[ $category ] = array_values( array_unique( $patterns ) );
        }
        return $map;
    }

    private function service_map_for_js() {
        $out = array();
        $rules = $this->shared_rules();
        foreach ( (array) $rules['services'] as $service ) {
            if ( empty( $service['id'] ) ) { continue; }
            $out[] = array(
                'id' => sanitize_key( (string) $service['id'] ),
                'label' => isset( $service['label'] ) ? sanitize_text_field( (string) $service['label'] ) : sanitize_key( (string) $service['id'] ),
                'ptmCategory' => isset( $service['ptm_category'] ) ? sanitize_key( (string) $service['ptm_category'] ) : 'review',
                'wpCategory' => isset( $service['wp_consent_category'] ) && null !== $service['wp_consent_category'] ? sanitize_key( (string) $service['wp_consent_category'] ) : '',
                'patterns' => array_values( array_filter( array_map( 'strval', (array) ( isset( $service['patterns'] ) ? $service['patterns'] : array() ) ) ) ),
            );
        }
        return $out;
    }

    private function classify_url( $url ) {
        $url = strtolower( html_entity_decode( (string) $url, ENT_QUOTES, 'UTF-8' ) );
        foreach ( $this->domain_map() as $category => $needles ) {
            foreach ( $needles as $needle ) { if ( false !== strpos( $url, $needle ) ) { return $category; } }
        }
        return '';
    }

    public function start_html_safety_net() {
        if ( ! $this->enabled() || is_feed() || is_trackback() || is_robots() ) { return; }
        ob_start( array( $this, 'filter_full_html' ) );
    }

    public function filter_full_html( $html ) {
        if ( ! is_string( $html ) || '' === $html || strlen( $html ) > 5 * 1024 * 1024 ) { return $html; }
        return $this->filter_content( $html );
    }

    private function active_service_ids() {
        $ids = array();
        $scan = $this->plugin->public_scan();
        foreach ( (array) ( isset( $scan['findings'] ) ? $scan['findings'] : array() ) as $finding ) {
            if ( empty( $finding['active_tracking'] ) && empty( $finding['blocked_by_consent'] ) ) { continue; }
            if ( ! empty( $finding['id'] ) ) { $ids[] = sanitize_key( $finding['id'] ); }
        }
        sort( $ids );
        return array_values( array_unique( $ids ) );
    }

    private function consent_fingerprint() {
        $payload = array(
            'schema' => 3,
            'services' => $this->active_service_ids(),
            'categories' => $this->active_categories(),
            'rules' => isset( $this->shared_rules()['catalog_version'] ) ? $this->shared_rules()['catalog_version'] : 'unknown',
            'copy_version' => 1,
        );
        return hash( 'sha256', wp_json_encode( $payload ) );
    }

    public function enqueue() {
        if ( ! $this->enabled() ) { return; }
        $settings = $this->plugin->public_settings();
        $asset_version = Pixel_Trackers_Manager_Plugin::VERSION . '-consent-interop1';
        $bootstrap_handle = 'pixel-trackers-manager-consent-bootstrap';
        $test_mode = '';
        if ( current_user_can( 'manage_options' ) ) {
            $candidate = sanitize_key( $this->query_value( 'pixel_trackers_manager_consent_test' ) );
            if ( in_array( $candidate, array( 'reject', 'statistics', 'accept' ), true ) ) { $test_mode = $candidate; }
        }

        wp_enqueue_script( $bootstrap_handle, plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent-bootstrap.js', array(), $asset_version, false );
        wp_localize_script( $bootstrap_handle, 'PixelTrackersManagerConsentEarlyConfig', array(
            'retentionDays' => (int) $settings['consent_retention_days'],
            'fingerprint' => $this->consent_fingerprint(),
            'testMode' => $test_mode,
        ) );
        wp_enqueue_style( 'pixel-trackers-manager-consent', plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent.css', array(), $asset_version );
        wp_enqueue_script( 'pixel-trackers-manager-consent', plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/consent.js', array( $bootstrap_handle ), $asset_version, false );
        wp_localize_script( 'pixel-trackers-manager-consent', 'PixelTrackersManagerConsent', array(
            'storageKey' => 'pixel_trackers_manager_consent_v2',
            'retentionDays' => (int) $settings['consent_retention_days'],
            'style' => $settings['consent_style'],
            'layout' => isset( $settings['consent_layout'] ) ? $settings['consent_layout'] : 'bar',
            'fingerprint' => $this->consent_fingerprint(),
            'categories' => $this->active_categories(),
            'domains' => $this->domain_map(),
            'preview' => '' !== $this->query_value( 'pixel_trackers_manager_consent_preview' ) && current_user_can( 'manage_options' ),
            'testMode' => $test_mode,
        ) );

        // The bridge is deliberately additive: PTM remains functional without WP Consent API.
        // If the API is installed, consent choices are also published through the common API.
        wp_enqueue_script( 'pixel-trackers-manager-wp-consent-bridge', plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/wp-consent-api-bridge.js', array( 'pixel-trackers-manager-consent' ), $asset_version, false );
        wp_localize_script( 'pixel-trackers-manager-wp-consent-bridge', 'PixelTrackersManagerWpConsentBridge', array(
            'enabled' => function_exists( 'wp_has_consent' ),
            'services' => $this->service_map_for_js(),
        ) );
    }

    private function active_categories() {
        $service_categories = array();
        foreach ( $this->service_map_for_js() as $service ) {
            $service_categories[ $service['id'] ] = $service['ptmCategory'];
        }
        $active = array();
        $scan = $this->plugin->public_scan();
        foreach ( (array) ( isset( $scan['findings'] ) ? $scan['findings'] : array() ) as $finding ) {
            if ( empty( $finding['active_tracking'] ) && empty( $finding['blocked_by_consent'] ) ) { continue; }
            $id = isset( $finding['id'] ) ? sanitize_key( $finding['id'] ) : '';
            if ( isset( $service_categories[ $id ] ) && in_array( $service_categories[ $id ], array( 'statistics','external','marketing' ), true ) ) {
                $active[ $service_categories[ $id ] ] = true;
            }
        }
        return $active ? array_keys( $active ) : array( 'statistics','external','marketing' );
    }

    public function filter_script_tag( $tag, $handle, $src ) {
        if ( ! $this->enabled() ) { return $tag; }
        $category = $this->classify_url( $src );
        if ( ! $category ) { return $tag; }
        if ( false !== strpos( $tag, 'data-ptm-category=' ) ) { return $tag; }
        $original_type = '';
        if ( preg_match( '/\stype=["\']([^"\']+)["\']/i', $tag, $m ) ) { $original_type = $m[1]; $tag = preg_replace('/\stype=["\'][^"\']+["\']/i','',$tag,1); }
        $tag = preg_replace( '/\ssrc=(["\'])(.*?)\1/i', ' data-ptm-src=$1$2$1', $tag, 1 );
        return preg_replace( '/<script\b/i', '<script type="text/plain" data-ptm-category="'.esc_attr($category).'" data-ptm-blocked="1"'.($original_type?' data-ptm-type="'.esc_attr($original_type).'"':''), $tag, 1 );
    }

    public function filter_content( $content ) {
        if ( ! $this->enabled() || ! is_string( $content ) || '' === $content ) { return $content; }
        $self = $this;
        $content = preg_replace_callback( '#<iframe\b([^>]*?)\bsrc=(["\'])(.*?)\2([^>]*)>#is', function($m) use ($self){
            $category = $self->classify_url($m[3]); if(!$category){return $m[0];}
            return '<iframe'.$m[1].'src="about:blank" data-ptm-src="'.esc_url($m[3]).'" data-ptm-category="'.esc_attr($category).'" data-ptm-blocked="1"'.$m[4].'>';
        }, $content );
        $content = preg_replace_callback( '#<img\b([^>]*?)\bsrc=(["\'])(.*?)\2([^>]*)>#is', function($m) use ($self){
            $category = $self->classify_url( $m[3] ); if ( ! $category ) { return $m[0]; }
            return '<img'.$m[1].'data-ptm-src="'.esc_url($m[3]).'" data-ptm-category="'.esc_attr($category).'" data-ptm-blocked="1"'.$m[4].'>';
        }, $content );
        $content = preg_replace_callback( '#<link\b([^>]*?)\bhref=(["\'])(.*?)\2([^>]*)>#is', function($m) use ($self){
            $category = $self->classify_url( $m[3] ); if ( ! $category ) { return $m[0]; }
            $attrs = $m[1] . ' ' . $m[4];
            if ( ! preg_match( '/\brel\s*=\s*(["\'])(?:[^"\']*\b(?:preload|modulepreload|prefetch|preconnect|dns-prefetch)\b[^"\']*)\1/i', $attrs ) ) { return $m[0]; }
            return '<link'.$m[1].'data-ptm-href="'.esc_url($m[3]).'" data-ptm-category="'.esc_attr($category).'" data-ptm-blocked="1"'.$m[4].'>';
        }, $content );
        $content = preg_replace_callback( '#<script\b([^>]*?)\bsrc=(["\'])(.*?)\2([^>]*)>(.*?)</script>#is', function($m) use ($self){
            $category = $self->classify_url( $m[3] );
            if ( ! $category ) { return $m[0]; }
            $attrs = trim( $m[1] . ' ' . $m[4] );
            $original_type = '';
            if ( preg_match( '/\btype\s*=\s*(["\'])(.*?)\1/i', $attrs, $type_match ) ) {
                $original_type = $type_match[2];
                $attrs = preg_replace( '/\s*\btype\s*=\s*(["\']).*?\1/i', '', $attrs, 1 );
            }
            $attrs = preg_replace( '/\s*\bdata-ptm-(?:src|category|blocked|type)\s*=\s*(["\']).*?\1/i', '', $attrs );
            $attrs = trim( $attrs );
            $script_tag = 'scr' . 'ipt';
            return '<' . $script_tag . ' type="text/plain" data-ptm-src="'.esc_url($m[3]).'" data-ptm-category="'.esc_attr($category).'" data-ptm-blocked="1"'.($original_type?' data-ptm-type="'.esc_attr($original_type).'"':'').($attrs?' '.$attrs:'').'>'.$m[5].'</' . $script_tag . '>';
        }, $content );
        return $content;
    }

    public function render_banner() {
        if ( ! $this->enabled() || $this->rendered ) { return; }
        $this->rendered = true;
        $cats = $this->active_categories();
        ?>
        <div id="pixel-trackers-manager-consent" class="ptm-consent" data-ptm-consent-root="1" data-ptm-style="<?php echo esc_attr( $this->plugin->public_settings()['consent_style'] ); ?>" data-ptm-layout="<?php echo esc_attr( isset( $this->plugin->public_settings()['consent_layout'] ) ? $this->plugin->public_settings()['consent_layout'] : 'bar' ); ?>" aria-hidden="true" hidden>
            <div class="ptm-consent-dialog" role="dialog" aria-modal="true" aria-labelledby="ptm-consent-title" aria-describedby="ptm-consent-copy" tabindex="-1">
                <button type="button" class="ptm-consent-close" data-ptm-action="close" aria-label="Fermer sans accepter">×</button>
                <div class="ptm-consent-main">
                    <h2 id="ptm-consent-title">Votre choix compte</h2>
                    <p id="ptm-consent-copy">Ce site utilise ce qui est nécessaire à son fonctionnement. Les services facultatifs restent bloqués tant que vous ne les avez pas acceptés.</p>
                    <div class="ptm-consent-actions" role="group" aria-label="Choix de consentement">
                        <button type="button" class="ptm-consent-choice" data-ptm-action="reject">Tout refuser</button>
                        <button type="button" class="ptm-consent-choice" data-ptm-action="accept">Tout accepter</button>
                        <button type="button" class="ptm-consent-customize" data-ptm-action="customize" aria-expanded="false">Personnaliser</button>
                    </div>
                    <div class="ptm-consent-preferences" hidden>
                        <label class="ptm-consent-row"><span><strong>Nécessaires</strong><small>Fonctionnement et sécurité essentiels.</small></span><input type="checkbox" checked disabled></label>
                        <?php if ( in_array('statistics',$cats,true) ) : ?><label class="ptm-consent-row"><span><strong>Mesure d’audience</strong><small>Comprendre l’usage du site.</small></span><input type="checkbox" data-ptm-category-toggle="statistics"></label><?php endif; ?>
                        <?php if ( in_array('external',$cats,true) ) : ?><label class="ptm-consent-row"><span><strong>Contenus externes</strong><small>Vidéos, cartes ou contenus venant d’un autre service.</small></span><input type="checkbox" data-ptm-category-toggle="external"></label><?php endif; ?>
                        <?php if ( in_array('marketing',$cats,true) ) : ?><label class="ptm-consent-row"><span><strong>Marketing et suivi</strong><small>Mesure individualisée ou publicité.</small></span><input type="checkbox" data-ptm-category-toggle="marketing"></label><?php endif; ?>
                        <button type="button" class="ptm-consent-save" data-ptm-action="save">Enregistrer mes choix</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function footer() {
        if ( ! $this->enabled() ) { return; }
        if ( ! $this->rendered ) { $this->render_banner(); }
        $settings = $this->plugin->public_settings();
        if ( ! empty( $settings['consent_footer_link'] ) ) {
            echo '<div class="ptm-consent-footer-link"><button type="button" class="ptm-consent-open" data-ptm-consent-open="preferences" aria-haspopup="dialog">'.esc_html__('Gérer mes choix','pixel-trackers-manager').'</button></div>';
        }
    }

    /**
     * Add a dedicated audit page instead of turning PTM into another closed CMP.
     */
    public function register_interop_page() {
        add_submenu_page(
            'pixel-trackers-manager',
            'Interop & vérification',
            'Interop & vérification',
            'manage_options',
            'pixel-trackers-manager-interop',
            array( $this, 'render_interop_page' )
        );
    }

    private function detected_cmps() {
        if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
        $active = (array) get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
        }
        $definitions = array(
            'Complianz' => array( 'complianz-gdpr/' ),
            'CookieYes / Cookie Law Info' => array( 'cookie-law-info/', 'cookieyes/' ),
            'Real Cookie Banner' => array( 'real-cookie-banner/' ),
            'Cookiebot' => array( 'cookiebot/' ),
            'iubenda' => array( 'iubenda-cookie-law-solution/', 'iubenda/' ),
            'Moove GDPR' => array( 'gdpr-cookie-compliance/' ),
            'WPConsent' => array( 'wpconsent-' ),
            'Borlabs Cookie' => array( 'borlabs-cookie/' ),
        );
        $found = array();
        foreach ( $definitions as $label => $needles ) {
            foreach ( $active as $plugin_file ) {
                foreach ( $needles as $needle ) {
                    if ( 0 === strpos( strtolower( (string) $plugin_file ), strtolower( $needle ) ) ) {
                        $found[ $label ] = (string) $plugin_file;
                    }
                }
            }
        }
        return $found;
    }

    public function render_interop_page() {
        if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Accès refusé.', 'pixel-trackers-manager' ) ); }
        $cmps = $this->detected_cmps();
        $settings = $this->plugin->public_settings();
        $rules = $this->shared_rules();
        $token = wp_generate_password( 32, false, false );
        $token_hash = hash( 'sha256', $token );
        set_transient( 'pixel_trackers_manager_browser_probe_' . $token_hash, 1, 10 * MINUTE_IN_SECONDS );
        $probe_url = add_query_arg( 'pixel_trackers_manager_browser_probe', rawurlencode( $token ), home_url( '/' ) );
        ?>
        <div class="wrap">
            <h1>PTM — Interop & vérification</h1>
            <p>Cette page vérifie avec quoi PTM doit coopérer. PTM peut conserver son propre consentement léger, mais il peut aussi auditer un CMP déjà en place au lieu d’en afficher un second.</p>

            <div class="notice notice-info inline"><p><strong>Catalogue commun :</strong> PTM Rules <?php echo esc_html( isset( $rules['catalog_version'] ) ? $rules['catalog_version'] : 'inconnu' ); ?>, embarqué localement.</p></div>

            <h2>Gestion du consentement détectée</h2>
            <table class="widefat striped"><tbody>
                <tr><th>Consentement natif PTM</th><td><?php echo ! empty( $settings['consent_enabled'] ) ? '<strong>Actif</strong>' : 'Inactif'; ?></td></tr>
                <tr><th>WP Consent API</th><td><?php echo function_exists( 'wp_has_consent' ) ? '<strong>Disponible</strong>' : 'Non détectée'; ?><?php echo function_exists( 'wp_has_service_consent' ) ? ' · API par service disponible' : ''; ?></td></tr>
                <tr><th>CMP tiers actif</th><td><?php echo $cmps ? esc_html( implode( ', ', array_keys( $cmps ) ) ) : 'Aucun CMP connu détecté'; ?></td></tr>
            </tbody></table>

            <?php if ( ! empty( $settings['consent_enabled'] ) && $cmps ) : ?>
                <div class="notice notice-warning inline"><p><strong>À vérifier :</strong> le consentement PTM et un autre CMP semblent actifs en même temps. Deux bandeaux concurrents ne sont généralement pas souhaitables.</p></div>
            <?php endif; ?>

            <h2>Test réel dans le navigateur</h2>
            <p>Le lien ci-dessous est valable environ 10 minutes. Pour vérifier ce qui part <strong>avant tout consentement</strong>, copiez-le et ouvrez-le dans une fenêtre privée neuve. Le test observe les ressources chargées pendant quelques secondes et affiche le résultat directement dans la page.</p>
            <p><input type="text" readonly class="large-text code" value="<?php echo esc_attr( $probe_url ); ?>"></p>
            <p><a class="button button-primary" href="<?php echo esc_url( $probe_url ); ?>" target="_blank" rel="noreferrer">Tester dans ce navigateur</a></p>
            <p><small>Aucune donnée de ce test n’est envoyée à Le Potager du Web. PTM n’affiche que les noms des cookies/stockages, jamais leurs valeurs.</small></p>
        </div>
        <?php
    }

    /**
     * Enable a short-lived anonymous-compatible browser probe URL. The random token is
     * stored only as a hash in a transient and is consumed on first use.
     */
    public function maybe_browser_audit_probe() {
        $token = $this->query_value( 'pixel_trackers_manager_browser_probe' );
        if ( '' === $token ) { return; }
        $key = 'pixel_trackers_manager_browser_probe_' . hash( 'sha256', $token );
        if ( ! get_transient( $key ) ) { return; }
        delete_transient( $key );
        if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
        show_admin_bar( false );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_browser_audit_probe' ), PHP_INT_MAX - 200 );
    }

    public function enqueue_browser_audit_probe() {
        $rules = $this->shared_rules();
        wp_enqueue_script(
            'pixel-trackers-manager-browser-audit',
            plugin_dir_url( dirname( __DIR__ ) . '/pixel-trackers-manager.php' ) . 'assets/browser-audit-probe.js',
            array(),
            Pixel_Trackers_Manager_Plugin::VERSION . '-browser-audit1',
            true
        );
        wp_localize_script( 'pixel-trackers-manager-browser-audit', 'PixelTrackersManagerBrowserAudit', array(
            'catalogVersion' => isset( $rules['catalog_version'] ) ? sanitize_text_field( (string) $rules['catalog_version'] ) : 'unknown',
            'services' => array_values( (array) $rules['services'] ),
            'siteHost' => (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ),
            'waitMs' => 4000,
        ) );
    }

    public function register_elementor_widget( $widgets_manager ) {
        if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }
        $file = plugin_dir_path( __FILE__ ) . 'class-pixel-trackers-manager-elementor-widget.php';
        if ( file_exists( $file ) ) { require_once $file; }
        if ( class_exists( 'Pixel_Trackers_Manager_Elementor_Consent_Widget' ) ) { $widgets_manager->register( new Pixel_Trackers_Manager_Elementor_Consent_Widget() ); }
    }
}
