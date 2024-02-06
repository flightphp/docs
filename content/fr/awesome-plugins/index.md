# Extensions Incroyables

Flight est incroyablement extensible. Il existe plusieurs extensions qui peuvent être utilisées pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement pris en charge par l'équipe FlightPHP et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Mise en cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe plusieurs bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- [Wruczek/PHP-File-Cache](/extensions-incroyables/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome dans un fichier

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques extensions qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/extensions-incroyables/tracy) - Il s'agit d'un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il possède plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/extensions-incroyables/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/extensions-incroyables/tracy), cette extension ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de base de données ne sont que des wrappers pour écrire des requêtes et d'autres sont des ORM complets.

- [flightphp/core PdoWrapper](/extensions-incroyables/pdo-wrapper) - Wrapper PDO officiel de Flight qui fait partie du core. Il s'agit d'un wrapper simple pour simplifier le processus d'écriture et d'exécution de requêtes. Ce n'est pas un ORM.
- [flightphp/active-record](/extensions-incroyables/active-record) - ORM/Mapper actif officiel de Flight. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour la création d'une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- [Ghostff/Session](/extensions-incroyables/session) - Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement de session). Utilise open_ssl de PHP pour le chiffrement/déchiffrement optionnel des données de session.

## Modélisation

La modélisation est primordiale pour toute application web avec une interface utilisateur. Il existe plusieurs moteurs de modélisation qui peuvent être utilisés avec Flight.

- [flightphp/core View](/learn#views) - Il s'agit d'un moteur de modélisation très basique qui fait partie du core. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/extensions-incroyables/latte) - Latte est un moteur de modélisation complet et très facile à utiliser, qui se rapproche plus d'une syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Contribution

Vous avez une extension que vous aimeriez partager ? Soumettez une pull request pour l'ajouter à la liste !