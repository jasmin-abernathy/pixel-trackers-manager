# Interopérabilité CMP et test navigateur

## Objectif

PTM n'a pas vocation à forcer son propre bandeau lorsqu'un site utilise déjà un gestionnaire de consentement (CMP) satisfaisant.

La page **PTM → Interop & vérification** :

- détecte plusieurs CMP WordPress courants lorsqu'ils sont actifs ;
- indique si WP Consent API est disponible ;
- avertit si le consentement natif PTM et un CMP tiers semblent actifs simultanément ;
- génère un lien temporaire pour observer les ressources réellement chargées dans le navigateur.

## WP Consent API

PTM se déclare compatible avec WP Consent API.

Lorsque le consentement natif PTM est utilisé et que WP Consent API est disponible :

- `statistics` est synchronisé vers la catégorie `statistics` ;
- `marketing` est synchronisé vers `marketing` ;
- les services connus sont synchronisés individuellement si l'API par service 2.x est disponible ;
- la famille PTM `external` n'est pas arbitrairement forcée dans une catégorie WordPress globale : la préférence passe en priorité par le consentement par service.

PTM continue de fonctionner si WP Consent API n'est pas installé.

## Test navigateur local

L'interface génère un jeton aléatoire valable environ 10 minutes. Seul son hash est stocké temporairement dans WordPress. Le jeton est consommé à la première ouverture.

Pour un test pré-consentement utile :

1. copier le lien ;
2. l'ouvrir dans une fenêtre privée neuve ;
3. attendre environ quatre secondes ;
4. lire le diagnostic affiché directement sur la page.

Le script observe :

- les ressources présentes dans `PerformanceResourceTiming` ;
- les scripts, iframes, images, styles et médias chargés dans le DOM ;
- les noms des cookies accessibles ;
- les noms des clés de `localStorage`.

Il n'affiche pas les valeurs des cookies/stockages et n'envoie pas le diagnostic à Le Potager du Web.

## Limites

Ce premier test n'est pas un navigateur headless distant et ne prétend pas certifier juridiquement un site. Il sert à vérifier concrètement si un service facultatif connu a déjà été chargé avant qu'un consentement soit présent dans un contexte de navigation neuf.

Certains appels peuvent aussi être déclenchés par des Service Workers, des navigateurs/extensions ou des mécanismes non visibles dans les ressources du document : les observations restent donc des preuves techniques à interpréter, pas un certificat de conformité.

## PTM Rules

Le bloqueur natif et le test navigateur utilisent le snapshot local `data/ptm-rules.json`, issu du dépôt privé `jasmin-abernathy/ptm-rules`.

Aucun appel GitHub n'est effectué pendant l'utilisation du plugin.
