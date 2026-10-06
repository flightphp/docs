# Plugins géniaux

Flight est incroyablement extensible. Il existe de nombreux plugins qui peuvent être utilisés pour ajouter des fonctionnalités à votre application Flight. Certains sont officiellement pris en charge par l'équipe Flight et d'autres sont des bibliothèques micro/légères pour vous aider à démarrer.

## Outils d'IA

Flight peut devenir encore plus cool avec des plugins alimentés par l'IA.

- [Flight MCP](/awesome-plugins/mcp) - Un plugin pour intégrer MCP (Model Control Protocol) avec Flight, permettant des fonctionnalités alimentées par l'IA en toute fluidité. Principalement concentré sur les pages de documentation, il aide à réduire les coûts de jetons en fournissant les informations les plus à jour sur vos projets Flight.
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) - Un squelette de serveur MCP FlightPHP avec HTTP et stdio, ainsi que la découverte automatique d'outils, de prompts et de ressources.

## Documentation d'API

La documentation d'API est cruciale pour toute API. Elle aide les développeurs à comprendre comment interagir avec votre API et ce à quoi s'attendre en retour. Il existe quelques outils disponibles pour vous aider à générer la documentation d'API pour vos projets Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Article de blog écrit par Daniel Schreiber sur la façon d'utiliser la spécification OpenAPI avec FlightPHP pour construire votre API en utilisant une approche API-first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI est un excellent outil pour vous aider à générer la documentation d'API pour vos projets Flight. Il est très facile à utiliser et peut être personnalisé selon vos besoins. Il s'agit de la bibliothèque PHP qui vous aide à générer la documentation Swagger.

## Surveillance des performances applicatives (APM)

La surveillance des performances applicatives (APM) est cruciale pour toute application. Elle vous aide à comprendre comment votre application se comporte et où se trouvent les goulots d'étranglement. Il existe un certain nombre d'outils APM qui peuvent être utilisés avec Flight.

- <span class="badge bg-primary">officiel</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM est une bibliothèque APM simple qui peut être utilisée pour surveiller vos applications Flight. Elle permet de surveiller les performances de votre application et d'identifier les goulots d'étranglement.

## Async

Flight est déjà un framework rapide, mais lui ajouter un moteur turbo rend tout plus amusant (et plus stimulant) !

- [flightphp/async](/awesome-plugins/async) - Bibliothèque Async officielle de Flight. Cette bibliothèque est un moyen simple d'ajouter le traitement asynchrone à votre application. Elle utilise Swoole/Openswoole en interne pour fournir un moyen simple et efficace d'exécuter des tâches de manière asynchrone.

## Autorisation/Permissions

L'autorisation et les permissions sont cruciales pour toute application qui nécessite la mise en place de contrôles pour déterminer qui peut accéder à quoi.

- <span class="badge bg-primary">officiel</span> [flightphp/permissions](/awesome-plugins/permissions) - Bibliothèque officielle de permissions Flight. Cette bibliothèque est un moyen simple d'ajouter des permissions au niveau utilisateur et au niveau application à votre application.

## Authentification

L'authentification est essentielle pour les applications qui doivent vérifier l'identité des utilisateurs et sécuriser les endpoints API.

- [firebase/php-jwt](/awesome-plugins/jwt) - Bibliothèque JSON Web Token (JWT) pour PHP. Un moyen simple et sécurisé d'implémenter l'authentification par jeton dans vos applications Flight. Idéal pour l'authentification d'API sans état, la protection des routes avec des middleware et l'implémentation de flux d'autorisation de type OAuth.

## Mise en cache

La mise en cache est un excellent moyen d'accélérer votre application. Il existe un certain nombre de bibliothèques de mise en cache qui peuvent être utilisées avec Flight.

- <span class="badge bg-primary">officiel</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de mise en cache en fichier PHP légère, simple et autonome.

## CLI

Les applications CLI sont un excellent moyen d'interagir avec votre application. Vous pouvez les utiliser pour générer des contrôleurs, afficher toutes les routes, et plus encore.

- <span class="badge bg-primary">officiel</span> [flightphp/runway](/awesome-plugins/runway) - Runway est une application CLI qui vous aide à gérer vos applications Flight.

## Cookies

Les cookies sont un excellent moyen de stocker de petites quantités de données côté client. Ils peuvent être utilisés pour stocker les préférences utilisateur, les paramètres de l'application, et plus encore.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie est une bibliothèque PHP qui fournit un moyen simple et efficace de gérer les cookies.

## Débogage

Le débogage est crucial lorsque vous développez dans votre environnement local. Il existe quelques plugins qui peuvent améliorer votre expérience de débogage.

- [tracy/tracy](/awesome-plugins/tracy) - Il s'agit d'un gestionnaire d'erreurs complet qui peut être utilisé avec Flight. Il dispose d'un certain nombre de panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux.
- <span class="badge bg-primary">officiel</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilisé avec le gestionnaire d'erreurs [Tracy](/awesome-plugins/tracy), ce plugin ajoute quelques panneaux supplémentaires pour aider au débogage spécifiquement pour les projets Flight.

## Bases de données

Les bases de données sont au cœur de la plupart des applications. C'est ainsi que vous stockez et récupérez des données. Certaines bibliothèques de bases de données sont simplement des wrappers pour écrire des requêtes et d'autres sont des ORM à part entière.

- <span class="badge bg-primary">officiel</span> [flightphp/core SimplePdo](/learn/simple-pdo) - Helper PDO officiel de Flight intégré au cœur. Il s'agit d'un wrapper moderne avec des méthodes d'aide pratiques comme `insert()`, `update()`, `delete()` et `transaction()` pour simplifier les opérations de base de données. Tous les résultats sont retournés sous forme de Collections pour un accès flexible en tableau/objet. Ce n'est pas un ORM, juste une meilleure façon de travailler avec PDO.
- <span class="badge bg-warning">déprécié</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Wrapper PDO officiel de Flight intégré au cœur (déprécié depuis v3.18.0). Utilisez SimplePdo à la place.
- <span class="badge bg-primary">officiel</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mappeur ActiveRecord officiel de Flight. Une excellente petite bibliothèque pour récupérer et stocker facilement des données dans votre base de données.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin pour suivre toutes les modifications de base de données de votre projet.
- [knifelemon/easy-query](/awesome-plugins/easy-query) - Constructeur de requêtes SQL léger et fluide qui génère le SQL et les paramètres pour les requêtes préparées. Fonctionne très bien avec [SimplePdo](/learn/simple-pdo).

## Chiffrement

Le chiffrement est crucial pour toute application qui stocke des données sensibles. Chiffrer et déchiffrer les données n'est pas très difficile, mais stocker correctement la clé de chiffrement [peut](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [être](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficile](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). La chose la plus importante est de ne jamais stocker votre clé de chiffrement dans un répertoire public ou de la committer dans votre dépôt de code.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - C'est une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. La mise en route est relativement simple pour commencer à chiffrer et déchiffrer des données.

## E-mail

L'envoi d'e-mails est un besoin essentiel pour la plupart des applications web - messages de bienvenue, réinitialisations de mot de passe, notifications. Ces bibliothèques rendent cela simple tout en maintenant une délivrabilité solide.

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) - FlightMail enveloppe Symfony Mailer avec une API fluide et conviviale pour Flight. Envoyez des e-mails via SMTP ou tout fournisseur majeur à l'aide de simples chaînes DSN, routez différents fournisseurs par message et générez le contenu avec les templates Twig ou Latte. Ce plugin n'est pas officiel pour Flight et n'est pas maintenu par l'équipe Flight.

## File d'attente de tâches

Les files d'attente de tâches sont très utiles pour traiter des tâches de manière asynchrone. Cela peut être l'envoi d'e-mails, le traitement d'images, ou tout ce qui ne doit pas être fait en temps réel.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue est une bibliothèque qui peut être utilisée pour traiter des tâches de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite et PostgreSQL.

## Session

Les sessions ne sont pas vraiment utiles pour les API, mais pour développer une application web, les sessions peuvent être cruciales pour maintenir l'état et les informations de connexion.

- <span class="badge bg-primary">officiel</span> [flightphp/session](/awesome-plugins/session) - Bibliothèque de session officielle de Flight. C'est une bibliothèque de session simple qui peut être utilisée pour stocker et récupérer les données de session. Elle utilise la gestion de session intégrée de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gestionnaire de session PHP (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement facultatif des données de session.

## Templating

Le templating est essentiel pour toute application web avec une interface utilisateur. Il existe un certain nombre de moteurs de templating qui peuvent être utilisés avec Flight.

- <span class="badge bg-warning">déprécié</span> [flightphp/core View](/learn#views) - Il s'agit d'un moteur de templating très basique qui fait partie du noyau. Il n'est pas recommandé de l'utiliser si vous avez plus de quelques pages dans votre projet.
- [latte/latte](/awesome-plugins/latte) - Latte est un moteur de templating complet, très facile à utiliser et dont la syntaxe se rapproche davantage du PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.
- [twig/twig](/awesome-plugins/twig) - Twig est un moteur de templates flexible, rapide et sécurisé (le même que celui utilisé par Symfony). Les outils d'IA et de nombreux développeurs PHP le connaissent bien, il échappe automatiquement la sortie par défaut et possède un vaste écosystème d'extensions.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate est un puissant moteur de templates PHP avec compilation d'actifs, héritage de templates et traitement de variables. Il offre la minification automatique du CSS/JS, la mise en cache, l'encodage Base64 et une intégration facultative au framework Flight PHP.

## Intégration WordPress

Vous voulez utiliser Flight dans votre projet WordPress ? Il existe un plugin pratique pour cela !

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Ce plugin WordPress vous permet d'exécuter Flight directement aux côtés de WordPress. Il est parfait pour ajouter des API personnalisées, des microservices, ou même des applications complètes à votre site WordPress en utilisant le framework Flight. Très utile si vous voulez le meilleur des deux mondes !

## Contribuer

Vous avez un plugin à partager ? Soumettez une pull request pour l'ajouter à la liste !