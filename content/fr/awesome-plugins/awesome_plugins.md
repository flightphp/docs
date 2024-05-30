# Extensions Géniaux

Flight est incroyablement extensible. Il existe plusieurs extensions qui peuvent être utilisées pour ajouter des fonctionnalités à votre application Flight. Certaines sont officiellement prises en charge par l'équipe Flight et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Mise en cache

La mise en cache est un excellent moyen de accélérer votre application. Il existe plusieurs bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome

## Interface en ligne de commande (CLI)

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petites quantités de données côté client. Ils peuvent être utilisés pour stocker les préférences des utilisateurs, les paramètres de l'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques extensions qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - C'est un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il dispose de plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/awesome-plugins/tracy), ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de données

Les bases de données sont essentielles pour la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données sont simplement des wrappers pour écrire des requêtes et d'autres sont des ORMs complets.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO officiel de Flight qui fait partie du core. C'est un wrapper simple pour simplifier le processus d'écriture de requêtes et de les exécuter. Ce n'est pas un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord officiel de Flight. Petite bibliothèque géniale pour récupérer et stocker facilement des données dans votre base de données.

## Chiffrement

Le chiffrement est crucial pour toute application qui stocke des données sensibles. Chiffrer et déchiffrer les données n'est pas très difficile, mais stocker correctement la clé de chiffrement peut être difficile. La chose la plus importante est de ne jamais stocker votre clé de chiffrement dans un répertoire public ou de la commettre à votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - C'est une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. Le démarrage est assez simple pour commencer à chiffrer et déchiffrer des données.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour développer une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- [Ghostff/Session](/awesome-plugins/session) - Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement facultatif des données de session.

## Modèles

Les modèles sont essentiels pour toute application web avec une UI. Il existe plusieurs moteurs de modèles qui peuvent être utilisés avec Flight.

- [flightphp/core View](/learn#views) - C'est un moteur de modèles très basique qui fait partie du core. Il n'est pas recommandé de l'utiliser si vous avez plus que quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de modèles complet et très facile à utiliser, qui se rapproche plus d'une syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous aimeriez partager ? Soumettez une pull request pour l'ajouter à la liste !