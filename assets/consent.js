(function () {
    'use strict';

    var cfg = window.PixelTrackersManagerConsent || {};
    var storageKey = cfg.storageKey || 'pixel_trackers_manager_consent_v1';
    var current = null;
    var lastFocusedElement = null;

    function readChoice() {
        try {
            var saved = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (!saved || !saved.savedAt) {
                return null;
            }
            if (cfg.fingerprint && saved.fingerprint !== cfg.fingerprint) {
                return null;
            }
            var age = Date.now() - Number(saved.savedAt);
            if (age > Number(cfg.retentionDays || 180) * 86400000) {
                return null;
            }
            return saved;
        } catch (e) {
            return null;
        }
    }

    function allowed(category) {
        return !!(current && current[category] === true);
    }

    function classify(url) {
        var value = String(url || '').toLowerCase();
        var domains = cfg.domains || {};
        var category;
        var i;

        for (category in domains) {
            if (!Object.prototype.hasOwnProperty.call(domains, category)) {
                continue;
            }
            for (i = 0; i < domains[category].length; i += 1) {
                if (value.indexOf(String(domains[category][i]).toLowerCase()) !== -1) {
                    return category;
                }
            }
        }
        return '';
    }

    function blockNode(node) {
        if (!node || node.nodeType !== 1) {
            return node;
        }

        var tag = (node.tagName || '').toLowerCase();
        var src = '';
        try {
            src = node.src || node.getAttribute('src') || '';
        } catch (e) {
            src = '';
        }

        var category = classify(src);
        if (!category || allowed(category)) {
            return node;
        }

        if (tag === 'script') {
            if (src) {
                node.setAttribute('data-ptm-src', src);
                node.removeAttribute('src');
            }
            var type = node.getAttribute('type');
            if (type && type !== 'text/plain') {
                node.setAttribute('data-ptm-type', type);
            }
            node.setAttribute('type', 'text/plain');
        } else if (tag === 'iframe') {
            if (src) {
                node.setAttribute('data-ptm-src', src);
                node.setAttribute('src', 'about:blank');
            }
        } else if (tag === 'img') {
            if (src) {
                node.setAttribute('data-ptm-src', src);
                node.removeAttribute('src');
            }
        }

        node.setAttribute('data-ptm-category', category);
        node.setAttribute('data-ptm-blocked', '1');
        return node;
    }

    var nativeAppendChild = Element.prototype.appendChild;
    var nativeInsertBefore = Element.prototype.insertBefore;
    var previousSetAttribute = Element.prototype.setAttribute;

    Element.prototype.setAttribute = function (name, value) {
        var tag = (this.tagName || '').toLowerCase();
        if (String(name).toLowerCase() === 'src' && (tag === 'script' || tag === 'iframe' || tag === 'img')) {
            var category = classify(value);
            if (category && !allowed(category)) {
                previousSetAttribute.call(this, 'data-ptm-src', value);
                previousSetAttribute.call(this, 'data-ptm-category', category);
                previousSetAttribute.call(this, 'data-ptm-blocked', '1');
                if (tag === 'script') {
                    previousSetAttribute.call(this, 'type', 'text/plain');
                    return;
                }
                if (tag === 'iframe') {
                    return previousSetAttribute.call(this, 'src', 'about:blank');
                }
                if (tag === 'img') {
                    return;
                }
            }
        }
        return previousSetAttribute.call(this, name, value);
    };

    Element.prototype.appendChild = function (node) {
        return nativeAppendChild.call(this, blockNode(node));
    };

    Element.prototype.insertBefore = function (node, referenceNode) {
        return nativeInsertBefore.call(this, blockNode(node), referenceNode);
    };

    function guardSrc(prototype) {
        try {
            var descriptor = Object.getOwnPropertyDescriptor(prototype, 'src');
            if (!descriptor || !descriptor.get || !descriptor.set || descriptor.configurable === false) {
                return;
            }

            Object.defineProperty(prototype, 'src', {
                configurable: true,
                enumerable: descriptor.enumerable,
                get: descriptor.get,
                set: function (value) {
                    var category = classify(value);
                    var tag = (this.tagName || '').toLowerCase();
                    if (category && !allowed(category)) {
                        previousSetAttribute.call(this, 'data-ptm-src', value);
                        previousSetAttribute.call(this, 'data-ptm-category', category);
                        previousSetAttribute.call(this, 'data-ptm-blocked', '1');
                        if (tag === 'script') {
                            previousSetAttribute.call(this, 'type', 'text/plain');
                        } else if (tag === 'iframe') {
                            descriptor.set.call(this, 'about:blank');
                        }
                        return;
                    }
                    return descriptor.set.call(this, value);
                }
            });
        } catch (e) {
            // Server-side neutralisation remains available if a browser refuses this guard.
        }
    }

    if (window.HTMLScriptElement) {
        guardSrc(window.HTMLScriptElement.prototype);
    }
    if (window.HTMLIFrameElement) {
        guardSrc(window.HTMLIFrameElement.prototype);
    }
    if (window.HTMLImageElement) {
        guardSrc(window.HTMLImageElement.prototype);
    }

    function testChoice() {
        if (!cfg.testMode) { return null; }
        if (cfg.testMode === 'accept') { return {statistics:true, external:true, marketing:true, savedAt:Date.now(), fingerprint:cfg.fingerprint || ''}; }
        if (cfg.testMode === 'statistics') { return {statistics:true, external:false, marketing:false, savedAt:Date.now(), fingerprint:cfg.fingerprint || ''}; }
        return {statistics:false, external:false, marketing:false, savedAt:Date.now(), fingerprint:cfg.fingerprint || ''};
    }

    current = cfg.preview ? null : (testChoice() || readChoice());

    function activateAllowedResources() {
        document.querySelectorAll('[data-ptm-blocked="1"][data-ptm-category]').forEach(function (element) {
            var category = element.getAttribute('data-ptm-category');
            if (!allowed(category)) {
                return;
            }

            var tag = element.tagName.toLowerCase();
            var src = element.getAttribute('data-ptm-src');

            if (tag === 'script') {
                var replacement = document.createElement('script');
                Array.prototype.slice.call(element.attributes).forEach(function (attribute) {
                    if (['type', 'data-ptm-src', 'data-ptm-category', 'data-ptm-blocked', 'data-ptm-type'].indexOf(attribute.name) === -1) {
                        replacement.setAttribute(attribute.name, attribute.value);
                    }
                });
                var originalType = element.getAttribute('data-ptm-type');
                if (originalType) {
                    replacement.type = originalType;
                }
                if (src) {
                    replacement.src = src;
                }
                if (element.textContent) {
                    replacement.textContent = element.textContent;
                }
                if (element.parentNode) {
                    element.parentNode.replaceChild(replacement, element);
                }
                return;
            }

            if (src) {
                element.setAttribute('src', src);
            }
            element.removeAttribute('data-ptm-blocked');
        });
    }

    var diviMapsInitialised = false;
    var diviRecaptchaInitialised = false;

    function reinitialiseDiviIntegrations() {
        // Divi modules may have attempted to initialise while an optional third-party
        // resource was still blocked. Once the visitor allows it, give Divi a chance
        // to initialise the affected module again. This adapter is deliberately small
        // and only calls public/global runtime hooks when Divi itself exposes them.
        if (allowed('external') && document.querySelector('.et_pb_map_container') && !diviMapsInitialised) {
            var mapAttempts = 0;
            var initMaps = function () {
                mapAttempts += 1;
                if (window.jQuery && typeof window.et_pb_map_init === 'function') {
                    window.jQuery('.et_pb_map_container').each(function () {
                        try { window.et_pb_map_init(window.jQuery(this)); } catch (e) {}
                    });
                    diviMapsInitialised = true;
                    return;
                }
                if (mapAttempts < 8) { window.setTimeout(initMaps, 500); }
            };
            window.setTimeout(initMaps, 180);
        }

        // reCAPTCHA is not force-classified by PTM because its legal treatment depends
        // on context. If another rule has delayed it and Divi exposes its own runtime
        // reinitializer after consent, use that hook without making reCAPTCHA a tracker
        // category by default.
        if (!diviRecaptchaInitialised && document.querySelector('.et_pb_contact_form_container')) {
            var recaptchaAttempts = 0;
            var initRecaptcha = function () {
                recaptchaAttempts += 1;
                var api = window.etCore && window.etCore.api && window.etCore.api.spam && window.etCore.api.spam.recaptcha;
                if (api && typeof api.init === 'function' && (window.grecaptcha || document.querySelector('script[src*="recaptcha"]'))) {
                    try { api.init(); diviRecaptchaInitialised = true; } catch (e) {}
                    return;
                }
                if (recaptchaAttempts < 6) { window.setTimeout(initRecaptcha, 500); }
            };
            window.setTimeout(initRecaptcha, 220);
        }
    }

    function announceConsentUpdate() {
        try {
            document.dispatchEvent(new CustomEvent('pixel-trackers-manager:consent-updated', {
                detail: {
                    statistics: allowed('statistics'),
                    external: allowed('external'),
                    marketing: allowed('marketing')
                }
            }));
        } catch (e) {}
    }

    function syncEarlyGuard(choice) {
        if (window.PixelTrackersManagerConsentEarly && typeof window.PixelTrackersManagerConsentEarly.setCurrent === 'function') {
            window.PixelTrackersManagerConsentEarly.setCurrent(choice);
        }
    }

    function saveChoice(choice) {
        choice.savedAt = Date.now();
        choice.fingerprint = cfg.fingerprint || '';
        if (!cfg.preview && !cfg.testMode) {
            try {
                localStorage.setItem(storageKey, JSON.stringify(choice));
            } catch (e) {
                // Consent still works for the current page if storage is unavailable.
            }
        }
        current = choice;
        syncEarlyGuard(choice);
        activateAllowedResources();
        reinitialiseDiviIntegrations();
        announceConsentUpdate();
        hideBanner(true);
    }

    function saveAll(value) {
        saveChoice({
            statistics: value,
            external: value,
            marketing: value
        });
    }

    function bannerRoot() {
        return document.getElementById('pixel-trackers-manager-consent');
    }

    function hideBanner(restoreFocus) {
        var root = bannerRoot();
        if (root) {
            root.hidden = true;
        }
        if (restoreFocus && lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            try {
                lastFocusedElement.focus();
            } catch (e) {
                // No action needed if the previous element disappeared.
            }
        }
    }

    function showBanner(force) {
        var root = bannerRoot();
        if (!root) {
            return;
        }
        if (!force && current && !cfg.preview) {
            root.hidden = true;
            return;
        }
        lastFocusedElement = document.activeElement;
        root.hidden = false;
        var dialog = root.querySelector('.ptm-consent-dialog');
        if (dialog) {
            window.setTimeout(function () {
                dialog.focus();
            }, 0);
        }
    }

    function closeWithoutChoice() {
        try {
            sessionStorage.setItem('pixel_trackers_manager_consent_closed', '1');
        } catch (e) {
            // Session storage is only a convenience to avoid reopening after a close.
        }
        hideBanner(true);
    }

    function trapKeyboard(event, root) {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeWithoutChoice();
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }

        var focusable = Array.prototype.slice.call(root.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
        )).filter(function (element) {
            return !element.hidden && element.offsetParent !== null;
        });

        if (!focusable.length) {
            return;
        }

        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function inheritSiteStyle() {
        if (cfg.style !== 'inherit') {
            return;
        }

        var root = bannerRoot();
        if (!root) {
            return;
        }

        var bodyStyle = window.getComputedStyle(document.body);
        var representativeButton = document.querySelector(
            '.elementor-button, .et_pb_button, .bricks-button, .fl-button, .vc_btn3, .wp-element-button, button[type="submit"], input[type="submit"]'
        );
        var buttonStyle = representativeButton ? window.getComputedStyle(representativeButton) : null;

        root.style.setProperty('--pixel-trackers-manager-font', bodyStyle.fontFamily || 'inherit');
        if (buttonStyle && buttonStyle.borderRadius) {
            root.style.setProperty('--pixel-trackers-manager-radius', buttonStyle.borderRadius);
        }

        // Brand colours are deliberately not copied. Accept and reject must remain equivalent.
    }

    function initialise() {
        current = cfg.preview ? null : (testChoice() || readChoice());
        syncEarlyGuard(current);
        activateAllowedResources();
        reinitialiseDiviIntegrations();
        inheritSiteStyle();

        var root = bannerRoot();
        if (root) {
            root.addEventListener('click', function (event) {
                var button = event.target.closest('[data-ptm-action]');
                if (!button) {
                    return;
                }

                var action = button.getAttribute('data-ptm-action');
                if (action === 'reject') {
                    saveAll(false);
                } else if (action === 'accept') {
                    saveAll(true);
                } else if (action === 'customize') {
                    var preferences = root.querySelector('.ptm-consent-preferences');
                    preferences.hidden = !preferences.hidden;
                    button.setAttribute('aria-expanded', preferences.hidden ? 'false' : 'true');
                } else if (action === 'save') {
                    var choice = { statistics: false, external: false, marketing: false };
                    root.querySelectorAll('[data-ptm-category-toggle]').forEach(function (toggle) {
                        choice[toggle.getAttribute('data-ptm-category-toggle')] = !!toggle.checked;
                    });
                    saveChoice(choice);
                }
            });

            root.addEventListener('keydown', function (event) {
                trapKeyboard(event, root);
            });

            var close = root.querySelector('.ptm-consent-close');
            if (close) {
                close.addEventListener('click', closeWithoutChoice);
            }
        }

        document.addEventListener('click', function (event) {
            if (event.target.closest('.ptm-consent-open')) {
                event.preventDefault();
                showBanner(true);
            }
        });

        var closedThisSession = false;
        try {
            closedThisSession = sessionStorage.getItem('pixel_trackers_manager_consent_closed') === '1';
        } catch (e) {
            closedThisSession = false;
        }

        if (!current && !closedThisSession) {
            showBanner(false);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise);
    } else {
        initialise();
    }
}());
