(function () {
    'use strict';

    var cfg = window.PixelTrackersManagerWpConsentBridge || {};
    if (!cfg.enabled) {
        return;
    }

    function setCategory(category, allowed) {
        if (typeof window.wp_set_consent !== 'function') {
            return;
        }
        try {
            window.wp_set_consent(category, allowed ? 'allow' : 'deny');
        } catch (e) {}
    }

    function setService(service, allowed) {
        if (typeof window.wp_set_service_consent !== 'function') {
            return;
        }
        try {
            window.wp_set_service_consent(service, !!allowed);
        } catch (e) {}
    }

    function sync(detail) {
        detail = detail || {};

        // PTM's categories that map unambiguously to WP Consent API categories.
        setCategory('statistics', detail.statistics === true);
        setCategory('marketing', detail.marketing === true);

        // External content is intentionally not forced into one WP category. When the
        // service-level API (2.x+) is available, explicit service consent is used instead.
        (cfg.services || []).forEach(function (service) {
            if (!service || !service.id || service.ptmCategory === 'review') {
                return;
            }
            setService(service.id, detail[service.ptmCategory] === true);
        });
    }

    document.addEventListener('pixel-trackers-manager:consent-updated', function (event) {
        sync(event && event.detail ? event.detail : {});
    });

    window.PixelTrackersManagerWpConsentBridgeState = {
        apiPresent: typeof window.wp_has_consent === 'function',
        serviceApiPresent: typeof window.wp_has_service_consent === 'function',
        sync: sync
    };
}());
