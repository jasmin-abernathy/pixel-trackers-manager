=== Pixel Trackers Manager ===
Contributors: juliane16
Tags: privacy, gdpr, trackers, cookies, analytics, consent
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.0.2-test4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Repérez ce qui suit vos visiteurs, documentez ce qui est réellement utile et, si vous le souhaitez, bloquez les services facultatifs avant leur choix.

== Description ==

Pixel Trackers Manager est un outil technique d'aide à l'audit de confidentialité et à la documentation pour WordPress. Il repère des traceurs et services tiers, distingue autant que possible une activité réellement observée d'une simple intégration disponible, puis aide à maintenir les informations publiques du site.

Pixel Trackers Manager ne certifie pas la conformité au RGPD et ne remplace pas une analyse juridique adaptée à l'activité réelle de l'organisation.

Fonctions de la version de test 0.0.2-test4 :

* navigation principale retravaillée : onglets pastel continus, visuellement raccordés au contenu et sans tuile blanche isolée ;
* Assistant RGPD déplacé dans son propre onglet, sauvegardes AJAX locales et navigation entre sections sans audit HTTP caché ;
* petit sommaire latéral automatique sur les onglets riches, compact sur les écrans plus étroits ;
* vue d’ensemble responsive : les cartes et actions se replient sans imposer de défilement horizontal à la page ;
* éléments manquants ou partiels de la couverture documentaire cliquables vers la bonne étape de l’Assistant RGPD ;
* pages non publiques : une 404 correspondant à un brouillon, une page privée, en attente ou programmée est classée comme action WordPress et non comme erreur technique ;
* proposition de publier une page juridique générée lorsqu’elle est suffisamment renseignée, ou de la compléter avant publication ;
* création native des nouvelles pages juridiques avec Elementor via son API de documents et avec Divi 5 via ses blocs natifs ; compatibilité conservée avec l’ancien format Divi ;
* séparation claire entre éditeur du site, responsable du traitement, contact public, contact pour exercer les droits et DPO réellement désigné ; l’e-mail d’administration WordPress n’est plus recopié comme contact public ;
* critères de complétude des mentions légales renforcés selon le profil renseigné ;
* pages publiques générées débarrassées des consignes internes de PTM et des formulations « à vérifier dans le questionnaire » ;
* nouvelle étape « outils utilisés en dehors de WordPress » : e-mail, WhatsApp/messageries, réservation (Calendly, Koalendar, Google/Microsoft…), formulaires externes, paiements/HelloAsso et fichiers/listes ;
* conseils contextualisés pour les envois groupés hors outil de newsletter : désinscription, CCI et gestion des oppositions ;
* premiers contrôles de formulaires connus, centrés sur la minimisation et les champs obligatoires à justifier, sans déclarer automatiquement une pratique illégale ;
* adaptateurs de conservation en lecture seule pour Gravity Forms, WooCommerce et MailPoet, avec lien vers le réglage lorsque la durée doit être vérifiée ou définie ;
* détection des principales extensions de sauvegarde et lecture prudente des réglages UpdraftPlus utiles à la protection des données ; PTM ne remplace pas un plugin de sécurité ;
* consentement indépendant des constructeurs : barre fixe ou encart PTM natif ; le moteur est désactivé dans les éditeurs visuels et réinitialise prudemment certains modules Divi (Maps/reCAPTCHA) après consentement ;
* trois niveaux lisibles dans l’interface : problème constaté, à vérifier et conseil ;
* progression du scan toujours fondée sur le traitement des URL : les erreurs restent séparées et n’empêchent pas 100 % ;
* correctif conservé du test3 : un premier scan demandé depuis l’assistant ne peut plus se relancer tout seul après le rafraîchissement final.

Fonctions de la première version publique :

* analyse standard rapide et analyse complète optionnelle des contenus publics ;
* progression par petits lots : une page en erreur n'arrête pas les autres ;
* titres de pages lisibles, y compris les accents, apostrophes et caractères spéciaux ;
* détection de services de mesure d'audience, marketing, contenus externes, e-mailing et outils de consentement connus ;
* distinction entre suivi actif observé, intégration présente et simple référence à vérifier ;
* lecture locale des contenus de l'éditeur WordPress, d'Elementor et de Divi ;
* assistant en langage courant : Pixel Trackers Manager réutilise d'abord ce qu'il peut déjà déduire du site ;
* les champs facultatifs laissés vides ne sont pas transformés en erreurs et ne sont pas redemandés s'ils n'empêchent pas de publier un document cohérent ;
* score documentaire transparent : nombre d'éléments applicables documentés / nombre total d'éléments applicables ;
* génération des mentions légales, de la politique de confidentialité et des informations sur les cookies ;
* codes courts (shortcodes) : `[ptm_legal_notice]`, `[ptm_privacy_policy]`, `[ptm_cookies]`, `[ptm_services]`, `[ptm_rights]`, `[ptm_documents]` et `[ptm_consent_settings]` ;
* insertion explicite dans Elementor et Divi 4, ou mise à jour de l'éditeur WordPress classique ;
* Divi 5 : création des nouvelles pages juridiques dans une structure native Section → Ligne → Colonne → Texte lorsqu’il est clairement détecté ; repli sûr vers l’éditeur WordPress si le contexte n’est pas reconnu ;
* bouton pour mettre à jour en une seule fois les pages juridiques prises en charge ;
* bannière de consentement native facultative, désactivée à l'installation ;
* lorsque cette bannière est activée, les services facultatifs reconnus sont bloqués par défaut jusqu'au choix ;
* « Tout accepter » et « Tout refuser » ont le même poids visuel ; aucune case facultative n'est précochée ; fermer la bannière ne vaut pas acceptation ;
* apparence pouvant reprendre automatiquement la typographie et le style de boutons du site sans créer de biais entre accepter et refuser ;
* lien permanent « Gérer mes choix » et widget Elementor équivalent ; le shortcode universel fonctionne aussi dans Gutenberg, Divi et les zones de widgets ;
* les données d'audit, réponses et préférences d'administration restent dans WordPress ;
* journal local et export des données d'audit.

= Compatibilité avec les constructeurs =

La bannière de consentement ne dépend d'aucun constructeur : elle est rendue au niveau WordPress et continue donc à fonctionner si le site utilise Gutenberg, Elementor, Divi ou un thème classique.

Elementor : Pixel Trackers Manager peut lire le contenu textuel des widgets, insérer explicitement les documents pris en charge et proposer un widget « Gérer mes choix ».

Divi 4 : lecture des principaux modules textuels et insertion explicite d'un module contenant le shortcode.

Divi 5 : Pixel Trackers Manager lit le contenu utile et peut créer une nouvelle page juridique dans la structure native de Divi 5. Il ne réécrit pas arbitrairement une page existante lorsque sa structure ne peut pas être reconnue avec certitude ; le code court reste alors la solution de repli.

Autres constructeurs : Bricks, Beaver Builder, WPBakery, Oxygen (anciens et nouveaux formats), Breakdance, Brizy, SiteOrigin et Avada sont détectés de façon conservatrice lorsqu'une signature de page est disponible. La bannière continue de fonctionner au niveau WordPress ; pour les documents, Pixel Trackers Manager privilégie le code court plutôt qu'une modification hasardeuse de la structure du constructeur.

= Bannière de consentement =

La bannière native est optionnelle et reste désactivée après installation ou mise à jour. Cela évite de couper soudainement des services sur un site en production.

Une fois activée, le principe est inverse : Pixel Trackers Manager neutralise les services facultatifs reconnus avant le choix, puis n'active que les catégories acceptées. Il traite les scripts WordPress, les contenus intégrés courants et une partie des scripts ajoutés dynamiquement. Un garde-fou chargé très tôt dans la page complète la neutralisation pour les constructeurs ou thèmes qui créent directement leurs scripts de suivi. Les intégrations qui ne peuvent pas être interceptées de manière fiable doivent rester vérifiées par l'administrateur.

Si un autre gestionnaire de consentement est détecté, Pixel Trackers Manager avertit l'administrateur afin d'éviter deux bannières concurrentes.

= Service externe facultatif : Recherche d'entreprises =

La recherche d'entreprise n'est exécutée qu'après un clic explicite de l'administrateur. Le terme recherché (nom, SIREN ou SIRET) est envoyé à l'API publique Recherche d'entreprises afin de proposer des informations publiques à préremplir.

Aucune donnée d'audit n'est envoyée à l'éditeur de Pixel Trackers Manager.

Documentation du service : https://recherche-entreprises.api.gouv.fr/docs/

== Installation ==

1. Téléversez l'archive de Pixel Trackers Manager dans Extensions > Ajouter une extension.
2. Activez l'extension.
3. Ouvrez Pixel Trackers Manager pour la première fois : l’assistant de configuration se lance à ce moment-là, pas pendant l’activation.
4. Vérifiez les trois pages de référence proposées (mentions légales, confidentialité, cookies/traceurs). Si une page n’existe pas, l’assistant peut créer un brouillon avec le bon shortcode.
5. Lancez la première analyse uniquement si vous le souhaitez ; aucune analyse lourde n’est déclenchée par l’installation ou la simple ouverture.
6. Ouvrez l’onglet Assistant RGPD pour compléter uniquement ce que le site ne permet pas déjà de déduire ; Pages légales reste le récapitulatif documentaire.
7. Activez la bannière native dans Consentement seulement si vous souhaitez que Pixel Trackers Manager gère aussi le blocage et le choix des visiteurs.

== Frequently Asked Questions ==

= Pixel Trackers Manager rend-il automatiquement mon site conforme au RGPD ? =

Non. Il fournit des observations techniques, aide à documenter les traitements et peut gérer un mécanisme de consentement. La conformité dépend toujours du contexte réel et des obligations applicables.

= Un champ vide sera-t-il constamment redemandé ? =

Non. Une information facultative qui n'empêche pas de générer ou publier un contenu cohérent reste silencieuse. Elle est remise en avant uniquement lorsqu'elle est nécessaire ou lorsqu'une situation que vous avez explicitement déclarée la rend nécessaire.

= La bannière est-elle activée automatiquement ? =

Non. Elle est désactivée par défaut. Si vous l'activez, les services facultatifs reconnus sont ensuite bloqués par défaut jusqu'au choix du visiteur.

= Comment PTM gère-t-il Divi 5 ? =

Lorsqu’un site utilise clairement Divi 5, PTM peut créer une nouvelle page juridique avec une structure native Divi 5. Pour une page existante dont la structure n’est pas reconnue avec certitude, PTM privilégie toujours une modification explicite ou le shortcode plutôt qu’une réécriture risquée. La bannière de consentement reste indépendante de Divi.

= Des données sont-elles envoyées à l'éditeur ? =

Non. Les analyses, réglages et réponses à l'assistant restent dans votre WordPress. Seule la recherche facultative d'entreprise contacte le service public correspondant après votre clic.

== Changelog ==

= 0.0.2-test4 =
* Assistant RGPD dans un onglet dédié, navigation interne légère et sommaires de sections.
* Navigation pastel continue et vue d’ensemble responsive sans débordement horizontal.
* Couverture documentaire cliquable vers la correction correspondante.
* Brouillons/pages non publiques distingués des vraies erreurs 404 avec publication guidée.
* Création native des nouvelles pages juridiques Elementor et Divi 5, avec replis conservateurs.
* Séparation éditeur/responsable du traitement/contacts/DPO et contenus juridiques générés renforcés.
* Outils externes, pratiques d’e-mailing, réservation/messageries, premiers contrôles de formulaires, conservation et sauvegardes.
* Consentement indépendant des builders avec adaptation prudente de modules Divi après choix.

= 0.0.2-test3 =
* Correctif critique : le lancement automatique du premier scan est consommé une seule fois ; le rafraîchissement de fin ne peut plus relancer le scan.
* Assistant : actions de validation/navigation alignées en bas à droite.

= 0.0.2-test2 =
* Setup visuel en quatre étapes, lancé au premier accès et reprenable.
* Rappel masquable sur la page d’accueil de wp-admin et incitation au premier scan complet.
* Détection, sélection manuelle et création en brouillon des trois pages juridiques de référence.
* Gestion du consentement clarifiée avec choix barre/encart et respect d’une CMP tierce détectée.
* Sauvegardes de l’assistant rendues locales et légères ; aperçu final AJAX sans rechargement complet.
* Scan : 100 % = toutes les URL traitées, même lorsque certaines sont en erreur ; retry ciblé des échecs.
* Détection du constructeur par page et suppression des mentions Divi/Elementor hors contexte.
* Refonte visuelle des onglets, boutons secondaires, cartes et navigation du dashboard.
* Prototype de mode test administrateur et de fingerprint de consentement.
* Détection expérimentale des Google Fonts distantes comme ressource externe.
* Assistant relançable depuis Réglages.

= 0.0.1 =
* Première version publique, issue du cycle de développement interne 0.x.
* Analyse progressive accélérée par petits lots et erreurs de pages non bloquantes.
* Score fondé uniquement sur les éléments réellement applicables, avec ratio visible.
* Les informations facultatives non bloquantes ne sont plus redemandées ni pénalisées.
* Correction du décodage des titres de pages.
* Mise à jour groupée des pages juridiques prises en charge.
* Compatibilité Gutenberg, Elementor, Divi 4 et stratégie sûre pour Divi 5.
* Bannière native facultative avec blocage protecteur par défaut, choix visuellement équilibrés et préférences révocables.
* Widget Elementor « Gérer mes choix » et shortcode universel `[ptm_consent_settings]`.
