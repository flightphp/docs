# Gestionnaire d'événements

_à partir de la v3.15.0_

## Aperçu

Les événements vous permettent d'enregistrer et de déclencher des comportements personnalisés dans votre application. Avec l'ajout de `Flight::onEvent()` et `Flight::triggerEvent()`, vous pouvez maintenant vous accrocher à des moments clés du cycle de vie de votre application ou définir vos propres événements (comme les notifications et les e-mails) pour rendre votre code plus modulaire et extensible. Ces méthodes font partie des [méthodes mappables de Flight](/learn/extending), ce qui signifie que vous pouvez surcharger leur comportement pour répondre à vos besoins.

## Comprendre

Les événements vous permettent de séparer les différentes parties de votre application afin qu'elles ne dépendent pas trop les unes des autres. Cette séparation — souvent appelée **découplage** — rend votre code plus facile à mettre à jour, à étendre ou à déboguer. Au lieu d'écrire tout en un gros bloc, vous pouvez diviser votre logique en pièces plus petites et indépendantes qui répondent à des actions spécifiques (événements).

Imaginez que vous construisez une application de blog :
- Quand un utilisateur publie un commentaire, vous pourriez vouloir :
  - Sauvegarder le commentaire dans la base de données.
  - Envoyer un e-mail au propriétaire du blog.
  - Enregistrer l'action pour la sécurité.

Sans événements, vous entasseriez tout cela dans une seule fonction. Avec les événements, vous pouvez le diviser : une partie sauvegarde le commentaire, une autre déclenche un événement comme `'comment.posted'`, et des écouteurs séparés gèrent l'e-mail et l'enregistrement. Cela garde votre code plus propre et vous permet d'ajouter ou de supprimer des fonctionnalités (comme les notifications) sans toucher à la logique principale.

### Cas d'utilisation courants

Dans la plupart des cas, les événements sont bons pour des choses qui sont optionnelles, mais pas une partie absolument essentielle de votre système. Par exemple, les éléments suivants sont bons à avoir, mais si elles échouent pour une raison quelconque, votre application devrait toujours fonctionner :

- **Enregistrement** : Enregistrer des actions comme les connexions ou les erreurs sans encombrer votre code principal.
- **Notifications** : Envoyer des e-mails ou des alertes quand quelque chose se produit.
- **Mises à jour de cache** : Actualiser les caches ou notifier d'autres systèmes des changements.

Cependant, supposons que vous ayez une fonctionnalité de mot de passe oublié. Cela devrait faire partie de votre fonctionnalité principale et ne pas être un événement car si cet e-mail ne part pas, votre utilisateur ne peut pas réinitialiser son mot de passe et utiliser votre application.

## Utilisation de base

Le système d'événements de Flight est construit autour de deux méthodes principales : `Flight::onEvent()` pour enregistrer les écouteurs d'événements et `Flight::triggerEvent()` pour déclencher les événements. Voici comment vous pouvez les utiliser :

### Enregistrement des écouteurs d'événements

Pour écouter un événement, utilisez `Flight::onEvent()`. Cette méthode vous permet de définir ce qui doit se produire quand un événement se produit.

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event` : Un nom pour votre événement (par exemple, `'user.login'`).
- `$callback` : La fonction à exécuter quand l'événement est déclenché.

Vous "souscrivez" à un événement en indiquant à Flight ce qu'il faut faire quand cela se produit. Le rappel peut accepter des arguments passés depuis le déclencheur d'événement.

Le système d'événements de Flight est synchrone, ce qui signifie que chaque écouteur d'événement est exécuté en séquence, l'un après l'autre. Quand vous déclenchez un événement, tous les écouteurs enregistrés pour cet événement s'exécuteront jusqu'à leur achèvement avant que votre code continue. Il est important de comprendre cela car cela diffère des systèmes d'événements asynchrones où les écouteurs pourraient s'exécuter en parallèle ou plus tard.

#### Exemple simple
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";

	// you can send an email if the login is from a new location
});
```
Ici, quand l'événement `'user.login'` est déclenché, il saluera l'utilisateur par son nom et pourrait aussi inclure une logique pour envoyer un e-mail si nécessaire.

> **Note :** Le rappel peut être une fonction, une fonction anonyme, ou une méthode d'une classe.

### Déclenchement des événements

Pour faire se produire un événement, utilisez `Flight::triggerEvent()`. Cela indique à Flight d'exécuter tous les écouteurs enregistrés pour cet événement, en passant les données que vous fournissez.

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event` : Le nom de l'événement que vous déclenchez (doit correspondre à un événement enregistré).
- `...$args` : Arguments optionnels à envoyer aux écouteurs (peut être n'importe quel nombre d'arguments).

#### Exemple simple
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Cela déclenche l'événement `'user.login'` et envoie `'alice'` à l'écouteur que nous avons défini plus tôt, ce qui affichera : `Welcome back, alice!`.

- Si aucun écouteur n'est enregistré, rien ne se passe — votre application ne se cassera pas.
- Utilisez l'opérateur de propagation (`...`) pour passer plusieurs arguments de manière flexible.

### Arrêt des événements

Si un écouteur retourne `false`, aucun écouteur supplémentaire pour cet événement ne sera exécuté. Cela vous permet d'arrêter la chaîne d'événements basée sur des conditions spécifiques. Souvenez-vous, l'ordre des écouteurs compte, car le premier à retourner `false` arrêtera les autres.

**Exemple** :
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Stops subsequent listeners
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // this is never sent
});
```

### Surcharge des méthodes d'événements

`Flight::onEvent()` et `Flight::triggerEvent()` sont disponibles pour être [étendus](/learn/extending), ce qui signifie que vous pouvez redéfinir leur fonctionnement. C'est idéal pour les utilisateurs avancés qui veulent personnaliser le système d'événements, comme ajouter de l'enregistrement ou changer la façon dont les événements sont dispatchés.

#### Exemple : Personnalisation de `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Log every event registration
    error_log("New event listener added for: $event");
    // Call the default behavior (assuming an internal event system)
    Flight::_onEvent($event, $callback);
});
```
Maintenant, chaque fois que vous enregistrez un événement, il l'enregistre avant de procéder.

#### Pourquoi surcharger ?
- Ajouter du débogage ou de la surveillance.
- Restreindre les événements dans certains environnements (par exemple, désactiver en test).
- Intégrer avec une bibliothèque d'événements différente.

### Où placer vos événements

Si vous êtes nouveau aux concepts d'événements dans votre projet, vous pourriez vous demander : *où est-ce que j'enregistre tous ces événements dans mon application ?* La simplicité de Flight signifie qu'il n'y a pas de règle stricte — vous pouvez les placer où cela a du sens pour votre projet. Cependant, les garder organisés vous aide à maintenir votre code au fur et à mesure que votre application grandit. Voici quelques options pratiques et meilleures pratiques, adaptées à la nature légère de Flight :

#### Option 1 : Dans votre `index.php` principal
Pour les petites applications ou les prototypes rapides, vous pouvez enregistrer les événements directement dans votre fichier `index.php` aux côtés de vos routes. Cela garde tout au même endroit, ce qui est bien quand la simplicité est votre priorité.

```php
require 'vendor/autoload.php';

// Register events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Define routes
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Avantages** : Simple, pas de fichiers supplémentaires, idéal pour les petits projets.
- **Inconvénients** : Peut devenir désordonné au fur et à mesure que votre application grandit avec plus d'événements et de routes.

#### Option 2 : Un fichier `events.php` séparé
Pour une application légèrement plus grande, envisagez de déplacer les enregistrements d'événements dans un fichier dédié comme `app/config/events.php`. Incluez ce fichier dans votre `index.php` avant vos routes. Cela imite la façon dont les routes sont souvent organisées dans `app/config/routes.php` dans les projets Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Avantages** : Garde `index.php` focalisé sur le routage, organise les événements logiquement, facile à trouver et à modifier.
- **Inconvénients** : Ajoute un peu de structure, ce qui pourrait sembler excessif pour des applications très petites.

#### Option 3 : Près de l'endroit où ils sont déclenchés
Une autre approche est d'enregistrer les événements près de l'endroit où ils sont déclenchés, comme à l'intérieur d'un contrôleur ou d'une définition de route. Cela fonctionne bien si un événement est spécifique à une partie de votre application.

```php
Flight::route('/signup', function () {
    // Register event here
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Avantages** : Garde le code lié ensemble, bon pour des fonctionnalités isolées.
- **Inconvénients** : Disperse les enregistrements d'événements, rendant plus difficile de voir tous les événements d'un coup ; risque d'enregistrements dupliqués si pas prudent.

#### Meilleure pratique pour Flight
- **Commencez simple** : Pour les petites applications, placez les événements dans `index.php`. C'est rapide et s'aligne avec le minimalisme de Flight.
- **Grandissez intelligemment** : Au fur et à mesure que votre application s'étend (par exemple, plus de 5-10 événements), utilisez un fichier `app/config/events.php`. C'est un pas naturel, comme organiser les routes, et garde votre code ordonné sans ajouter de frameworks complexes.
- **Évitez la sur-ingénierie** : Ne créez pas une classe ou un répertoire "gestionnaire d'événements" complet sauf si votre application devient énorme — Flight prospère sur la simplicité, alors gardez-le léger.

#### Astuce : Groupez par objectif
Dans `events.php`, groupez les événements liés (par exemple, tous les événements liés aux utilisateurs ensemble) avec des commentaires pour plus de clarté :

```php
// app/config/events.php
// User Events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Page Events
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

Cette structure s'adapte bien et reste conviviale pour les débutants.

### Exemples du monde réel

Parcourons quelques scénarios du monde réel pour montrer comment fonctionnent les événements et pourquoi ils sont utiles.

#### Exemple 1 : Enregistrement d'une connexion utilisateur
```php
// Step 1: Register a listener
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Step 2: Trigger it in your app
Flight::route('/login', function () {
    $username = 'bob'; // Pretend this comes from a form
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Pourquoi c'est utile** : Le code de connexion n'a pas besoin de savoir pour l'enregistrement — il déclenche juste l'événement. Vous pouvez plus tard ajouter plus d'écouteurs (par exemple, envoyer un e-mail de bienvenue) sans changer la route.

#### Exemple 2 : Notification de nouveaux utilisateurs
```php
// Listener for new registrations
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulate sending an email
    echo "Email sent to $email: Welcome, $name!";
});

// Trigger it when someone signs up
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Pourquoi c'est utile** : La logique d'inscription se concentre sur la création de l'utilisateur, tandis que l'événement gère les notifications. Vous pourriez ajouter plus d'écouteurs (par exemple, enregistrer l'inscription) plus tard.

#### Exemple 3 : Vider un cache
```php
// Listener to clear a cache
Flight::onEvent('page.updated', function ($pageId) {
	// if using the flightphp/cache plugin
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Trigger when a page is edited
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretend we updated the page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Pourquoi c'est utile** : Le code d'édition ne se soucie pas du cache — il signale juste la mise à jour. D'autres parties de l'application peuvent réagir comme nécessaire.

### Meilleures pratiques

- **Nommez les événements clairement** : Utilisez des noms spécifiques comme `'user.login'` ou `'page.updated'` pour qu'il soit évident ce qu'ils font.
- **Gardez les écouteurs simples** : Ne mettez pas de tâches lentes ou complexes dans les écouteurs — gardez votre application rapide.
- **Testez vos événements** : Déclenchez-les manuellement pour vous assurer que les écouteurs fonctionnent comme prévu.
- **Utilisez les événements sagement** : Ils sont excellents pour le découplage, mais trop d'événements peuvent rendre votre code difficile à suivre — utilisez-les quand cela a du sens.

Le système d'événements dans Flight PHP, avec `Flight::onEvent()` et `Flight::triggerEvent()`, vous donne une façon simple mais puissante de construire des applications flexibles. En permettant à différentes parties de votre application de communiquer via des événements, vous pouvez garder votre code organisé, réutilisable et facile à étendre. Que vous enregistriez des actions, envoyiez des notifications ou gériez des mises à jour, les événements vous aident à le faire sans emmêler votre logique. De plus, avec la possibilité de surcharger ces méthodes, vous avez la liberté d'adapter le système à vos besoins. Commencez petit avec un seul événement, et regardez comment cela transforme la structure de votre application !

### Événements intégrés

Flight PHP vient avec quelques événements intégrés que vous pouvez utiliser pour vous accrocher au cycle de vie du framework. Ces événements sont déclenchés à des points spécifiques du cycle requête/réponse, vous permettant d'exécuter une logique personnalisée quand certaines actions se produisent.

#### Liste des événements intégrés
- **flight.request.received** : `function(Request $request)` Déclenché quand une requête est reçue, analysée et traitée.
- **flight.error** : `function(Throwable $exception)` Déclenché quand une erreur se produit pendant le cycle de vie de la requête.
- **flight.redirect** : `function(string $url, int $status_code)` Déclenché quand une redirection est initiée.
- **flight.cache.checked** : `function(string $cache_key, bool $hit, float $executionTime)` Déclenché quand le cache est vérifié pour une clé spécifique et si c'est un hit ou un miss de cache.
- **flight.middleware.before** : `function(Route $route)`Déclenché après l'exécution du middleware before.
- **flight.middleware.after** : `function(Route $route)` Déclenché après l'exécution du middleware after.
- **flight.middleware.executed** : `function(Route $route, $middleware, string $method, float $executionTime)` Déclenché après l'exécution de n'importe quel middleware
- **flight.route.matched** : `function(Route $route)` Déclenché quand une route est matched, mais pas encore exécutée.
- **flight.route.executed** : `function(Route $route, float $executionTime)` Déclenché après l'exécution et le traitement d'une route. `$executionTime` est le temps qu'il a fallu pour exécuter la route (appeler le contrôleur, etc.).
- **flight.view.rendered** : `function(string $template_file_path, float $executionTime)` Déclenché après le rendu d'une vue. `$executionTime` est le temps qu'il a fallu pour rendre le template. **Note : Si vous surchargez la méthode `render`, vous devrez re-déclencher cet événement.**
- **flight.response.sent** : `function(Response $response, float $executionTime)` Déclenché après l'envoi d'une réponse au client. `$executionTime` est le temps qu'il a fallu pour construire la réponse.

## Voir aussi
- [Extending Flight](/learn/extending) - Comment étendre et personnaliser les fonctionnalités principales de Flight.
- [Cache](/awesome-plugins/php_file_cache) - Exemple d'utilisation d'événements pour vider le cache quand une page est mise à jour.

## Dépannage
- Si vous ne voyez pas vos écouteurs d'événements être appelés, assurez-vous de les enregistrer avant de déclencher les événements. L'ordre d'enregistrement compte.

## Journal des changements
- v3.15.0 - Ajout des événements à Flight.