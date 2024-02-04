# Super Plugins

Flight est incroyablement extensible. Il existe un certain nombre de plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement pris en charge par l'équipe FlightPHP et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Mise en cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe un certain nombre de bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - Il s'agit d'un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il dispose de plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/awesome-plugins/tracy), ce plugin ajoute quelques panneaux supplémentaires pour aider spécifiquement au débogage des projets Flight.

## Bases de données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données ne sont que des wrappers simples pour écrire des requêtes, tandis que d'autres sont des ORM complets.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO officiel de Flight qui fait partie du noyau. Il s'agit d'un wrapper simple pour simplifier le processus d'écriture et d'exécution de requêtes. Ce n'est pas un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord officiel de Flight. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour développer une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- [Ghostff/Session](/awesome-plugins/session) - Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement de session). Utilise open_ssl de PHP pour le chiffrement/déchiffrement facultatif des données de session.

## Modèles

La modélisation est essentielle pour toute application web avec une interface utilisateur. Il existe un certain nombre de moteurs de modélisation qui peuvent être utilisés avec Flight.

- [flightphp/core View](/learn#views) - Il s'agit d'un moteur de modélisation très basique qui fait partie du cœur. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de modélisation complet et très facile à utiliser, qui se rapproche plus d'une syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous aimeriez partager ? Soumettez une pull request pour l'ajouter à la liste !