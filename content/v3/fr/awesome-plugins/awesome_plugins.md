# Plugins Formidables

Flight est incroyablement extensible. Il existe un certain nombre de plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement pris en charge par l'équipe Flight et d'autres sont des bibliothèques micro/lite pour vous aider à démarrer.

## Documentation API

La documentation API est cruciale pour toute API. Elle aide les développeurs à comprendre comment interagir avec votre API et ce qu'ils peuvent attendre en retour. Il existe quelques outils disponibles pour vous aider à générer la documentation API pour vos projets Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Article de blog écrit par Daniel Schreiber sur la façon d'utiliser la spécification OpenAPI avec FlightPHP pour construire votre API en adoptant une approche API-first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI est un excellent outil pour vous aider à générer la documentation API pour vos projets Flight. Il est très facile à utiliser et peut être personnalisé pour répondre à vos besoins. C'est la bibliothèque PHP pour vous aider à générer la documentation Swagger.

## Surveillance des Performances des Applications (APM)

La surveillance des performances des applications (APM) est cruciale pour toute application. Elle vous aide à comprendre comment votre application performe et où se trouvent les goulots d'étranglement. Il existe un certain nombre d'outils APM qui peuvent être utilisés avec Flight.
- <span class="badge bg-primary">officiel</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM est une bibliothèque APM simple qui peut être utilisée pour surveiller vos applications Flight. Elle peut être utilisée pour surveiller les performances de votre application et vous aider à identifier les goulots d'étranglement.

## Asynchrone

Flight est déjà un framework rapide, mais lui ajouter un turbo le rend encore plus amusant (et challenging) !

- [flightphp/async](/awesome-plugins/async) - Bibliothèque officielle Flight Async. Cette bibliothèque est une façon simple d'ajouter un traitement asynchrone à votre application. Elle utilise Swoole/Openswoole en arrière-plan pour fournir une façon simple et efficace d'exécuter des tâches de manière asynchrone.

## Autorisation/Permissions

L'autorisation et les permissions sont cruciales pour toute application qui nécessite des contrôles pour déterminer qui peut accéder à quoi.

- <span class="badge bg-primary">officiel</span> [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle Flight Permissions. Cette bibliothèque est une façon simple d'ajouter des permissions au niveau utilisateur et application à votre application. 

## Authentification

L'authentification est essentielle pour les applications qui doivent vérifier l'identité des utilisateurs et sécuriser les points de terminaison API.

- [firebase/php-jwt](/awesome-plugins/jwt) - Bibliothèque JSON Web Token (JWT) pour PHP. Une façon simple et sécurisée d'implémenter l'authentification basée sur des tokens dans vos applications Flight. Parfaite pour l'authentification API sans état, la protection des routes avec des middlewares, et l'implémentation de flux d'autorisation de style OAuth.

## Mise en Cache

La mise en cache est une excellente façon d'accélérer votre application. Il existe un certain nombre de bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- <span class="badge bg-primary">officiel</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de mise en cache PHP légère, simple et autonome en fichier

## CLI

Les applications CLI sont une excellente façon d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- <span class="badge bg-primary">officiel</span> [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont une excellente façon de stocker de petites quantités de données côté client. Ils peuvent être utilisés pour stocker les préférences utilisateur, les paramètres d'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit une façon simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - C'est un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il dispose d'un certain nombre de panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- <span class="badge bg-primary">officiel</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/awesome-plugins/tracy), ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de Données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez les données. Certaines bibliothèques de bases de données sont simplement des wrappers pour écrire des requêtes et d'autres sont des ORMs complets.

- <span class="badge bg-primary">officiel</span> [flightphp/core SimplePdo](/learn/simple-pdo) - Aide officielle Flight PDO qui fait partie du core. C'est un wrapper moderne avec des méthodes d'aide pratiques comme `insert()`, `update()`, `delete()`, et `transaction()` pour simplifier les opérations de base de données. Tous les résultats sont retournés sous forme de Collections pour un accès flexible aux tableaux/objets. Pas un ORM, juste une meilleure façon de travailler avec PDO.
- <span class="badge bg-warning">déprécié</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Wrapper officiel Flight PDO qui fait partie du core (déprécié depuis la v3.18.0). Utilisez SimplePdo à la place.
- <span class="badge bg-primary">officiel</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper officiel Flight ActiveRecord. Excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin pour suivre toutes les modifications de base de données pour votre projet.

## Chiffrement

Le chiffrement est crucial pour toute application qui stocke des données sensibles. Chiffrer et déchiffrer les données n'est pas terriblement difficile, mais stocker correctement la clé de chiffrement [peut](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [être](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficile](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). La chose la plus importante est de ne jamais stocker votre clé de chiffrement dans un répertoire public ou de l'engager dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - C'est une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. Se lancer est assez simple pour commencer à chiffrer et déchiffrer des données.

## File d'Attente de Tâches

Les files d'attente de tâches sont vraiment utiles pour traiter les tâches de manière asynchrone. Cela peut être l'envoi d'emails, le traitement d'images, ou tout ce qui n'a pas besoin d'être fait en temps réel.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue est une bibliothèque qui peut être utilisée pour traiter les tâches de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite, et PostgreSQL.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour construire une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- <span class="badge bg-primary">officiel</span> [flightphp/session](/awesome-plugins/session) - Bibliothèque officielle Flight Session. C'est une bibliothèque de session simple qui peut être utilisée pour stocker et récupérer des données de session. Elle utilise la gestion de session intégrée de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gestionnaire de Session PHP (non-bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement optionnel des données de session.

## Modélisation

La modélisation est au cœur de toute application web avec une interface utilisateur. Il existe un certain nombre de moteurs de modélisation qui peuvent être utilisés avec Flight.

- <span class="badge bg-warning">déprécié</span> [flightphp/core View](/learn#views) - C'est un moteur de modélisation très basique qui fait partie du core. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de modélisation complet qui est très facile à utiliser et se rapproche plus de la syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate est un puissant moteur de template PHP avec compilation d'actifs, héritage de templates, et traitement de variables. Fonctionnalités : minification automatique CSS/JS, mise en cache, encodage Base64, et intégration optionnelle avec le framework PHP Flight.

## Intégration WordPress

Voulez-vous utiliser Flight dans votre projet WordPress ? Il y a un plugin pratique pour cela !

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Ce plugin WordPress vous permet d'exécuter Flight directement aux côtés de WordPress. C'est parfait pour ajouter des API personnalisées, des microservices, ou même des applications complètes à votre site WordPress en utilisant le framework Flight. Super utile si vous voulez le meilleur des deux mondes !

## Contribution

Vous avez un plugin que vous aimeriez partager ? Soumettez une pull request pour l'ajouter à la liste !