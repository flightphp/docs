# Système d'Événements dans Flight PHP (v3.15.0+)

Flight PHP introduit un système d'événements léger et intuitif qui vous permet d'enregistrer et de déclencher des événements personnalisés dans votre application. Avec l'ajout de `Flight::onEvent()` et `Flight::triggerEvent()`, vous pouvez désormais vous intégrer aux moments clés du cycle de vie de votre application ou définir vos propres événements pour rendre votre code plus modulaire et extensible. Ces méthodes font partie des **méthodes mappables** de Flight, ce qui signifie que vous pouvez remplacer leur comportement pour l'adapter à vos besoins.

Ce guide couvre tout ce que vous devez savoir pour commencer avec les événements, y compris pourquoi ils sont précieux, comment les utiliser, et des exemples pratiques pour aider les débutants à comprendre leur puissance.

## Pourquoi Utiliser des Événements ?

Les événements vous permettent de séparer différentes parties de votre application afin qu'elles ne dépendent pas trop les unes des autres. Cette séparation—souvent appelée **découplage**—rend votre code plus facile à mettre à jour, à étendre ou à déboguer. Au lieu d'écrire tout dans un seul gros morceau, vous pouvez diviser votre logique en plus petits morceaux indépendants qui réagissent à des actions spécifiques (événements).

Imaginez que vous construisez une application de blog :
- Lorsqu'un utilisateur poste un commentaire, vous pourriez vouloir :
  - Enregistrer le commentaire dans la base de données.
  - Envoyer un e-mail au propriétaire du blog.
  - Consigner l'action pour la sécurité.

Sans événements, vous feriez tout cela dans une seule fonction. Avec des événements, vous pouvez le diviser : une partie enregistre le commentaire, une autre déclenche un événement comme `'comment.posted'`, et des écouteurs séparés gèrent l'e-mail et la journalisation. Cela maintient votre code plus propre et vous permet d'ajouter ou de supprimer des fonctionnalités (comme des notifications) sans toucher à la logique principale.

### Utilisations Courantes
- **Journalisation** : Enregistrer des actions comme des connexions ou des erreurs sans encombrer votre code principal.
- **Notifications** : Envoyer des e-mails ou des alertes lorsque quelque chose se produit.
- **Mises à jour** : Actualiser les caches ou notifier d'autres systèmes des changements.

## Enregistrer des Écouteurs d'Événements

Pour écouter un événement, utilisez `Flight::onEvent()`. Cette méthode vous permet de définir ce qui doit se passer lorsqu'un événement se produit.

### Syntaxe
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event` : Un nom pour votre événement (par exemple, `'user.login'`).
- `$callback` : La fonction à exécuter lorsque l'événement est déclenché.

### Comment Ça Fonctionne
Vous "vous abonnez" à un événement en disant à Flight quoi faire quand il se produit. Le callback peut accepter des arguments passés depuis le déclencheur d'événements.

Le système d'événements de Flight est synchrone, ce qui signifie que chaque écouteur d'événements est exécuté en séquence, l'un après l'autre. Lorsque vous déclenchez un événement, tous les écouteurs enregistrés pour cet événement s'exécuteront jusqu'à leur terme avant que votre code ne continue. Il est important de comprendre cela car cela diffère des systèmes d'événements asynchrones où les écouteurs peuvent s'exécuter en parallèle ou à un moment ultérieur.

### Exemple Simple
```php
Flight::onEvent('user.login', function ($username) {
    echo "Bienvenue de nouveau, $username !";
});
```
Ici, lorsque l'événement `'user.login'` est déclenché, il saluera l'utilisateur par son nom.

### Points Clés
- Vous pouvez ajouter plusieurs écouteurs au même événement—ils s'exécuteront dans l'ordre où vous les avez enregistrés.
- Le callback peut être une fonction, une fonction anonyme, ou une méthode d'une classe.

## Déclenchement d'Événements

Pour faire se produire un événement, utilisez `Flight::triggerEvent()`. Cela demande à Flight d'exécuter tous les écouteurs enregistrés pour cet événement, en passant toute donnée que vous fournissez.

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
Cela déclenche l'événement `'user.login'` et envoie `'alice'` à l'écouteur que nous avons défini plus tôt, qui produira : `Bienvenue de nouveau, alice !`.

### Points Clés
- Si aucun écouteur n'est enregistré, rien ne se passe—votre application ne se brisera pas.
- Utilisez l'opérateur de propagation (`...`) pour passer plusieurs arguments de manière flexible.

### Enregistrement d'Écouteurs d'Événements

...

**Arrêter d'autres Écouteurs** :
Si un écouteur renvoie `false`, aucun autre écouteur pour cet événement ne sera exécuté. Cela vous permet d'arrêter la chaîne d'événements en fonction de conditions spécifiques. Rappelez-vous, l'ordre des écouteurs est important, car le premier à renvoyer `false` arrêtera les autres de s'exécuter.

**Exemple** :
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Arrête les écouteurs suivants
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // ceci n'est jamais envoyé
});
```

## Remplacer les Méthodes d'Événements

`Flight::onEvent()` et `Flight::triggerEvent()` sont disponibles pour être [étendus](/learn/extending), ce qui signifie que vous pouvez redéfinir leur fonctionnement. C'est formidable pour les utilisateurs avancés qui souhaitent personnaliser le système d'événements, comme ajouter des journaux ou changer la façon dont les événements sont dispatchés.

### Exemple : Personnaliser `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Enregistrer chaque enregistrement d'événement
    error_log("Nouvel écouteur d'événement ajouté pour : $event");
    // Appeler le comportement par défaut (supposant un système d'événements interne)
    Flight::_onEvent($event, $callback);
});
```
Maintenant, chaque fois que vous enregistrez un événement, il l'enregistre avant de continuer.

### Pourquoi Remplacer ?
- Ajouter du débogage ou de la surveillance.
- Restreindre les événements dans certains environnements (par exemple, désactiver lors des tests).
- Intégrer une autre bibliothèque d'événements.

## Où Mettre Vos Événements

En tant que débutant, vous pourriez vous demander : *où dois-je enregistrer tous ces événements dans mon application ?* La simplicité de Flight signifie qu'il n'y a pas de règle stricte—vous pouvez les placer où cela a du sens pour votre projet. Cependant, les garder organisés vous aide à maintenir votre code à mesure que votre application grandit. Voici quelques options pratiques et meilleures pratiques, adaptées à la légèreté de Flight :

### Option 1 : Dans Votre Fichier Principal `index.php`
Pour de petites applications ou des prototypes rapides, vous pouvez enregistrer des événements directement dans votre fichier `index.php` aux côtés de vos routes. Cela garde tout au même endroit, ce qui est bien lorsque la simplicité est votre priorité.

```php
require 'vendor/autoload.php';

// Enregistrer des événements
Flight::onEvent('user.login', function ($username) {
    error_log("$username s'est connecté à " . date('Y-m-d H:i:s'));
});

// Définir des routes
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Connecté !";
});

Flight::start();
```
- **Avantages** : Simple, pas de fichiers supplémentaires, idéal pour de petits projets.
- **Inconvénients** : Peut devenir désordonné à mesure que votre application grandit avec plus d'événements et de routes.

### Option 2 : Un Fichier Séparé `events.php`
Pour une application légèrement plus grande, envisagez de déplacer les enregistrements d'événements dans un fichier dédié comme `app/config/events.php`. Incluez ce fichier dans votre `index.php` avant vos routes. Cela imite la façon dont les routes sont souvent organisées dans `app/config/routes.php` dans les projets Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username s'est connecté à " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "E-mail envoyé à $email : Bienvenue, $name !";
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
- **Avantages** : Garde `index.php` concentré sur le routage, organise les événements logiquement, facile à trouver et à éditer.
- **Inconvénients** : Ajoute un peu de structure, ce qui pourrait sembler excessif pour des très petites applications.

### Option 3 : Près de Leur Déclenchement
Une autre approche consiste à enregistrer des événements près de leur déclenchement, comme à l'intérieur d'un contrôleur ou d'une définition de route. Cela fonctionne bien si un événement est spécifique à une partie de votre application.

```php
Flight::route('/signup', function () {
    // Enregistrer l'événement ici
    Flight::onEvent('user.registered', function ($email) {
        echo "E-mail de bienvenue envoyé à $email !";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Inscrit !";
});
```
- **Avantages** : Garde le code lié ensemble, bon pour des fonctionnalités isolées.
- **Inconvénients** : Éparpille les enregistrements d'événements, rendant plus difficile la vue de tous les événements à la fois ; risque d'inscriptions en double si ce n'est pas prudent.

### Meilleure Pratique pour Flight
- **Commencer Simple** : Pour de toutes petites applications, placez les événements dans `index.php`. C’est rapide et s'aligne sur le minimalisme de Flight.
- **Grandir Intelligent** : À mesure que votre application s'élargit (par exemple, plus de 5-10 événements), utilisez un fichier `app/config/events.php`. C'est une étape naturelle, comme organiser des routes, et garde votre code bien rangé sans ajouter de frameworks complexes.
- **Évitez la Sur-ingénierie** : Ne créez pas une classe ou un répertoire "gestionnaire d'événements" à moins que votre application ne devienne énorme—Flight prospère sur la simplicité, alors gardez-le léger.

### Astuce : Groupez par Objectif
Dans `events.php`, regroupez les événements liés (par exemple, tous les événements liés aux utilisateurs ensemble) avec des commentaires pour plus de clarté :

```php
// app/config/events.php
// Événements Utilisateur
Flight::onEvent('user.login', function ($username) {
    error_log("$username s'est connecté");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Bienvenue à $email !";
});

// Événements de Page
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Cette structure évolue bien et reste conviviale pour les débutants.

## Exemples pour les Débutants

Passons en revue quelques scénarios réels pour montrer comment les événements fonctionnent et pourquoi ils sont utiles.

### Exemple 1 : Journaliser une Connexion Utilisateur
```php
// Étape 1 : Enregistrer un écouteur
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username s'est connecté à $time");
});

// Étape 2 : Déclencher cela dans votre application
Flight::route('/login', function () {
    $username = 'bob'; // Supposons que cela provienne d'un formulaire
    Flight::triggerEvent('user.login', $username);
    echo "Salut, $username !";
});
```
**Pourquoi C'est Utile** : Le code de connexion n'a pas besoin de se soucier de la journalisation—il déclenche juste l'événement. Vous pouvez plus tard ajouter plus d'écouteurs (par exemple, envoyer un e-mail de bienvenue) sans changer la route.

### Exemple 2 : Notifier de Nouveaux Utilisateurs
```php
// Écouteur pour les nouvelles inscriptions
Flight::onEvent('user.registered', function ($email, $name) {
    // Simule l'envoi d'un e-mail
    echo "E-mail envoyé à $email : Bienvenue, $name !";
});

// Déclencher cela lorsque quelqu'un s'inscrit
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Merci pour votre inscription !";
});
```
**Pourquoi C'est Utile** : La logique d'inscription se concentre sur la création de l'utilisateur, tandis que l'événement gère les notifications. Vous pourriez ajouter plus d'écouteurs (par exemple, journaliser l'inscription) plus tard.

### Exemple 3 : Vider un Cache
```php
// Écouteur pour vider un cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Vider le cache de session si applicable
    echo "Cache vidé pour la page $pageId.";
});

// Déclencher lors de la modification d'une page
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Supposons que nous ayons mis à jour la page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId mise à jour.";
});
```
**Pourquoi C'est Utile** : Le code de modification ne se soucie pas du cache—il signale simplement la mise à jour. D'autres parties de l'application peuvent réagir selon leurs besoins.

## Meilleures Pratiques

- **Nommer les Événements Clairement** : Utilisez des noms spécifiques comme `'user.login'` ou `'page.updated'` pour qu'il soit évident ce qu'ils font.
- **Garder les Écouteurs Simples** : Ne placez pas de tâches lentes ou complexes dans les écouteurs—gardez votre application rapide.
- **Tester Vos Événements** : Déclenchez-les manuellement pour vous assurer que les écouteurs fonctionnent comme prévu.
- **Utiliser les Événements Judicieusement** : Ils sont excellents pour le découplage, mais trop peuvent rendre votre code difficile à suivre—utilisez-les quand cela a du sens.

Le système d'événements dans Flight PHP, avec `Flight::onEvent()` et `Flight::triggerEvent()`, vous donne un moyen simple mais puissant de construire des applications flexibles. En laissant différentes parties de votre application communiquer entre elles par le biais d'événements, vous pouvez garder votre code organisé, réutilisable et facile à étendre. Que vous enregistriez des actions, envoyiez des notifications ou gériez des mises à jour, les événements vous aident à le faire sans enchevêtrer votre logique. De plus, avec la possibilité de remplacer ces méthodes, vous avez la liberté d'adapter le système à vos besoins. Commencez petit avec un seul événement, et regardez comment cela transforme la structure de votre application !

## Événements Intégrés

Flight PHP vient avec quelques événements intégrés que vous pouvez utiliser pour vous accrocher au cycle de vie du framework. Ces événements sont déclenchés à des moments spécifiques dans le cycle de demande/réponse, vous permettant d'exécuter une logique personnalisée lorsqu' certaines actions se produisent.

### Liste des Événements Intégrés
- `flight.request.received` : Déclenché lorsqu'une demande est reçue, analysée et traitée.
- `flight.route.middleware.before` : Déclenché après l'exécution du middleware avant.
- `flight.route.middleware.after` : Déclenché après l'exécution du middleware après.
- `flight.route.executed` : Déclenché après l'exécution et le traitement d'une route.
- `flight.response.sent` : Déclenché après qu'une réponse a été envoyée au client.