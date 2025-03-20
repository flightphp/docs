# Système d'Événements dans Flight PHP (v3.15.0+)

Flight PHP introduit un système d'événements léger et intuitif qui vous permet d'enregistrer et de déclencher des événements personnalisés dans votre application. Avec l'ajout de `Flight::onEvent()` et `Flight::triggerEvent()`, vous pouvez désormais vous accrocher à des moments clés du cycle de vie de votre application ou définir vos propres événements pour rendre votre code plus modulaire et extensible. Ces méthodes font partie des **méthodes mappables** de Flight, ce qui signifie que vous pouvez remplacer leur comportement pour répondre à vos besoins.

Ce guide couvre tout ce que vous devez savoir pour commencer avec les événements, y compris pourquoi ils sont précieux, comment les utiliser, et des exemples pratiques pour aider les débutants à comprendre leur puissance.

## Pourquoi Utiliser des Événements ?

Les événements vous permettent de séparer différentes parties de votre application afin qu'elles ne dépendent pas trop les unes des autres. Cette séparation—souvent appelée **découplage**—rend votre code plus facile à mettre à jour, à étendre ou à déboguer. Au lieu d'écrire tout dans un gros morceau, vous pouvez diviser votre logique en pièces plus petites et indépendantes qui réagissent à des actions spécifiques (événements).

Imaginez que vous construisez une application de blog :
- Lorsqu'un utilisateur publie un commentaire, vous pourriez vouloir :
  - Sauvegarder le commentaire dans la base de données.
  - Envoyer un e-mail au propriétaire du blog.
  - Journaliser l'action pour des raisons de sécurité.

Sans événements, vous entasseriez tout cela dans une seule fonction. Avec des événements, vous pouvez le diviser : une partie sauvegarde le commentaire, une autre déclenche un événement comme `'comment.posted'`, et des écouteurs séparés gèrent l'e-mail et la journalisation. Cela garde votre code plus propre et vous permet d'ajouter ou de supprimer des fonctionnalités (comme des notifications) sans toucher à la logique principale.

### Utilisations Courantes
- **Journalisation** : Enregistrer des actions comme des connexions ou des erreurs sans encombrer votre code principal.
- **Notifications** : Envoyer des e-mails ou des alertes lorsqu'un événement se produit.
- **Mises à jour** : Actualiser des caches ou informer d'autres systèmes des modifications.

## Enregistrement des Écouteurs d'Événements

Pour écouter un événement, utilisez `Flight::onEvent()`. Cette méthode vous permet de définir ce qui doit se produire lorsqu'un événement se produit.

### Syntaxe
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event` : Un nom pour votre événement (ex. : `'user.login'`).
- `$callback` : La fonction à exécuter lorsque l'événement est déclenché.

### Comment Ça Marche
Vous "vous abonnez" à un événement en disant à Flight ce qu'il doit faire lorsqu'il se produit. Le rappel peut accepter des arguments passés par le déclenchement de l'événement.

Le système d'événements de Flight est synchrone, ce qui signifie que chaque écouteur d'événement est exécuté en séquence, l'un après l'autre. Lorsque vous déclenchez un événement, tous les écouteurs enregistrés pour cet événement s'exécuteront jusqu'à leur achèvement avant que votre code ne continue. Il est important de comprendre cela car cela diffère des systèmes d'événements asynchrones où les écouteurs peuvent s'exécuter en parallèle ou à un moment ultérieur.

### Exemple Simple
```php
Flight::onEvent('user.login', function ($username) {
    echo "Bienvenue de nouveau, $username!";
});
```
Ici, lorsque l'événement `'user.login'` est déclenché, il accueillera l'utilisateur par son nom.

### Points Clés
- Vous pouvez ajouter plusieurs écouteurs au même événement ; ils s'exécuteront dans l'ordre où vous les avez enregistrés.
- Le rappel peut être une fonction, une fonction anonyme ou une méthode d'une classe.

## Déclenchement des Événements

Pour faire se produire un événement, utilisez `Flight::triggerEvent()`. Cela indique à Flight d'exécuter tous les écouteurs enregistrés pour cet événement, en passant toute donnée que vous fournissez.

### Syntaxe
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event` : Le nom de l'événement que vous déclenchez (doit correspondre à un événement enregistré).
- `...$args` : Arguments optionnels à envoyer aux écouteurs (peut être n'importe quel nombre d'arguments).

### Exemple Simple
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Cela déclenche l'événement `'user.login'` et envoie `'alice'` à l'écouteur que nous avons défini plus tôt, qui affichera : `Bienvenue de nouveau, alice!`.

### Points Clés
- Si aucun écouteur n'est enregistré, rien ne se passe ; votre application ne planterait pas.
- Utilisez l'opérateur de propagation (`...`) pour passer plusieurs arguments de manière flexible.

### Enregistrement des Écouteurs d'Événements

...

**Arrêter d'autres Écouteurs** :
Si un écouteur retourne `false`, aucun autre écouteur pour cet événement ne sera exécuté. Cela vous permet d'arrêter la chaîne d'événements en fonction de conditions spécifiques. Rappelez-vous, l'ordre des écouteurs est important, car le premier à retourner `false` arrêtera le reste.

**Exemple** :
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Arrête les écouteurs suivants
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // cela n'est jamais envoyé
});
```

## Surcharge des Méthodes d'Événements

`Flight::onEvent()` et `Flight::triggerEvent()` sont disponibles pour être [étendus](/learn/extending), ce qui signifie que vous pouvez redéfinir comment ils fonctionnent. C'est idéal pour les utilisateurs avancés qui souhaitent personnaliser le système d'événements, comme l'ajout de journalisation ou modifier la façon dont les événements sont dispatchés.

### Exemple : Personnalisation de `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Journaliser chaque enregistrement d'événement
    error_log("Nouveau listener d'événement ajouté pour : $event");
    // Appeler le comportement par défaut (supposant un système d'événements interne)
    Flight::_onEvent($event, $callback);
});
```
Maintenant, chaque fois que vous enregistrez un événement, il le journalise avant de continuer.

### Pourquoi Surcharger ?
- Ajouter la journalisation ou le monitoring.
- Restreindre les événements dans certains environnements (ex. : désactiver lors des tests).
- Intégrer une autre bibliothèque d'événements.

## Où Placer Vos Événements

En tant que débutant, vous vous demandez peut-être : *où enregistrer tous ces événements dans mon application ?* La simplicité de Flight signifie qu'il n'y a pas de règle stricte : vous pouvez les mettre où cela a du sens pour votre projet. Cependant, les garder organisés vous aide à maintenir votre code au fur et à mesure de la croissance de votre application. Voici quelques options pratiques et des meilleures pratiques, adaptées à la légèreté de Flight :

### Option 1 : Dans Votre Fichier Principal `index.php`
Pour de petites applications ou des prototypes rapides, vous pouvez enregistrer des événements directement dans votre fichier `index.php` aux côtés de vos routes. Cela garde tout en un seul endroit, ce qui est acceptable lorsque la simplicité est votre priorité.

```php
require 'vendor/autoload.php';

// Enregistrer des événements
Flight::onEvent('user.login', function ($username) {
    error_log("$username connecté à " . date('Y-m-d H:i:s'));
});

// Définir les routes
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Connecté!";
});

Flight::start();
```
- **Avantages** : Simple, pas de fichiers supplémentaires, idéal pour de petits projets.
- **Inconvénients** : Peut devenir désordonné à mesure que votre application se développe avec plus d'événements et de routes.

### Option 2 : Un Fichier Séparé `events.php`
Pour une application un peu plus grande, envisagez de déplacer les enregistrements d'événements dans un fichier dédié comme `app/config/events.php`. Incluez ce fichier dans votre `index.php` avant vos routes. Cela imite comment les routes sont souvent organisées dans `app/config/routes.php` dans les projets Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username connecté à " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "E-mail envoyé à $email : Bienvenue, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Connecté!";
});

Flight::start();
```
- **Avantages** : Garde `index.php` concentré sur le routage, organise les événements de manière logique, facile à trouver et à modifier.
- **Inconvénients** : Ajoute un tout petit peu de structure, ce qui pourrait sembler excessif pour de très petites applications.

### Option 3 : Près de Leur Déclenchement
Une autre approche consiste à enregistrer les événements près de leur déclenchement, comme à l'intérieur d'un contrôleur ou d'une définition de route. Cela fonctionne bien si un événement est spécifique à une partie de votre application.

```php
Flight::route('/signup', function () {
    // Enregistrer l'événement ici
    Flight::onEvent('user.registered', function ($email) {
        echo "E-mail de bienvenue envoyé à $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Inscrit!";
});
```
- **Avantages** : Garde le code lié ensemble, bon pour des fonctionnalités isolées.
- **Inconvénients** : Éparpille les enregistrements d'événements, ce qui rend plus difficile de voir tous les événements à la fois ; risque d'enregistrements en double si l'on n'y fait pas attention.

### Meilleure Pratique pour Flight
- **Commencez Simple** : Pour les petites applications, placez les événements dans `index.php`. C'est rapide et s'aligne avec le minimalisme de Flight.
- **Croissez Intelligent** : À mesure que votre application s'agrandit (ex. : plus de 5-10 événements), utilisez un fichier `app/config/events.php`. C'est une étape logique, comme l'organisation des routes, et cela garde votre code propre sans ajouter de frameworks complexes.
- **Évitez la Sur- Ingénierie** : Ne créez pas une classe ou un répertoire complet "gestionnaire d'événements" à moins que votre application ne devienne énorme—Flight prospère grâce à la simplicité, alors gardez-le léger.

### Conseil : Groupez par Objectif
Dans `events.php`, groupez les événements connexes (ex. : tous les événements liés à l'utilisateur ensemble) avec des commentaires pour plus de clarté :

```php
// app/config/events.php
// Événements Utilisateur
Flight::onEvent('user.login', function ($username) {
    error_log("$username connecté");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Bienvenue à $email!";
});

// Événements de Page
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Cette structure se développe bien et reste conviviale pour les débutants.

## Exemples pour Débutants

Passons en revue quelques scénarios du monde réel pour montrer comment les événements fonctionnent et pourquoi ils sont utiles.

### Exemple 1 : Journaliser une Connexion Utilisateur
```php
// Étape 1 : Enregistrer un écouteur
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username connecté à $time");
});

// Étape 2 : Déclencher cela dans votre application
Flight::route('/login', function () {
    $username = 'bob'; // Supposons que cela provienne d'un formulaire
    Flight::triggerEvent('user.login', $username);
    echo "Salut, $username!";
});
```
**Pourquoi C'est Utile** : Le code de connexion n'a pas besoin de savoir sur la journalisation—il déclenche simplement l'événement. Vous pouvez plus tard ajouter d'autres écouteurs (ex. : envoyer un e-mail de bienvenue) sans changer la route.

### Exemple 2 : Notifier de Nouveaux Utilisateurs
```php
// Écouteur pour les nouvelles inscriptions
Flight::onEvent('user.registered', function ($email, $name) {
    // Simuler l'envoi d'un e-mail
    echo "E-mail envoyé à $email : Bienvenue, $name!";
});

// Déclencher cela lors de l'inscription de quelqu'un
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Merci pour votre inscription!";
});
```
**Pourquoi C'est Utile** : La logique d'inscription se concentre sur la création de l'utilisateur, tandis que l'événement gère les notifications. Vous pourriez ajouter plus d'écouteurs (ex. : journaliser l'inscription) plus tard.

### Exemple 3 : Effacer un Cache
```php
// Écouteur pour effacer un cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Effacer le cache de session si applicable
    echo "Cache effacé pour la page $pageId.";
});

// Déclencher lorsque la page est modifiée
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Supposons que nous avons mis à jour la page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId mise à jour.";
});
```
**Pourquoi C'est Utile** : Le code d'édition ne se préoccupe pas du cache—il signale simplement la mise à jour. D'autres parties de l'application peuvent réagir au besoin.

## Meilleures Pratiques

- **Nommez les Événements Claire** : Utilisez des noms spécifiques comme `'user.login'` ou `'page.updated'` afin qu'il soit évident ce qu'ils font.
- **Gardez les Écouteurs Simples** : Ne mettez pas de tâches lentes ou complexes dans les écouteurs—gardez votre application rapide.
- **Testez Vos Événements** : Déclenchez-les manuellement pour vous assurer que les écouteurs fonctionnent comme prévu.
- **Utilisez les Événements Judicieusement** : Ils sont excellents pour le découplage, mais trop peuvent rendre votre code difficile à suivre—utilisez-les lorsque cela a du sens.

Le système d'événements dans Flight PHP, avec `Flight::onEvent()` et `Flight::triggerEvent()`, vous offre une manière simple mais puissante de construire des applications flexibles. En permettant aux différentes parties de votre application de communiquer entre elles par le biais d'événements, vous pouvez garder votre code organisé, réutilisable et facile à étendre. Que vous journalisiez des actions, envoyiez des notifications ou gériez des mises à jour, les événements vous aident à le faire sans enlacer votre logique. De plus, avec la capacité de remplacer ces méthodes, vous avez la liberté de personnaliser le système selon vos besoins. Commencez petit avec un seul événement, et regardez comment cela transforme la structure de votre application !

## Événements Intégrés

Flight PHP propose quelques événements intégrés que vous pouvez utiliser pour vous accrocher à la lifecycle du framework. Ces événements sont déclenchés à des moments spécifiques dans le cycle requête/réponse, vous permettant d'exécuter une logique personnalisée lorsque certaines actions se produisent.

### Liste des Événements Intégrés
- **flight.request.received** : `function(Request $request)` Déclenché lorsqu'une requête est reçue, analysée et traitée.
- **flight.error** : `function(Throwable $exception)` Déclenché lorsqu'une erreur se produit durant le cycle de vie de la requête.
- **flight.redirect** : `function(string $url, int $status_code)` Déclenché lorsqu'une redirection est initiée.
- **flight.cache.checked** : `function(string $cache_key, bool $hit, float $executionTime)` Déclenché lorsque le cache est vérifié pour une clé spécifique et si le cache a été atteint ou manqué.
- **flight.middleware.before** : `function(Route $route)` Déclenché après l'exécution du middleware avant.
- **flight.middleware.after** : `function(Route $route)` Déclenché après l'exécution du middleware après.
- **flight.middleware.executed** : `function(Route $route, $middleware, string $method, float $executionTime)` Déclenché après l'exécution de tout middleware.
- **flight.route.matched** : `function(Route $route)` Déclenché lorsqu'une route est correspondue, mais pas encore exécutée.
- **flight.route.executed** : `function(Route $route, float $executionTime)` Déclenché après qu'une route soit exécutée et traitée. `$executionTime` est le temps qu'il a fallu pour exécuter la route (appeler le contrôleur, etc).
- **flight.view.rendered** : `function(string $template_file_path, float $executionTime)` Déclenché après qu'une vue soit rendue. `$executionTime` est le temps qu'il a fallu pour rendre le modèle. **Remarque : Si vous remplacez la méthode `render`, vous devrez redéclencher cet événement.**
- **flight.response.sent** : `function(Response $response, float $executionTime)` Déclenché après qu'une réponse soit envoyée au client. `$executionTime` est le temps qu'il a fallu pour construire la réponse.