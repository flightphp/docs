# Plugins Formidables

Flight est incroyablement extensible. Il y a un certain nombre de plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement pris en charge par l'équipe Flight et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Documentation de l'API

La documentation de l'API est cruciale pour toute API. Elle aide les développeurs à comprendre comment interagir avec votre API et ce qu'ils peuvent attendre en retour. Il y a quelques outils disponibles pour vous aider à générer une documentation d'API pour vos projets Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Article de blog écrit par Daniel Schreiber sur la façon d'utiliser la spécification OpenAPI avec FlightPHP pour construire votre API en adoptant une approche API d'abord.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI est un excellent outil pour vous aider à générer une documentation d'API pour vos projets Flight. Il est très facile à utiliser et peut être personnalisé selon vos besoins. Il s'agit de la bibliothèque PHP pour vous aider à générer la documentation Swagger.

## Surveillance des Performances des Applications (APM)

La surveillance des performances des applications (APM) est cruciale pour toute application. Elle vous aide à comprendre comment votre application fonctionne et où se trouvent les goulots d'étranglement. Il y a un certain nombre d'outils APM qui peuvent être utilisés avec Flight.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM est une simple bibliothèque APM qui peut être utilisée pour surveiller vos applications Flight. Elle peut être utilisée pour surveiller les performances de votre application et vous aider à identifier les goulots d'étranglement.

## Authentification/Autorisation

L'authentification et l'autorisation sont cruciales pour toute application qui nécessite des contrôles pour déterminer qui peut accéder à quoi. 

- <span class="badge bg-primary">officiel</span> [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle Flight Permissions. Cette bibliothèque est un moyen simple d'ajouter des permissions au niveau utilisateur et application à votre application. 

## Mise en Cache

La mise en cache est un excellent moyen d'accélérer votre application. Il y a un certain nombre de bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- <span class="badge bg-primary">officiel</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Légère, simple et autonome, classe PHP de mise en cache en fichier

## CLI

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- <span class="badge bg-primary">officiel</span> [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petites quantités de données du côté client. Ils peuvent être utilisés pour stocker les préférences de l'utilisateur, les paramètres de l'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il y a quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - Il s'agit d'un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il dispose d'un certain nombre de panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et d'ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le [Tracy](/awesome-plugins/tracy) gestionnaire d'erreurs, ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de Données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données sont simplement des wrappers pour écrire des requêtes et d'autres sont des ORMs complets.

- <span class="badge bg-primary">officiel</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO officiel Flight qui fait partie du cœur. Il s'agit d'un simple wrapper pour simplifier le processus d'écriture et d'exécution de requêtes. Ce n'est pas un ORM.
- <span class="badge bg-primary">officiel</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord officiel Flight. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin pour suivre tous les changements de base de données pour votre projet.

## Cryptage

Le cryptage est crucial pour toute application qui stocke des données sensibles. Le cryptage et le décryptage des données n'est pas terriblement difficile, mais stocker correctement la clé de cryptage [peut](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [être](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficile](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). La chose la plus importante est de ne jamais stocker votre clé de cryptage dans un répertoire public ou de l'engager dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Il s'agit d'une bibliothèque qui peut être utilisée pour crypter et décrypter des données. Se mettre en route est assez simple pour commencer à crypter et décrypter des données.

## File d'Attente de Tâches

Les files d'attente de tâches sont vraiment utiles pour traiter les tâches de manière asynchrone. Cela peut inclure l'envoi d'e-mails, le traitement d'images, ou tout ce qui n'a pas besoin d'être fait en temps réel.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue est une bibliothèque qui peut être utilisée pour traiter les tâches de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite, et PostgreSQL.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour construire une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- <span class="badge bg-primary">officiel</span> [flightphp/session](/awesome-plugins/session) - Bibliothèque officielle Flight Session. Il s'agit d'une simple bibliothèque de session qui peut être utilisée pour stocker et récupérer des données de session. Elle utilise la gestion de session intégrée de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gestionnaire de sessions PHP (non-bloquant, flash, segment, cryptage de session). Utilise PHP open_ssl pour un cryptage/décryptage optionnel des données de session.

## Modèles de Gabarits

Les modèles de gabarits sont au cœur de toute application web avec une interface utilisateur. Il y a un certain nombre de moteurs de gabarits qui peuvent être utilisés avec Flight.

- <span class="badge bg-warning">déconseillé</span> [flightphp/core View](/learn#views) - Il s'agit d'un moteur de gabarits très basique qui fait partie du cœur. Il n'est pas recommandé de l'utiliser si votre projet comporte plus de quelques pages.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de gabarits complet qui est très facile à utiliser et qui se rapproche plus de la syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et d'ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous souhaitez partager ? Soumettez une pull request pour l'ajouter à la liste !