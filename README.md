# Pixel Trackers Manager

Pixel Trackers Manager est une extension WordPress d'aide à l'audit de confidentialité : détection de traceurs et services tiers, contrôle des pages légales, gestion facultative du consentement et aide à la documentation du site.

> **État du dépôt :** version de test `0.0.2-test4`. Le plugin ne certifie pas la conformité au RGPD et ne remplace pas une analyse juridique adaptée à l'activité réelle de l'organisation.

## Principes

- analyses et réglages conservés dans WordPress ;
- approche locale et sobre ;
- distinction entre activité réellement observée, intégration présente et point à vérifier ;
- consentement facultatif, sans favoriser visuellement l'acceptation ;
- compatibilité WordPress native, Elementor et Divi avec replis conservateurs ;
- aucune modification lourde ou publication juridique silencieuse.

## Installation de test

1. Placer ce dépôt dans `wp-content/plugins/pixel-trackers-manager/`, ou construire un ZIP du dossier du plugin.
2. Activer **Pixel Trackers Manager** dans WordPress.
3. Ouvrir le plugin pour lancer son assistant de configuration.

La version de test requiert WordPress 6.5+ et PHP 7.4+.

## Documentation

- `readme.txt` : fiche WordPress du plugin et fonctionnalités.
- `docs/USER-GUIDE.md` : guide utilisateur.
- `docs/TESTS-0.0.2-test4.md` : checklist de validation de la version actuelle.
- `docs/CAHIER-DES-CHARGES.md` : décisions produit et feuille de route de développement.
- `changelog.txt` : historique détaillé des versions de test.

## Développement

La branche `main` est destinée à rester sur une version testée et installable. Pour les changements en cours, une branche `develop` ou une branche de fonctionnalité est recommandée.

Les vérifications automatiques GitHub incluses contrôlent la syntaxe PHP et JavaScript à chaque push et pull request.

## Signaler un problème

Utilisez les modèles d'issues GitHub pour un bug ou une proposition d'amélioration. Pour une vulnérabilité de sécurité exploitable, évitez de publier les détails dans une issue publique et utilisez le canal privé de sécurité du dépôt lorsqu'il est activé.

## Licence

Pixel Trackers Manager est distribué sous licence **GPL v2 ou ultérieure**. Voir `LICENSE`.
