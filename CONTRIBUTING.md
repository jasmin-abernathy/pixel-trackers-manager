# Contribuer à Pixel Trackers Manager

Merci de contribuer au projet.

## Avant de proposer un changement

- Vérifiez qu'une issue similaire n'existe pas déjà.
- Gardez les modifications ciblées : un correctif ou une fonctionnalité cohérente par branche/PR.
- Ne rendez pas une fonction distante ou intrusive indispensable lorsque le traitement peut rester local.
- Ne publiez pas de données personnelles, identifiants, clés API ou exports de sites clients.

## Vérifications minimales

Avant un push ou une pull request :

- PHP : `php -l` sur tous les fichiers `.php` ;
- JavaScript : `node --check` sur les fichiers `.js` ;
- tester l'activation du plugin sans erreur fatale ;
- pour une modification du scanner, vérifier qu'une URL en erreur n'interrompt pas le reste du scan ;
- pour une modification juridique ou de consentement, vérifier qu'aucun contenu n'est publié ou activé sans action explicite de l'utilisateur.

La checklist détaillée de la version courante se trouve dans `docs/TESTS-0.0.2-test4.md`.
