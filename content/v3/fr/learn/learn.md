# Découvrir Flight

Flight est un framework rapide, simple et extensible pour PHP. Il est très polyvalent et peut être utilisé pour construire tout type d'application web. 
Il est conçu avec la simplicité en tête et écrit de manière à être facile à comprendre et à utiliser.

> **Note :** Vous verrez des exemples qui utilisent `Flight::` comme une variable statique et d'autres qui utilisent l'objet Engine `$app->`. Les deux fonctionnent de manière interchangeable avec l'autre. `$app` et `$this->app` dans un contrôleur/middleware est l'approche recommandée par l'équipe Flight.

## Composants de base

### [Routage](/learn/routing)

Apprenez à gérer les routes pour votre application web. Cela inclut également le groupement de routes, les paramètres de route et les middleware.

### [Middleware](/learn/middleware)

Apprenez à utiliser les middleware pour filtrer les requêtes et les réponses dans votre application.

### [Autoloading](/learn/autoloading)

Apprenez à charger automatiquement vos propres classes dans votre application.

### [Requêtes](/learn/requests)

Apprenez à gérer les requêtes et les réponses dans votre application.

### [Réponses](/learn/responses)

Apprenez à envoyer des réponses à vos utilisateurs.

### [Modèles HTML](/learn/templates)

Apprenez à utiliser le moteur de vue intégré pour rendre vos modèles HTML.

### [Sécurité](/learn/security)

Apprenez à sécuriser votre application contre les menaces de sécurité courantes.

### [Configuration](/learn/configuration)

Apprenez à configurer le framework pour votre application.

### [Gestionnaire d'événements](/learn/events)

Apprenez à utiliser le système d'événements pour ajouter des événements personnalisés à votre application.

### [Extension de Flight](/learn/extending)

Apprenez à étendre le framework en ajoutant vos propres méthodes et classes.

### [Crochets de méthodes et filtrage](/learn/filtering)

Apprenez à ajouter des crochets d'événements à vos méthodes et aux méthodes internes du framework.

### [Conteneur d'injection de dépendances (DIC)](/learn/dependency-injection-container)

Apprenez à utiliser les conteneurs d'injection de dépendances (DIC) pour gérer les dépendances de votre application.

## Classes utilitaires

### [Collections](/learn/collections)

Les collections sont utilisées pour stocker des données et y accéder comme un tableau ou un objet pour plus de simplicité.

### [Wrapper JSON](/learn/json)

Cela comprend quelques fonctions simples pour rendre l'encodage et le décodage de votre JSON cohérents.

### [Wrapper PDO](/learn/pdo-wrapper)

PDO peut parfois causer plus de maux de tête que nécessaire. Cette classe wrapper simple peut rendre l'interaction avec votre base de données significativement plus facile.

### [Gestionnaire de fichiers téléchargés](/learn/uploaded-file)

Une classe simple pour aider à gérer les fichiers téléchargés et à les déplacer vers un emplacement permanent.

## Concepts importants

### [Pourquoi un framework ?](/learn/why-frameworks)

Voici un court article sur pourquoi vous devriez utiliser un framework. Il est une bonne idée de comprendre les avantages d'utiliser un framework avant de commencer à en utiliser un.

De plus, un excellent tutoriel a été créé par [@lubiana](https://git.php.fail/lubiana). Bien qu'il n'entre pas dans les détails spécifiques sur Flight, 
ce guide vous aidera à comprendre certains des concepts majeurs entourant un framework et pourquoi ils sont bénéfiques à utiliser. 
Vous pouvez trouver le tutoriel [ici](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight comparé à d'autres frameworks](/learn/flight-vs-another-framework)

Si vous migrez d'un autre framework comme Laravel, Slim, Fat-Free ou Symfony vers Flight, cette page vous aidera à comprendre les différences entre les deux.

## Autres sujets

### [Tests unitaires](/learn/unit-testing)

Suivez ce guide pour apprendre à tester vos unités de code Flight de manière solide.

### [IA et expérience développeur](/learn/ai)

Apprenez comment Flight fonctionne avec les outils d'IA et les flux de travail modernes des développeurs pour vous aider à coder plus rapidement et plus intelligemment.

### [Migration v2 -> v3](/learn/migrating-to-v3)

La compatibilité descendante a été maintenue dans l'ensemble, mais il y a certains changements dont vous devriez être conscient lors de la migration de v2 à v3.