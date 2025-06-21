# Système d'événements dans Flight PHP (v3.15.0+)

Flight PHP introduit un système d'événements léger et intuitif qui vous permet d'enregistrer et de déclencher des événements personnalisés dans votre application. Avec l'ajout de `Flight::onEvent()` et `Flight::triggerEvent()`, vous pouvez maintenant vous connecter à des moments clés du cycle de vie de votre application ou définir vos propres événements pour rendre votre code plus modulaire et extensible. Ces méthodes font partie des **méthodes mappables** de Flight, ce qui signifie que vous pouvez surcharger leur comportement pour qu'il corresponde à vos besoins.

Ce guide couvre tout ce que vous devez savoir pour commencer avec les événements, y compris pourquoi ils sont précieux, comment les utiliser et des exemples pratiques pour aider les débutants à comprendre leur puissance.

## Pourquoi utiliser les événements ?

Les événements vous permettent de séparer les différentes parties de votre application afin qu'elles ne dépendent pas trop les unes des autres. Cette séparation — souvent appelée **découplage** — rend votre code plus facile à mettre à jour, à étendre ou à déboguer. Au lieu d'écrire tout dans un seul gros bloc, vous pouvez diviser votre logique en pièces plus petites et indépendantes qui répondent à des actions spécifiques (événements).

Imaginez que vous construisez une application de blog :
- Lorsque un utilisateur publie un commentaire, vous pourriez vouloir :
  - Enregistrer le commentaire dans la base de données.
  - Envoyer un e-mail au propriétaire du blog.
  - Enregistrer l'action pour des raisons de sécurité.

Sans événements, vous entasseriez tout cela dans une seule fonction. Avec les événements, vous pouvez le diviser : une partie enregistre le commentaire, une autre déclenche un événement comme `'comment.posted'`, et des écouteurs séparés gèrent l'e-mail et l'enregistrement. Cela garde votre code plus propre et vous permet d'ajouter ou de supprimer des fonctionnalités (comme les notifications) sans toucher à la logique principale.

### Utilisations courantes
- **Enregistrement** : Enregistrer des actions comme les connexions ou les erreurs sans encombrer votre code principal.
- **Notifications** : Envoyer des e-mails ou des alertes lorsqu'il se produit quelque chose.
- **Mises à jour** : Rafraîchir les caches ou notifier d'autres systèmes des changements.

## Enregistrement d'écouteurs d'événements

Pour écouter un événement, utilisez `Flight::onEvent()`. Cette méthode vous permet de définir ce qui doit se produire lorsque l'événement se produit.

### Syntaxe
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event` : Un nom pour votre événement (par exemple, `'user.login'`).
- `$callback` : La fonction à exécuter lorsque l'événement est déclenché.  // Cette ligne est un commentaire, donc elle est traduite

### Comment ça fonctionne
Vous "vous abonnez" à un événement en indiquant à Flight ce qu'il faut faire lorsqu'il se produit. Le rappel peut accepter des arguments passés depuis le déclencheur d'événement.

Le système d'événements de Flight est synchrone, ce qui signifie que chaque écouteur d'événement est exécuté en séquence, l'un après l'autre. Lorsque vous déclenchez un événement, tous les écouteurs enregistrés pour cet événement s'exécutent jusqu'à leur achèvement avant que votre code ne continue. Cela est important à comprendre car cela diffère des systèmes d'événements asynchrones où les écouteurs pourraient s'exécuter en parallèle ou plus tard.

### Exemple simple
```php
Flight::onEvent('user.login', function ($username) {
    echo "Bienvenue, $username !";  // Cette ligne est un commentaire, donc elle est traduite
});
```
Ici, lorsque l'événement `'user.login'` est déclenché, il salue l'utilisateur par son nom.

### Points clés
- Vous pouvez ajouter plusieurs écouteurs au même événement — ils s'exécutent dans l'ordre dans lequel vous les avez enregistrés.
- Le rappel peut être une fonction, une fonction anonyme ou une méthode d'une classe.

## Déclenchement d'événements

Pour faire se produire un événement, utilisez `Flight::triggerEvent()`. Cela indique à Flight d'exécuter tous les écouteurs enregistrés pour cet événement, en transmettant les données que vous fournissez.

### Syntaxe
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event` : Le nom de l'événement que vous déclenchez (doit correspondre à un événement enregistré).
- `...$args` : Arguments optionnels à envoyer aux écouteurs (peut être n'importe quel nombre d'arguments).

### Exemple simple
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Cela déclenche l'événement `'user.login'` et envoie `'alice'` à l'écouteur que nous avons défini plus tôt, ce qui affichera : `Bienvenue, alice !`.  // Cette ligne est un commentaire, donc elle est traduite

### Points clés
- Si aucun écouteur n'est enregistré, rien ne se produit — votre application ne se casse pas.
- Utilisez l'opérateur de propagation (`...`) pour passer plusieurs arguments de manière flexible.

### Enregistrement d'écouteurs d'événements

...

**Arrêter les écouteurs suivants** :
Si un écouteur retourne `false`, aucun écouteur supplémentaire pour cet événement ne sera exécuté. Cela vous permet d'arrêter la chaîne d'événements en fonction de conditions spécifiques. Souvenez-vous, l'ordre des écouteurs compte, car le premier à retourner `false` arrêtera les autres.

**Exemple** :
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {  // Cette ligne est un commentaire, donc elle est traduite
        logoutUser($username);
        return false; // Arrête les écouteurs suivants  // Cette ligne est un commentaire, donc elle est traduite
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // ceci n'est jamais envoyé  // Cette ligne est un commentaire, donc elle est traduite
});
```

## Surcharge des méthodes d'événements

`Flight::onEvent()` et `Flight::triggerEvent()` peuvent être [étendus](/learn/extending), ce qui signifie que vous pouvez redéfinir leur fonctionnement. C'est idéal pour les utilisateurs avancés qui souhaitent personnaliser le système d'événements, comme ajouter un enregistrement ou modifier la manière dont les événements sont dispatchés.

### Exemple : Personnalisation de `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Enregistrer chaque enregistrement d'événement  // Cette ligne est un commentaire, donc elle est traduite
    error_log("Nouvel écouteur d'événement ajouté pour : $event");  // Cette ligne est un commentaire, donc elle est traduite
    // Appeler le comportement par défaut (en supposant un système d'événements interne)
    Flight::_onEvent($event, $callback);
});
```
Maintenant, chaque fois que vous enregistrez un événement, il l'enregistre avant de procéder.

### Pourquoi surcharger ?
- Ajouter du débogage ou de la surveillance.
- Restreindre les événements dans certains environnements (par exemple, désactiver en test).
- Intégrer avec une bibliothèque d'événements différente.

## Où placer vos événements

En tant que débutant, vous vous demandez peut-être : *où dois-je enregistrer tous ces événements dans mon application ?* La simplicité de Flight signifie qu'il n'y a pas de règle stricte — vous pouvez les placer où cela a du sens pour votre projet. Cependant, les garder organisés vous aide à maintenir votre code à mesure que votre application grandit. Voici quelques options pratiques et des meilleures pratiques, adaptées à la nature légère de Flight :

### Option 1 : Dans votre fichier principal `index.php`
Pour les petites applications ou les prototypes rapides, vous pouvez enregistrer les événements directement dans votre fichier `index.php` aux côtés de vos routes. Cela garde tout au même endroit, ce qui est bien lorsque la simplicité est votre priorité.

```php
require 'vendor/autoload.php';

// Enregistrer les événements  // Cette ligne est un commentaire, donc elle est traduite
Flight::onEvent('user.login', function ($username) {
    error_log("$username s'est connecté à " . date('Y-m-d H:i:s'));  // Cette ligne est un commentaire, donc elle est traduite
});

// Définir les routes
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Connecté !";
});

Flight::start();
```
- **Avantages** : Simple, pas de fichiers supplémentaires, idéal pour les petits projets.
- **Inconvénients** : Peut devenir désordonné à mesure que votre application grandit avec plus d'événements et de routes.

### Option 2 : Un fichier séparé `events.php`
Pour une application légèrement plus grande, envisagez de déplacer les enregistrements d'événements dans un fichier dédié comme `app/config/events.php`. Incluez ce fichier dans votre `index.php` avant vos routes. Cela imite la manière dont les routes sont souvent organisées dans `app/config/routes.php` dans les projets Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username s'est connecté à " . date('Y-m-d H:i:s'));  // Cette ligne est un commentaire, donc elle est traduite
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "E-mail envoyé à $email : Bienvenue, $name !";  // Cette ligne est un commentaire, donc elle est traduite
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Connecté !";
});

Flight::start();
```
- **Avantages** : Garde `index.php` axé sur le routage, organise les événements de manière logique, facile à trouver et à éditer.
- **Inconvénients** : Ajoute un peu de structure, ce qui pourrait sembler excessif pour les très petites applications.

### Option 3 : Près de l'endroit où ils sont déclenchés
Une autre approche consiste à enregistrer les événements près de l'endroit où ils sont déclenchés, comme à l'intérieur d'un contrôleur ou d'une définition de route. Cela fonctionne bien si un événement est spécifique à une partie de votre application.

```php
Flight::route('/signup', function () {
    // Enregistrer l'événement ici  // Cette ligne est un commentaire, donc elle est traduite
    Flight::onEvent('user.registered', function ($email) {
        echo "E-mail de bienvenue envoyé à $email !";  // Cette ligne est un commentaire, donc elle est traduite
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Inscrit !";
});
```
- **Avantages** : Garde le code lié ensemble, bon pour les fonctionnalités isolées.
- **Inconvénients** : Éparpille les enregistrements d'événements, rendant plus difficile de voir tous les événements d'un coup ; risque de duplications si l'on n'est pas prudent.

### Meilleure pratique pour Flight
- **Commencer simplement** : Pour les petites applications, placez les événements dans `index.php`. C'est rapide et s'aligne sur le minimalisme de Flight.
- **Grandir intelligemment** : À mesure que votre application s'étend (par exemple, plus de 5-10 événements), utilisez un fichier `app/config/events.php`. C'est une étape naturelle, comme l'organisation des routes, et cela garde votre code ordonné sans ajouter de cadres complexes.
- **Éviter la sur-ingénierie** : Ne créez pas une classe ou un répertoire "gestionnaire d'événements" complet à moins que votre application ne devienne énorme — Flight excelle dans la simplicité, donc gardez-le léger.

### Astuce : Grouper par objectif
Dans `events.php`, groupez les événements liés (par exemple, tous les événements liés aux utilisateurs ensemble) avec des commentaires pour plus de clarté :

```php
// app/config/events.php
// Événements utilisateur  // Cette ligne est un commentaire, donc elle est traduite
Flight::onEvent('user.login', function ($username) {
    error_log("$username s'est connecté");  // Cette ligne est un commentaire, donc elle est traduite
});
Flight::onEvent('user.registered', function ($email) {
    echo "Bienvenue à $email !";  // Cette ligne est un commentaire, donc elle est traduite
});

// Événements page  // Cette ligne est un commentaire, donc elle est traduite
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // Effacer le cache de session si applicable  // Cette ligne est un commentaire, donc elle est traduite
});
```

Cette structure s'adapte bien et reste conviviale pour les débutants.

## Exemples pour les débutants

Parlons de quelques scénarios du monde réel pour montrer comment fonctionnent les événements et pourquoi ils sont utiles.

### Exemple 1 : Enregistrer une connexion d'utilisateur
```php
// Étape 1 : Enregistrer un écouteur  // Cette ligne est un commentaire, donc elle est traduite
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username s'est connecté à $time");  // Cette ligne est un commentaire, donc elle est traduite
});

// Étape 2 : Le déclencher dans votre application
Flight::route('/login', function () {
    $username = 'bob'; // Prétendez que cela vient d'un formulaire
    Flight::triggerEvent('user.login', $username);
    echo "Bonjour, $username !";
});
```
**Pourquoi c'est utile** : Le code de connexion n'a pas besoin de savoir sur l'enregistrement — il déclenche simplement l'événement. Vous pouvez plus tard ajouter d'autres écouteurs (par exemple, envoyer un e-mail de bienvenue) sans modifier la route.

### Exemple 2 : Notifier sur de nouveaux utilisateurs
```php
// Écouteur pour les nouvelles inscriptions  // Cette ligne est un commentaire, donc elle est traduite
Flight::onEvent('user.registered', function ($email, $name) {
    // Simuler l'envoi d'un e-mail
    echo "E-mail envoyé à $email : Bienvenue, $name !";  // Cette ligne est un commentaire, donc elle est traduite
});

// Le déclencher lorsqu'une personne s'inscrit
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Merci de vous être inscrit !";
});
```
**Pourquoi c'est utile** : La logique d'inscription se concentre sur la création de l'utilisateur, tandis que l'événement gère les notifications. Vous pourriez ajouter plus d'écouteurs (par exemple, enregistrer l'inscription) plus tard.

### Exemple 3 : Effacer un cache
```php
// Écouteur pour effacer un cache  // Cette ligne est un commentaire, donc elle est traduite
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Effacer le cache de session si applicable  // Cette ligne est un commentaire, donc elle est traduite
    echo "Cache effacé pour la page $pageId.";
});

// Le déclencher lorsqu'une page est éditée
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Prétendez que nous avons mis à jour la page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId mise à jour.";
});
```
**Pourquoi c'est utile** : Le code d'édition ne se soucie pas du cache — il signale simplement la mise à jour. D'autres parties de l'application peuvent réagir si nécessaire.

## Meilleures pratiques

- **Nommez les événements clairement** : Utilisez des noms spécifiques comme `'user.login'` ou `'page.updated'` pour qu'il soit évident de ce dont il s'agit.
- **Gardez les écouteurs simples** : Ne mettez pas de tâches lentes ou complexes dans les écouteurs — gardez votre application rapide.
- **Testez vos événements** : Déclenchez-les manuellement pour vous assurer que les écouteurs fonctionnent comme prévu.
- **Utilisez les événements avec sagesse** : Ils sont excellents pour le découplage, mais trop d'entre eux peuvent rendre votre code difficile à suivre — utilisez-les quand cela a du sens.

Le système d'événements dans Flight PHP, avec `Flight::onEvent()` et `Flight::triggerEvent()`, vous offre un moyen simple mais puissant de construire des applications flexibles. En permettant à différentes parties de votre application de communiquer via des événements, vous pouvez garder votre code organisé, réutilisable et facile à étendre. Que vous enregistriez des actions, envoyiez des notifications ou gériez des mises à jour, les événements vous aident à le faire sans emmêler votre logique. De plus, avec la possibilité de surcharger ces méthodes, vous avez la liberté d'adapter le système à vos besoins. Commencez petit avec un seul événement et observez comment cela transforme la structure de votre application !

## Événements intégrés

Flight PHP est livré avec quelques événements intégrés que vous pouvez utiliser pour vous connecter au cycle de vie du framework. Ces événements sont déclenchés à des points spécifiques du cycle de demande/réponse, vous permettant d'exécuter une logique personnalisée lorsque certaines actions se produisent.

### Liste des événements intégrés
- **flight.request.received** : `function(Request $request)` Déclenché lorsque une demande est reçue, analysée et traitée.
- **flight.error** : `function(Throwable $exception)` Déclenché lorsqu'une erreur se produit pendant le cycle de demande.
- **flight.redirect** : `function(string $url, int $status_code)` Déclenché lorsqu'une redirection est initiée.
- **flight.cache.checked** : `function(string $cache_key, bool $hit, float $executionTime)` Déclenché lorsque le cache est vérifié pour une clé spécifique et si le cache a touché ou non.
- **flight.middleware.before** : `function(Route $route)` Déclenché après l'exécution du middleware before.
- **flight.middleware.after** : `function(Route $route)` Déclenché après l'exécution du middleware after.
- **flight.middleware.executed** : `function(Route $route, $middleware, string $method, float $executionTime)` Déclenché après l'exécution de n'importe quel middleware.
- **flight.route.matched** : `function(Route $route)` Déclenché lorsqu'une route est correspondante, mais pas encore exécutée.
- **flight.route.executed** : `function(Route $route, float $executionTime)` Déclenché après l'exécution d'une route et son traitement. `$executionTime` est le temps qu'il a fallu pour exécuter la route (appeler le contrôleur, etc.).
- **flight.view.rendered** : `function(string $template_file_path, float $executionTime)` Déclenché après qu'une vue est rendue. `$executionTime` est le temps qu'il a fallu pour rendre le modèle. **Note : Si vous surchargez la méthode `render`, vous devrez re-déclencher cet événement.**  // Cette ligne est un commentaire, donc elle est traduite
- **flight.response.sent** : `function(Response $response, float $executionTime)` Déclenché après qu'une réponse est envoyée au client. `$executionTime` est le temps qu'il a fallu pour construire la réponse.