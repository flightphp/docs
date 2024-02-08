# Extensions Formidables

Le vol est incroyablement extensible. Il existe plusieurs extensions qui peuvent être utilisées pour ajouter des fonctionnalités à votre application vol. Certains sont officiellement pris en charge par l'équipe FlightPHP et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Stockage en cache

Le stockage en cache est un excellent moyen d'accélérer votre application. Il existe plusieurs bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- [Wruczek/PHP-File-Cache](/formidables-extensions/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome dans le fichier

## Débogage

Le débogage est essentiel lorsque vous développez dans votre environnement local. Il existe quelques extensions qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/formidables-extensions/tracy) - Il s'agit d'un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il possède plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/formidables-extensions/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/formidables-extensions/tracy), cette extension ajoute quelques panneaux supplémentaires pour aider à déboguer spécifiquement les projets Flight.

## Bases de données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données ne sont que des enveloppes simples pour écrire des requêtes, tandis que d'autres sont des ORM complets.

- [flightphp/core PdoWrapper](/formidables-extensions/pdo-wrapper) - Enveloppe PDO officielle de Flight qui fait partie du noyau. Il s'agit d'une enveloppe simple pour aider à simplifier le processus d'écriture et d'exécution de requêtes. Ce n'est pas un ORM.
- [flightphp/active-record](/formidables-extensions/active-record) - ORM/Mapper ActiveRecord officiel de Flight. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour la construction d'une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- [Ghostff/Session](/formidables-extensions/session) - Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement facultatif des données de session.

## Modélisation

La modélisation est essentielle pour toute application web avec une interface utilisateur. Il existe plusieurs moteurs de modélisation qui peuvent être utilisés avec Flight.

- [flightphp/core View](/learn#views) - Il s'agit d'un moteur de modélisation très basique qui fait partie du noyau. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/formidables-extensions/latte) - Latte est un moteur de modélisation complet et très facile à utiliser, plus proche d'une syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous aimeriez partager ? Soumettez une demande de tirage pour l'ajouter à la liste!