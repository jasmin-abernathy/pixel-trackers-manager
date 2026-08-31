=== Pixel Trackers Manager ===
Contributors: juliane16
Tags: privacy, gdpr, cookies, consent, trackers
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.0.2-test4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Audit local des traceurs, documentation de confidentialité et gestion facultative du consentement pour WordPress.

== Description ==

Pixel Trackers Manager (PTM) aide les administrateurs WordPress à comprendre ce que leur site fait réellement avec des services tiers et des traceurs, à maintenir leurs pages légales et, s'ils le souhaitent, à gérer le consentement des visiteurs.

PTM est conçu local-first : les résultats d'analyse, réglages et réponses de l'assistant restent dans WordPress par défaut. Le plugin ne certifie pas juridiquement la conformité au RGPD et ne remplace pas une analyse adaptée à l'activité réelle de l'organisation.

Fonctions principales :

* analyse standard ou complète des contenus publics ;
* détection de traceurs, services tiers, contenus externes et outils de consentement connus ;
* distinction entre preuve technique observée, intégration présente et élément restant à vérifier ;
* progression du scan par petits lots : une page en erreur n'arrête pas les autres ;
* assistant RGPD en langage courant, qui réutilise d'abord ce que WordPress permet déjà de déduire ;
* couverture documentaire fondée uniquement sur les éléments réellement applicables ;
* gestion des pages Mentions légales, Politique de confidentialité et Cookies / consentement ;
* création en brouillon et mise à jour explicite, sans publication juridique silencieuse ;
* compatibilité prudente avec Gutenberg, Elementor, Divi et plusieurs autres constructeurs ;
* bannière de consentement native facultative, désactivée à l'installation ;
* blocage protecteur des services facultatifs reconnus avant le choix lorsque la bannière PTM est activée ;
* actions Tout accepter / Tout refuser de poids visuel équivalent ;
* contrôle permanent Gérer mes choix, widget Elementor et shortcode universel ;
* premiers contrôles de pratiques externes au site : e-mailing, messageries, réservation, formulaires externes, paiements et fichiers/listes.

= Consentement =

La bannière PTM fonctionne indépendamment du constructeur de pages. Elle est montée au niveau global de la page afin d'éviter les conflits de positionnement propres aux thèmes, Divi ou Elementor.

Lorsque PTM gère le consentement :

* aucune catégorie facultative n'est précochée ;
* fermer la bannière ne vaut pas acceptation ;
* le choix peut être modifié avec Gérer mes choix ;
* les services facultatifs reconnus restent bloqués avant le choix ;
* le moteur réel est désactivé dans les éditeurs visuels des constructeurs.

Shortcodes publics : `[ptm_legal_notice]`, `[ptm_privacy_policy]`, `[ptm_cookies]`, `[ptm_services]`, `[ptm_rights]`, `[ptm_documents]` et `[ptm_consent_settings]`.

= Constructeurs de pages =

Gutenberg / WordPress : lecture et mise à jour via les API WordPress et shortcodes.

Elementor : lecture locale des widgets connus, création explicite de pages juridiques prises en charge et widget Gérer mes choix.

Divi : lecture prudente des contenus connus. PTM privilégie une structure native lorsqu'elle est clairement reconnue et un repli par shortcode lorsqu'une réécriture sûre n'est pas garantie.

Autres constructeurs : PTM reconnaît de façon conservatrice plusieurs signatures courantes et préfère toujours un repli manuel à une modification hasardeuse d'un stockage inconnu.

= Données et confidentialité =

PTM n'envoie pas les résultats d'audit à l'éditeur du plugin et n'intègre pas de télémétrie publicitaire.

Les préférences de consentement des visiteurs sont enregistrées localement dans leur navigateur. Les données d'administration et d'audit restent dans la base WordPress du site sauf action explicite vers un service externe documenté ci-dessous.

= Service externe facultatif : API Recherche d'entreprises =

PTM peut proposer une recherche facultative d'entreprise française pour préremplir des informations publiques. Cette recherche n'est exécutée qu'après un clic explicite d'un administrateur.

Le terme recherché (nom, SIREN ou SIRET) est alors envoyé à l'API publique Recherche d'entreprises opérée par la Direction interministérielle du numérique (DINUM). Aucun résultat de scan du site n'est envoyé avec cette requête.

Service : https://annuaire-entreprises.data.gouv.fr/donnees/api-entreprises
Documentation API : https://recherche-entreprises.api.gouv.fr/docs/

== Installation ==

1. Téléversez l'archive de Pixel Trackers Manager dans Extensions > Ajouter une extension.
2. Activez l'extension.
3. Ouvrez Pixel Trackers Manager : l'assistant de configuration se lance à la première ouverture, pas pendant l'activation.
4. Vérifiez les trois pages juridiques de référence proposées.
5. Lancez une analyse lorsque vous le souhaitez ; aucune analyse complète n'est déclenchée automatiquement par l'installation.
6. Activez la bannière native dans Consentement uniquement si vous souhaitez que PTM gère aussi le blocage et le choix des visiteurs.

== Frequently Asked Questions ==

= Pixel Trackers Manager rend-il automatiquement mon site conforme au RGPD ? =

Non. PTM fournit des observations techniques, aide à documenter les traitements et peut gérer un mécanisme de consentement. La conformité dépend toujours du contexte réel et des obligations applicables.

= Des données d'audit sont-elles envoyées à l'éditeur ? =

Non. Les analyses, réglages et réponses de l'assistant restent dans votre WordPress. Seule la recherche facultative d'entreprise contacte l'API publique correspondante après votre clic.

= La bannière est-elle activée automatiquement ? =

Non. Elle est désactivée par défaut. Si vous l'activez, les services facultatifs reconnus sont ensuite bloqués jusqu'au choix du visiteur.

= PTM remplace-t-il une solution de sécurité ? =

Non. PTM contrôle des aspects de confidentialité et de documentation. Il ne remplace pas un pare-feu, un antivirus, un scanner de vulnérabilités ou un outil de durcissement généraliste.

== Changelog ==

= 0.0.2-test4 =
* Assistant RGPD dans un onglet dédié et sauvegardes AJAX sans audit HTTP implicite.
* Navigation et vue d'ensemble responsive retravaillées.
* Pages non publiques distinguées des vraies erreurs HTTP.
* Création guidée de pages juridiques avec compatibilité Elementor et Divi.
* Rôles juridiques et contacts mieux séparés dans la documentation générée.
* Outils externes, conservation et sauvegardes mieux documentés.
* Bannière de consentement rendue indépendante des builders et contrôle Gérer mes choix fiabilisé.
* Compatibilité de test ciblée WordPress 7.1.

L'historique détaillé des builds de développement est conservé dans `changelog.txt`.
