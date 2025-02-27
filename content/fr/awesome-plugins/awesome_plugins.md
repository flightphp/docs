# Plugins géniaux

Flight est incroyablement extensible. Il existe un certain nombre de plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement soutenus par l'équipe Flight et d'autres sont des bibliothèques micro/légères pour vous aider à commencer.

## Documentation de l'API

La documentation de l'API est cruciale pour toute API. Elle aide les développeurs à comprendre comment interagir avec votre API et ce à quoi s'attendre en retour. Il existe quelques outils disponibles pour vous aider à générer la documentation de l'API pour vos projets Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Article de blog écrit par Daniel Schreiber sur la façon d'utiliser la spécification OpenAPI avec FlightPHP pour construire votre API en utilisant une approche d'abord API.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI est un excellent outil pour vous aider à générer la documentation de l'API pour vos projets Flight. Il est très facile à utiliser et peut être personnalisé pour répondre à vos besoins. C'est la bibliothèque PHP pour vous aider à générer la documentation Swagger.

## Authentification/Autorisation

L'authentification et l'autorisation sont cruciales pour toute application qui nécessite des contrôles concernant qui peut accéder à quoi.

- [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle des permissions Flight. Cette bibliothèque est un moyen simple d'ajouter des permissions au niveau de l'utilisateur et de l'application à votre application.

## Mise en cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe un certain nombre de bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de cache PHP simple, légère et autonome.

## CLI

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petites quantités de données côté client. Ils peuvent être utilisés pour stocker les préférences utilisateur, les paramètres d'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - C'est un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il dispose de plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile de l'étendre et d'ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le [Tracy](/awesome-plugins/tracy) gestionnaire d'erreurs, ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de données

Les bases de données sont le cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données sont simplement des wrappers pour écrire des requêtes et certaines sont des ORM complets.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO Flight officiel qui fait partie du cœur. C'est un simple wrapper pour simplifier le processus d'écriture de requêtes et de les exécuter. Ce n'est pas un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord Flight officiel. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin pour suivre tous les changements de base de données pour votre projet.

## Chiffrement

Le chiffrement est crucial pour toute application qui stocke des données sensibles. Chiffrer et déchiffrer les données n'est pas très difficile, mais stocker correctement la clé de chiffrement [peut](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [être](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficile](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). La chose la plus importante est de ne jamais stocker votre clé de chiffrement dans un répertoire public ou de l'engager dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - C'est une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. Se lancer et commencer à chiffrer et déchiffrer des données est assez simple.

## File d'attente de tâches

Les files d'attente de tâches sont vraiment utiles pour traiter des tâches de manière asynchrone. Cela peut être l'envoi d'e-mails, le traitement d'images ou toute autre tâche qui n'a pas besoin d'être faite en temps réel.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue est une bibliothèque qui peut être utilisée pour traiter des tâches de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite et PostgreSQL.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour construire une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- [Ghostff/Session](/awesome-plugins/session) - Gestionnaire de sessions PHP (non-bloquant, flash, segment, cryptage des sessions). Utilise PHP open_ssl pour le chiffrement/déchiffrement optionnel des données de session.

## Modèles

Le modèle est essentiel pour toute application web avec une interface utilisateur. Il existe un certain nombre de moteurs de modèles qui peuvent être utilisés avec Flight.

- [flightphp/core View](/learn#views) - C'est un moteur de modèles très basique qui fait partie du cœur. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de modèles complet qui est très facile à utiliser et qui se rapproche d'une syntaxe PHP plus que Twig ou Smarty. Il est aussi très facile de l'étendre et d'ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous aimeriez partager ? Soumettez une demande de tirage pour l'ajouter à la liste !