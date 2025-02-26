# Plugins géniaux

Flight est incroyablement extensible. Il existe un certain nombre de plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement supportés par l'équipe Flight et d'autres sont des bibliothèques micro/légères pour vous aider à démarrer.

## Documentation de l'API

La documentation de l'API est cruciale pour toute API. Elle aide les développeurs à comprendre comment interagir avec votre API et à quoi s'attendre en retour. Il existe quelques outils disponibles pour vous aider à générer la documentation de l'API pour vos projets Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Article de blog écrit par Daniel Schreiber sur la façon d'utiliser le générateur OpenAPI avec FlightPHP pour générer la documentation de l'API.
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI est un excellent outil pour vous aider à générer la documentation de l'API pour vos projets Flight. C'est très facile à utiliser et peut être personnalisé pour répondre à vos besoins. C'est la bibliothèque PHP qui vous aide à générer la documentation Swagger.

## Authentification/Autorisation

L'authentification et l'autorisation sont cruciales pour toute application qui nécessite des contrôles sur qui peut accéder à quoi.

- [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle des permissions Flight. Cette bibliothèque est un moyen simple d'ajouter des autorisations au niveau de l'utilisateur et de l'application à votre application.

## Mise en cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe un certain nombre de bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome

## CLI

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petits morceaux de données du côté client. Ils peuvent être utilisés pour stocker les préférences des utilisateurs, les paramètres de l'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - C'est un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il possède plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/awesome-plugins/tracy), ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données sont simplement des wrappers pour écrire des requêtes et d'autres sont des ORM complets.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO officiel de Flight faisant partie du noyau. C'est un wrapper simple pour aider à simplifier le processus d'écriture de requêtes et de leur exécution. Ce n'est pas un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord officiel de Flight. Petite bibliothèque géniale pour récupérer et stocker facilement des données dans votre base de données.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin pour suivre tous les changements de base de données pour votre projet.

## Cryptage

Le cryptage est crucial pour toute application qui stocke des données sensibles. Crypter et décrypter les données n'est pas très difficile, mais stocker correctement la clé de cryptage [peut](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [être](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficile](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). La chose la plus importante est de ne jamais stocker votre clé de cryptage dans un répertoire public ou de l'engager dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - C'est une bibliothèque qui peut être utilisée pour crypter et décrypter des données. Se lancer est assez simple pour commencer à crypter et déchiffrer des données.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour créer une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- [Ghostff/Session](/awesome-plugins/session) - Gestionnaire de session PHP (non-bloquant, flash, segment, cryptage de session). Utilise l'open_ssl PHP pour un cryptage/décryptage facultatif des données de session.

## Modélisation

La modélisation est essentielle pour toute application web avec une interface utilisateur. Il existe un certain nombre de moteurs de modélisation qui peuvent être utilisés avec Flight.

- [flightphp/core View](/learn#views) - C'est un moteur de modélisation très basique qui fait partie du noyau. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de modélisation complet qui est très facile à utiliser et qui ressemble davantage à une syntaxe PHP qu'à Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous souhaitez partager ? Soumettez une demande de tirage pour l'ajouter à la liste !