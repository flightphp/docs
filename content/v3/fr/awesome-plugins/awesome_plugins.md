# Plugins Géniaux

Flight est incroyablement extensible. Il existe de nombreux plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement pris en charge par l'équipe Flight et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Documentation API

La documentation API est cruciale pour toute API. Elle aide les développeurs à comprendre comment interagir avec votre API et à quoi s'attendre en retour. Il existe quelques outils disponibles pour vous aider à générer une documentation API pour vos projets Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Article de blog écrit par Daniel Schreiber sur la manière d'utiliser la spécification OpenAPI avec FlightPHP pour construire votre API en adoptant une approche API-first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI est un excellent outil pour générer une documentation API pour vos projets Flight. Il est très facile à utiliser et peut être personnalisé selon vos besoins. Il s'agit de la bibliothèque PHP pour générer la documentation Swagger.

## Surveillance des Performances des Applications (APM)

La surveillance des performances des applications (APM) est cruciale pour toute application. Elle vous aide à comprendre comment votre application fonctionne et où se trouvent les goulots d'étranglement. Il existe plusieurs outils APM qui peuvent être utilisés avec Flight.
- <span class="badge bg-info">bêta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM est une simple bibliothèque APM qui peut être utilisée pour surveiller vos applications Flight. Elle peut être utilisée pour surveiller les performances de votre application et vous aider à identifier les goulots d'étranglement.

## Authentification/Autorisation

L'authentification et l'autorisation sont cruciales pour toute application qui nécessite des contrôles pour déterminer qui peut accéder à quoi.

- <span class="badge bg-primary">officiel</span> [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle Flight Permissions. Cette bibliothèque est un moyen simple d'ajouter des permissions au niveau utilisateur et application à votre application.

## Mise en Cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe plusieurs bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- <span class="badge bg-primary">officiel</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Légère, simple et autonome, classe PHP de mise en cache en fichier

## CLI

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- <span class="badge bg-primary">officiel</span> [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petites quantités de données côté client. Ils peuvent être utilisés pour stocker les préférences de l'utilisateur, les paramètres de l'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - Il s'agit d'un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il dispose d'un certain nombre de panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le [Tracy](/awesome-plugins/tracy) gestionnaire d'erreurs, ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de Données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données sont de simples wrappers pour écrire des requêtes et d'autres sont des ORMs complets.

- <span class="badge bg-primary">officiel</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO officiel de Flight faisant partie du cœur. Il s'agit d'un simple wrapper pour simplifier le processus d'écriture et d'exécution de requêtes. Ce n'est pas un ORM.
- <span class="badge bg-primary">officiel</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord officiel de Flight. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin pour suivre tous les changements de base de données pour votre projet.

## Chiffrement

Le chiffrement est crucial pour toute application qui stocke des données sensibles. Chiffrer et déchiffrer les données n'est pas terriblement difficile, mais stocker correctement la clé de chiffrement [peut](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [être](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficile](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). La chose la plus importante est de ne jamais stocker votre clé de chiffrement dans un répertoire public ou de l'engager dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Il s'agit d'une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. Se mettre en route est assez simple pour commencer à chiffrer et déchiffrer des données.

## File d'Attente de Tâches

Les files d'attente de tâches sont vraiment utiles pour traiter les tâches de manière asynchrone. Cela peut inclure l'envoi d'e-mails, le traitement d'images, ou tout ce qui n'a pas besoin d'être fait en temps réel.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue est une bibliothèque qui peut être utilisée pour traiter les tâches de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite, et PostgreSQL.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour construire une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- <span class="badge bg-primary">officiel</span> [flightphp/session](/awesome-plugins/session) - Bibliothèque officielle Flight Session. Il s'agit d'une simple bibliothèque de session qui peut être utilisée pour stocker et récupérer des données de session. Elle utilise la gestion de session intégrée de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gestionnaire de sessions PHP (non-bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour un chiffrement/déchiffrement optionnel des données de session.

## Moteur de Modèles

Le moteur de modèles est au cœur de toute application web avec une interface utilisateur. Il existe plusieurs moteurs de modèles qui peuvent être utilisés avec Flight.

- <span class="badge bg-warning">déprécié</span> [flightphp/core View](/learn#views) - Il s'agit d'un moteur de modèles très basique qui fait partie du cœur. Il n'est pas recommandé de l'utiliser si votre projet comporte plus de quelques pages.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de modèles complet qui est très facile à utiliser et qui ressemble plus à une syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Intégration WordPress

Vous voulez utiliser Flight dans votre projet WordPress ? Il y a un plugin pratique pour cela !

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Ce plugin WordPress vous permet d'exécuter Flight directement aux côtés de WordPress. Il est parfait pour ajouter des API personnalisées, des microservices, ou même des applications complètes à votre site WordPress en utilisant le framework Flight. Super utile si vous voulez le meilleur des deux mondes !

## Contribution

Vous avez un plugin que vous souhaitez partager ? Soumettez une pull request pour l'ajouter à la liste !