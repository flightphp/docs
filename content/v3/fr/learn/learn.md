# Apprenez à connaître Flight

Flight est un framework rapide, simple et extensible pour PHP. Il est très polyvalent et peut être utilisé pour construire tout type d'application web. 
Il est conçu avec la simplicité à l'esprit et est écrit de manière facile à comprendre et à utiliser.

> **Note :** Vous verrez des exemples qui utilisent `Flight::` comme une variable statique et d'autres qui utilisent l'objet Engine `$app->`. Les deux fonctionnent de manière interchangeable l'un avec l'autre. `$app` et `$this->app` dans un contrôleur/middleware est l'approche recommandée par l'équipe Flight.

## Composants principaux

### [Routing](/learn/routing)

Apprenez à gérer les routes pour votre application web. Cela inclut également le groupement des routes, les paramètres de route et les middlewares.

### [Middleware](/learn/middleware)

Apprenez à utiliser les middlewares pour filtrer les requêtes et les réponses dans votre application.

### [Autoloading](/learn/autoloading)

Apprenez à charger automatiquement vos propres classes dans votre application.

### [Requests](/learn/requests)

Apprenez à gérer les requêtes et les réponses dans votre application.

### [Responses](/learn/responses)

Apprenez à envoyer des réponses à vos utilisateurs.

### [HTML Templates](/learn/templates)

Apprenez à utiliser le moteur de vue intégré pour rendre vos templates HTML.

### [Security](/learn/security)

Apprenez à sécuriser votre application contre les menaces de sécurité courantes.

### [Configuration](/learn/configuration)

Apprenez à configurer le framework pour votre application.

### [Event Manager](/learn/events)

Apprenez à utiliser le système d'événements pour ajouter des événements personnalisés à votre application.

### [Extending Flight](/learn/extending)

Apprenez à étendre le framework en ajoutant vos propres méthodes et classes.

### [Method Hooks and Filtering](/learn/filtering)

Apprenez à ajouter des hooks d'événements à vos méthodes et aux méthodes internes du framework.

### [Dependency Injection Container (DIC)](/learn/dependency-injection-container)

Apprenez à utiliser les conteneurs d'injection de dépendances (DIC) pour gérer les dépendances de votre application.

## Classes utilitaires

### [Collections](/learn/collections)

Les collections sont utilisées pour stocker des données et y accéder comme un tableau ou comme un objet pour plus de facilité d'utilisation.

### [JSON Wrapper](/learn/json)

Cela comprend quelques fonctions simples pour rendre l'encodage et le décodage de votre JSON cohérents.

### [SimplePdo](/learn/simple-pdo)

PDO peut parfois causer plus de maux de tête que nécessaire. SimplePdo est une classe d'aide PDO moderne avec des méthodes pratiques comme `insert()`, `update()`, `delete()`, et `transaction()` pour rendre les opérations de base de données beaucoup plus faciles.

### [PdoWrapper](/learn/pdo-wrapper) (Déprécié)

Le wrapper PDO original est déprécié à partir de la v3.18.0. Veuillez utiliser [SimplePdo](/learn/simple-pdo) à la place.

### [Uploaded File Handler](/learn/uploaded-file)

Une classe simple pour aider à gérer les fichiers téléchargés et à les déplacer vers un emplacement permanent.

## Concepts importants

### [Why a Framework?](/learn/why-frameworks)

Voici un court article sur pourquoi vous devriez utiliser un framework. Il est une bonne idée de comprendre les avantages d'utiliser un framework avant de commencer à en utiliser un.

De plus, un excellent tutoriel a été créé par [@lubiana](https://git.php.fail/lubiana). Bien qu'il n'entre pas dans les détails spécifiques sur Flight, 
ce guide vous aidera à comprendre certains des concepts majeurs entourant un framework et pourquoi ils sont bénéfiques à utiliser. 
Vous pouvez trouver le tutoriel [ici](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Compared to Other Frameworks](/learn/flight-vs-another-framework)

Si vous migrez d'un autre framework comme Laravel, Slim, Fat-Free ou Symfony vers Flight, cette page vous aidera à comprendre les différences entre les deux.

## Autres sujets

### [Unit Testing](/learn/unit-testing)

Suivez ce guide pour apprendre à tester vos unités de code Flight de manière solide.

### [AI & Developer Experience](/learn/ai)

Apprenez comment Flight fonctionne avec les outils d'IA et les flux de travail de développement modernes pour vous aider à coder plus rapidement et plus intelligemment.

### [Migrating v2 -> v3](/learn/migrating-to-v3)

La compatibilité ascendante a été maintenue dans l'ensemble, mais il y a certains changements dont vous devriez être conscient lors de la migration de v2 vers v3.