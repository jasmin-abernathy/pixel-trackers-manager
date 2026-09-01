(function () {
    'use strict';

    var cfg = window.PixelTrackersManagerBrowserAudit || {};
    var rules = Array.isArray(cfg.services) ? cfg.services : [];
    var startedAt = Date.now();

    function normaliseHost(host) {
        return String(host || '').toLowerCase().replace(/^www\./, '');
    }

    function hostOf(url) {
        try {
            return normaliseHost(new URL(url, window.location.href).hostname);
        } catch (e) {
            return '';
        }
    }

    function unique(values) {
        var seen = {};
        return values.filter(function (value) {
            value = String(value || '');
            if (!value || seen[value]) {
                return false;
            }
            seen[value] = true;
            return true;
        });
    }

    function allResourceUrls() {
        var urls = [];
        try {
            performance.getEntriesByType('resource').forEach(function (entry) {
                if (entry && entry.name) {
                    urls.push(entry.name);
                }
            });
        } catch (e) {}

        document.querySelectorAll('script[src],iframe[src],img[src],link[href],source[src],video[src],audio[src],object[data]').forEach(function (node) {
            urls.push(node.src || node.href || node.data || node.getAttribute('src') || node.getAttribute('href') || node.getAttribute('data') || '');
        });
        return unique(urls);
    }

    function matchRule(url) {
        var lower = String(url || '').toLowerCase();
        var i;
        var j;
        for (i = 0; i < rules.length; i += 1) {
            var patterns = Array.isArray(rules[i].patterns) ? rules[i].patterns : [];
            for (j = 0; j < patterns.length; j += 1) {
                if (patterns[j] && lower.indexOf(String(patterns[j]).toLowerCase()) !== -1) {
                    return rules[i];
                }
            }
        }
        return null;
    }

    function cookieNames() {
        if (!document.cookie) {
            return [];
        }
        return unique(document.cookie.split(';').map(function (part) {
            return part.split('=')[0].trim();
        }).filter(Boolean));
    }

    function storageKeys(storage) {
        var keys = [];
        try {
            for (var i = 0; i < storage.length; i += 1) {
                keys.push(storage.key(i));
            }
        } catch (e) {}
        return unique(keys);
    }

    function existingConsentSignals(cookies, localKeys) {
        var signals = [];
        var cookiePatterns = [/^wp_consent_/i, /^cmplz_/i, /cookieyes/i, /cookiebot/i, /cookieconsent/i, /euconsent/i, /borlabs/i, /moove_gdpr/i];
        cookies.forEach(function (name) {
            cookiePatterns.forEach(function (pattern) {
                if (pattern.test(name)) {
                    signals.push('cookie:' + name);
                }
            });
        });
        localKeys.forEach(function (key) {
            if (/pixel_trackers_manager_consent/i.test(key) || /consent/i.test(key)) {
                signals.push('storage:' + key);
            }
        });
        return unique(signals);
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
        });
    }

    function render(result) {
        var old = document.getElementById('ptm-browser-audit-overlay');
        if (old) {
            old.remove();
        }

        var overlay = document.createElement('div');
        overlay.id = 'ptm-browser-audit-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.style.cssText = 'position:fixed;z-index:2147483647;inset:16px;max-width:920px;margin:auto;background:#fff;color:#17251c;border:2px solid #23452d;border-radius:16px;box-shadow:0 18px 60px rgba(0,0,0,.28);padding:20px;overflow:auto;font:15px/1.5 system-ui,sans-serif;';

        var strict = result.consentSignals.length === 0;
        var knownProblem = strict && result.optionalKnown.length > 0;
        var title = knownProblem ? '⚠️ Des services facultatifs ont chargé avant consentement' : (strict ? '✅ Aucun service facultatif connu chargé avant consentement' : '🟠 Consentement déjà présent dans ce navigateur');

        var html = '<button id="ptm-browser-audit-close" style="float:right;font-size:18px">×</button>';
        html += '<h1 style="font-size:22px;margin-top:0">' + title + '</h1>';
        html += '<p><strong>Catalogue PTM Rules :</strong> ' + escapeHtml(cfg.catalogVersion || 'inconnu') + ' · observation locale ' + Math.max(1, Math.round((Date.now() - startedAt) / 1000)) + ' s.</p>';
        if (!strict) {
            html += '<p><strong>Ce test n’est pas un test pré-consentement strict</strong>, car un choix semble déjà enregistré. Relancez ce lien temporaire dans une fenêtre privée neuve pour le test le plus fiable.</p>';
        }

        html += '<h2 style="font-size:18px">Services connus réellement chargés</h2>';
        if (!result.known.length) {
            html += '<p>Aucun service du catalogue détecté parmi les ressources chargées.</p>';
        } else {
            html += '<ul>' + result.known.map(function (item) {
                return '<li><strong>' + escapeHtml(item.label) + '</strong> — ' + escapeHtml(item.ptmCategory || 'review') + '<br><code style="font-size:12px;word-break:break-all">' + escapeHtml(item.url) + '</code></li>';
            }).join('') + '</ul>';
        }

        html += '<h2 style="font-size:18px">Domaines externes non reconnus</h2>';
        if (!result.unknownExternal.length) {
            html += '<p>Aucun domaine tiers supplémentaire observé.</p>';
        } else {
            html += '<ul>' + result.unknownExternal.map(function (url) {
                return '<li><code style="font-size:12px;word-break:break-all">' + escapeHtml(url) + '</code></li>';
            }).join('') + '</ul>';
        }

        html += '<details><summary>Indices locaux de consentement</summary><p>PTM n’affiche que les <strong>noms</strong> des cookies/stockages, jamais leurs valeurs.</p><pre style="white-space:pre-wrap">' + escapeHtml(JSON.stringify({consentSignals: result.consentSignals, cookieNames: result.cookieNames, localStorageKeys: result.localStorageKeys}, null, 2)) + '</pre></details>';
        html += '<p style="margin-bottom:0"><small>Ce diagnostic est calculé dans votre navigateur et n’est pas envoyé à Le Potager du Web.</small></p>';
        overlay.innerHTML = html;
        document.documentElement.appendChild(overlay);
        document.getElementById('ptm-browser-audit-close').addEventListener('click', function () { overlay.remove(); });
    }

    function analyse() {
        var siteHost = normaliseHost(cfg.siteHost || window.location.hostname);
        var urls = allResourceUrls();
        var known = [];
        var unknownExternal = [];
        var knownSeen = {};

        urls.forEach(function (url) {
            var host = hostOf(url);
            var rule = matchRule(url);
            if (rule) {
                var key = String(rule.id || '') + '|' + url;
                if (!knownSeen[key]) {
                    knownSeen[key] = true;
                    known.push({id: rule.id || '', label: rule.label || rule.id || 'Service', ptmCategory: rule.ptm_category || 'review', url: url});
                }
                return;
            }
            if (host && host !== siteHost) {
                unknownExternal.push(url);
            }
        });

        var cookies = cookieNames();
        var localKeys = storageKeys(window.localStorage);
        var consentSignals = existingConsentSignals(cookies, localKeys);
        var optionalKnown = known.filter(function (item) {
            return ['statistics', 'marketing', 'external'].indexOf(item.ptmCategory) !== -1;
        });

        render({
            known: known,
            optionalKnown: optionalKnown,
            unknownExternal: unique(unknownExternal),
            cookieNames: cookies,
            localStorageKeys: localKeys,
            consentSignals: consentSignals
        });
    }

    window.setTimeout(analyse, Number(cfg.waitMs || 4000));
}());
