# Plugins Impressionnants

Flight est incroyablement extensible. Il existe plusieurs plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement pris en charge par l'équipe Flight et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Authentification/Autorisation

L'authentification et l'autorisation sont cruciales pour toute application qui nécessite des contrôles sur qui peut accéder à quoi.

- [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle des autorisations de Flight. Cette bibliothèque est un moyen simple d'ajouter des autorisations au niveau utilisateur et application à votre application.

## Mise en cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe plusieurs bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome

## CLI

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petites données côté client. Ils peuvent être utilisés pour stocker les préférences de l'utilisateur, les paramètres de l'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est essentiel lorsque vous développez en environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - Il s'agit d'un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il possède plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/awesome-plugins/tracy), ce plugin ajoute quelques panneaux supplémentaires pour aider spécifiquement à déboguer les projets Flight.

## Bases de données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de base de données ne sont que des wrappers simples pour écrire des requêtes, et d'autres sont des ORM complets.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO officiel de Flight faisant partie du cœur. Il s'agit d'un wrapper simple pour simplifier le processus d'écriture et d'exécution des requêtes. Ce n'est pas un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord officiel de Flight. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.

## Chiffrement

Le chiffrement est essentiel pour toute application qui stocke des données sensibles. Chiffrer et déchiffrer les données n'est pas très difficile, mais stocker correctement la clé de chiffrement peut être difficile. La chose la plus importante est de ne jamais stocker votre clé de chiffrement dans un répertoire public ou de la commit dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Il s'agit d'une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. Il est assez simple de commencer à chiffrer et déchiffrer des données.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour développer une application Web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- [Ghostff/Session](/awesome-plugins/session) - Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement optionnel des données de session.

## Mise en forme

La mise en forme est essentielle pour toute application Web avec une interface utilisateur. Il existe plusieurs moteurs de mise en forme qui peuvent être utilisés avec Flight.

- [flightphp/core View](/learn#views) - Il s'agit d'un moteur de mise en forme très basique qui fait partie du cœur. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de mise en forme complet et très facile à utiliser qui se rapproche plus d'une syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous aimeriez partager ? Soumettez une pull request pour l'ajouter à la liste !