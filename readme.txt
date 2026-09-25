=== Pixel Trackers Manager ===
Contributors: juliane16
Tags: privacy, gdpr, cookies, consent, trackers
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Local privacy audits, legal documentation assistance, and optional consent management for WordPress.

== Description ==

Pixel Trackers Manager (PTM) helps WordPress administrators understand what their site actually does with trackers and third-party services, keep privacy-related documentation up to date, and optionally manage visitor consent.

PTM is designed local-first: audit results, settings, and assistant answers stay in WordPress by default. The plugin does not certify GDPR compliance and does not replace legal advice adapted to an organisation's actual activities.

Main features:

* standard or full analysis of public site content;
* detection of known trackers, third-party services, external content, and consent tools;
* distinction between observed technical evidence, an available integration, and something that still needs human verification;
* progressive scans where one failing page does not stop the remaining analysis;
* a guided privacy assistant that reuses information WordPress can already provide before asking questions;
* documentation coverage based only on requirements that actually apply;
* management of Legal Notice, Privacy Policy, and Cookies / Consent reference pages;
* explicit draft creation and updates, without silently publishing legal content;
* conservative compatibility with Gutenberg, Elementor, Divi, and several other page builders;
* an optional native consent interface that is disabled after installation;
* protective blocking of recognised optional services before the visitor's choice when PTM consent is enabled;
* Accept all and Reject all actions with equal visual weight;
* a persistent Manage my choices control, Elementor widget, and universal shortcode;
* initial checks for practices outside WordPress, including email, messaging, booking tools, external forms, payments, and files/lists.

= Consent =

The PTM consent interface is independent from the page builder. It is mounted globally on the page to avoid positioning and stacking conflicts caused by themes, Divi, or Elementor.

When PTM manages consent:

* no optional category is preselected;
* closing the interface is not treated as consent;
* visitors can change their choice through Manage my choices;
* recognised optional services stay blocked before a choice;
* the real blocking engine is disabled inside visual page-builder editors.

Public shortcodes: `[ptm_legal_notice]`, `[ptm_privacy_policy]`, `[ptm_cookies]`, `[ptm_services]`, `[ptm_rights]`, `[ptm_documents]`, and `[ptm_consent_settings]`.

= Page builders =

WordPress / Gutenberg: PTM uses WordPress APIs and public shortcodes for supported content.

Elementor: PTM can inspect known local widget content, explicitly create supported legal pages, and provides a Manage my choices widget.

Divi: PTM reads recognised content conservatively. It prefers a native structure only when that structure is clearly understood and falls back to a shortcode when a safe rewrite cannot be guaranteed.

Other builders: PTM recognises several common page-level signatures conservatively and prefers a manual fallback over modifying unknown builder storage.

= Data and privacy =

PTM does not send site-audit results to the plugin author and does not include advertising telemetry.

Visitor consent preferences are stored locally in the visitor's browser. Administration and audit data stay in the site's WordPress database unless an administrator explicitly starts the documented external lookup below.

= Optional external service: French company search API =

PTM can offer an optional French company lookup to prefill public organisation information. The lookup only runs after an administrator explicitly starts it.

The search term (company name, SIREN, or SIRET) is sent to the public Recherche d'entreprises API operated by the French Interministerial Digital Directorate (DINUM). No PTM site-audit result is sent with that request.

Service information: https://annuaire-entreprises.data.gouv.fr/donnees/api-entreprises
API documentation: https://recherche-entreprises.api.gouv.fr/docs/

== Installation ==

1. Upload the Pixel Trackers Manager ZIP through Plugins > Add Plugin > Upload Plugin.
2. Activate the plugin.
3. Open Pixel Trackers Manager. The guided setup starts on first access, not during activation.
4. Review the proposed Legal Notice, Privacy Policy, and Cookies / Consent reference pages.
5. Start an analysis when you choose. Installation does not automatically launch a full-site scan.
6. Enable the native consent interface only if you want PTM to manage visitor choices and blocking as well.

== Frequently Asked Questions ==

= Does Pixel Trackers Manager automatically make my site GDPR compliant? =

No. PTM provides technical observations, helps document relevant practices, and can manage a consent mechanism. Compliance still depends on the site's actual context and applicable obligations.

= Are audit results sent to the plugin author? =

No. Audit results, settings, and assistant answers stay in your WordPress installation. Only the optional company lookup contacts the documented public API after an administrator starts that lookup.

= Is the consent interface enabled automatically? =

No. It is disabled by default. If you enable it, recognised optional services are then blocked until the visitor makes a choice.

= Does PTM replace a security plugin? =

No. PTM focuses on privacy-related technical checks and documentation. It does not replace a firewall, malware scanner, vulnerability scanner, or general WordPress hardening tool.

== Changelog ==

= 0.0.2 =
* Dedicated GDPR assistant tab with AJAX saves that do not trigger hidden public-page audits.
* Updated navigation and responsive overview.
* Non-public WordPress pages separated from genuine public HTTP errors.
* Guided legal-page creation with Elementor and Divi compatibility.
* Better separation of legal roles and contact details in generated documentation.
* Expanded coverage of external tools, retention settings, and backups.
* Builder-independent consent interface and more reliable Manage my choices control.
* WordPress 7.1-targeted test matrix.

Detailed development-build history is kept in `changelog.txt`.
