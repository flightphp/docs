# Plugins incroyables

Flight est incroyablement extensible. Il existe un certain nombre de plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement supportés par l'équipe Flight et d'autres sont des bibliothèques micro/légères pour vous aider à démarrer.

## Documentation API

La documentation de l'API est cruciale pour toute API. Elle aide les développeurs à comprendre comment interagir avec votre API et à quoi s'attendre en retour. Il existe plusieurs outils disponibles pour vous aider à générer la documentation API pour vos projets Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Article de blog écrit par Daniel Schreiber sur l'utilisation de la spécification OpenAPI avec FlightPHP pour développer votre API en adoptant une approche API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI est un excellent outil pour vous aider à générer la documentation API pour vos projets Flight. Il est très facile à utiliser et peut être personnalisé selon vos besoins. C'est la bibliothèque PHP pour vous aider à générer la documentation Swagger.

## Surveillance de la performance des applications (APM)

La surveillance de la performance des applications (APM) est cruciale pour toute application. Elle vous aide à comprendre comment votre application fonctionne et où se trouvent les goulets d'étranglement. Il existe plusieurs outils APM qui peuvent être utilisés avec Flight.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM est une bibliothèque APM simple qui peut être utilisée pour surveiller vos applications Flight. Elle peut être utilisée pour surveiller la performance de votre application et vous aider à identifier les goulets d'étranglement.

## Authentification/Autorisation

L'authentification et l'autorisation sont cruciales pour toute application qui nécessite des contrôles pour déterminer qui peut accéder à quoi.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle des permissions Flight. Cette bibliothèque est un moyen simple d'ajouter des permissions au niveau utilisateur et application à votre application.

## Mise en cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe un certain nombre de bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome.

## CLI

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petites quantités de données côté client. Ils peuvent être utilisés pour stocker des préférences utilisateur, des paramètres d'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - C'est un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il a plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/awesome-plugins/tracy), ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données sont simplement des wrappers pour écrire des requêtes et d'autres sont des ORM complets.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO Flight officiel qui fait partie du noyau. C'est un wrapper simple pour aider à simplifier le processus d'écriture et d'exécution de requêtes. Ce n'est pas un ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord Flight officiel. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin pour suivre tous les changements de base de données pour votre projet.

## Chiffrement

Le chiffrement est crucial pour toute application qui stocke des données sensibles. Chiffrer et déchiffrer les données n'est pas très difficile, mais stocker correctement la clé de chiffrement [peut](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [être](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficile](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). La chose la plus importante est de ne jamais stocker votre clé de chiffrement dans un répertoire public ou de l'engager dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - C'est une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. Se mettre en marche est assez simple pour commencer à chiffrer et déchiffrer des données.

## File d'attente des tâches

Les files d'attente des tâches sont très utiles pour traiter des tâches de manière asynchrone. Cela peut être l'envoi d'emails, le traitement d'images, ou tout ce qui n'a pas besoin d'être fait en temps réel.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue est une bibliothèque qui peut être utilisée pour traiter des emplois de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite et PostgreSQL.

## Session

Les sessions ne sont pas vraiment utiles pour les API mais pour construire une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Bibliothèque officielle des sessions Flight. C'est une bibliothèque de session simple qui peut être utilisée pour stocker et récupérer des données de session. Elle utilise la gestion des sessions intégrée de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gestionnaire de sessions PHP (non-bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour un chiffrement/déchiffrement optionnel des données de session.

## Modèles

Le modèle est au cœur de toute application web avec une interface utilisateur. Il existe un certain nombre de moteurs de templates qui peuvent être utilisés avec Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - C'est un moteur de templates très basique qui fait partie du noyau. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de templates complet qui est très facile à utiliser et qui semble plus proche d'une syntaxe PHP que Twig ou Smarty. C'est aussi très facile d'étendre et d'ajouter vos propres filtres et fonctions.

## Contribution

Vous avez un plugin que vous aimeriez partager ? Soumettez une demande de tirage pour l'ajouter à la liste !