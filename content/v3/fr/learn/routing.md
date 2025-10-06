# Routage

## Aperçu
Le routage dans Flight PHP mappe les motifs d'URL sur des fonctions de rappel ou des méthodes de classe, permettant une gestion rapide et simple des requêtes. Il est conçu pour un overhead minimal, une utilisation conviviale pour les débutants, et une extensibilité sans dépendances externes.

## Comprendre
Le routage est le mécanisme central qui connecte les requêtes HTTP à la logique de votre application dans Flight. En définissant des routes, vous spécifiez comment différentes URL déclenchent du code spécifique, que ce soit par des fonctions, des méthodes de classe ou des actions de contrôleur. Le système de routage de Flight est flexible, supportant des motifs basiques, des paramètres nommés, des expressions régulières, et des fonctionnalités avancées comme l'injection de dépendances et le routage de ressources. Cette approche garde votre code organisé et facile à maintenir, tout en restant rapide et simple pour les débutants et extensible pour les utilisateurs avancés.

> **Note :** Vous voulez en savoir plus sur le routage ? Consultez la page ["pourquoi un framework ?"]( /learn/why-frameworks) pour une explication plus approfondie.

## Utilisation de base

### Définir une route simple
Le routage de base dans Flight se fait en associant un motif d'URL à une fonction de rappel ou à un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Les routes sont associées dans l'ordre où elles sont définies. La première route qui correspond à une requête sera invoquée.

### Utiliser des fonctions comme rappels
Le rappel peut être n'importe quel objet qui est invocable. Vous pouvez donc utiliser une fonction régulière :

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Utiliser des classes et des méthodes comme contrôleur
Vous pouvez également utiliser une méthode (statique ou non) d'une classe :

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// ou
Flight::route('/', [ GreetingController::class, 'hello' ]); // méthode préférée
// ou
Flight::route('/', [ 'GreetingController::hello' ]);
// ou 
Flight::route('/', [ 'GreetingController->hello' ]);
```

Ou en créant d'abord un objet puis en appelant la méthode :

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **Note :** Par défaut, lorsqu'un contrôleur est appelé dans le framework, la classe `flight\Engine` est toujours injectée sauf si vous spécifiez via un [conteneur d'injection de dépendances](/learn/dependency-injection-container)

### Routage spécifique à la méthode

Par défaut, les motifs de route sont associés à toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Vous ne pouvez pas utiliser Flight::get() pour les routes car c'est une méthode 
//    pour obtenir des variables, pas pour créer une route.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

Vous pouvez également mapper plusieurs méthodes à un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Gestion spéciale pour les requêtes HEAD et OPTIONS

Flight fournit une gestion intégrée pour les requêtes HTTP `HEAD` et `OPTIONS` :

#### Requêtes HEAD

- Les **requêtes HEAD** sont traitées comme des requêtes `GET`, mais Flight supprime automatiquement le corps de la réponse avant de l'envoyer au client.
- Cela signifie que vous pouvez définir une route pour `GET`, et les requêtes HEAD vers la même URL ne renverront que les en-têtes (pas de contenu), comme attendu par les standards HTTP.

```php
Flight::route('GET /info', function() {
    echo 'This is some info!';
});
// Une requête HEAD vers /info renverra les mêmes en-têtes, mais pas de corps.
```

#### Requêtes OPTIONS

Les requêtes `OPTIONS` sont automatiquement gérées par Flight pour toute route définie.
- Lorsqu'une requête OPTIONS est reçue, Flight répond avec un statut `204 No Content` et un en-tête `Allow` listant toutes les méthodes HTTP supportées pour cette route.
- Vous n'avez pas besoin de définir une route séparée pour OPTIONS.

```php
// Pour une route définie comme :
Flight::route('GET|POST /users', function() { /* ... */ });

// Une requête OPTIONS vers /users répondra avec :
//
// Status: 204 No Content
// Allow: GET, POST, HEAD, OPTIONS
```

### Utiliser l'objet Router

De plus, vous pouvez obtenir l'objet Router qui dispose de méthodes d'aide pour votre utilisation :

```php

$router = Flight::router();

// mappe toutes les méthodes comme Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// requête GET
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Expressions régulières (Regex)
Vous pouvez utiliser des expressions régulières dans vos routes :

```php
Flight::route('/user/[0-9]+', function () {
  // Cela correspondra à /user/1234
});
```

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou des paramètres nommés avec des expressions régulières, car ils sont plus lisibles et plus faciles à maintenir.

### Paramètres nommés
Vous pouvez spécifier des paramètres nommés dans vos routes qui seront passés à votre fonction de rappel. **Ceci est plus pour la lisibilité de la route que pour toute autre chose. Veuillez voir la section ci-dessous sur l'avertissement important.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Note :** La correspondance de groupes regex `()` avec des paramètres positionnels n'est pas supportée. Ex : `:'\(`

#### Avertissement important

Bien que dans l'exemple ci-dessus, il semble que `@name` soit directement lié à la variable `$name`, ce n'est pas le cas. L'ordre des paramètres dans la fonction de rappel détermine ce qui lui est passé. Si vous inversiez l'ordre des paramètres dans la fonction de rappel, les variables seraient également inversées. Voici un exemple :

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Et si vous alliez à l'URL suivante : `/bob/123`, la sortie serait `hello, 123 (bob)!`. 
_Soyez prudent_ lorsque vous configurez vos routes et vos fonctions de rappel !

### Paramètres optionnels
Vous pouvez spécifier des paramètres nommés qui sont optionnels pour la correspondance en enveloppant les segments entre parenthèses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Cela correspondra aux URL suivantes :
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Tout paramètre optionnel qui n'est pas associé sera passé en tant que `NULL`.

### Routage avec joker
La correspondance n'est faite que sur des segments d'URL individuels. Si vous voulez associer plusieurs segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour router toutes les requêtes vers un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faites quelque chose
});
```

### Gestionnaire 404 Non trouvé

Par défaut, si une URL ne peut pas être trouvée, Flight enverra une réponse `HTTP 404 Not Found` qui est très simple et basique.
Si vous voulez avoir une réponse 404 plus personnalisée, vous pouvez [mapper](/learn/extending) votre propre méthode `notFound` :

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// Vous pourriez aussi utiliser Flight::render() avec un modèle personnalisé.
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

### Gestionnaire Méthode non trouvée

Par défaut, si une URL est trouvée mais que la méthode n'est pas autorisée, Flight enverra une réponse `HTTP 405 Method Not Allowed` qui est très simple et basique (Ex : Method Not Allowed. Allowed Methods are: GET, POST). Elle inclura également un en-tête `Allow` avec les méthodes autorisées pour cette URL.

Si vous voulez avoir une réponse 405 plus personnalisée, vous pouvez [mapper](/learn/extending) votre propre méthode `methodNotFound` :

```php
use flight\net\Route;

Flight::map('methodNotFound', function(Route $route) {
	$url = Flight::request()->url;
	$methods = implode(', ', $route->methods);

	// Vous pourriez aussi utiliser Flight::render() avec un modèle personnalisé.
	$output = <<<HTML
		<h1>My Custom 405 Method Not Allowed</h1>
		<h3>The method you have requested for {$url} is not allowed.</h3>
		<p>Allowed Methods are: {$methods}</p>
		HTML;

	$this->response()
		->clearBody()
		->status(405)
		->setHeader('Allow', $methods)
		->write($output)
		->send();
});
```

## Utilisation avancée

### Injection de dépendances dans les routes
Si vous voulez utiliser l'injection de dépendances via un conteneur (PSR-11, PHP-DI, Dice, etc.), le seul type de routes où cela est disponible est soit en créant directement l'objet vous-même et en utilisant le conteneur pour créer votre objet, soit en utilisant des chaînes pour définir la classe et la méthode à appeler. Vous pouvez consulter la page [Injection de dépendances](/learn/dependency-injection-container) pour plus d'informations. 

Voici un exemple rapide :

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// do something with $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Configurez le conteneur avec les paramètres dont vous avez besoin
// Voir la page Injection de dépendances pour plus d'informations sur PSR-11
$dice = new \Dice\Dice();

// N'oubliez pas de réassigner la variable avec '$dice = '!!!!! 
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Enregistrez le gestionnaire de conteneur
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes comme d'habitude
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// ou
Flight::route('/hello/@id', 'Greeting->hello');
// ou
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### Passer l'exécution à la route suivante
<span class="badge bg-warning">Déprécié</span>
Vous pouvez passer l'exécution à la route correspondante suivante en retournant `true` depuis votre fonction de rappel.

```php
Flight::route('/user/@name', function (string $name) {
  // Vérifiez une condition
  if ($name !== "Bob") {
    // Continuez vers la route suivante
    return true;
  }
});

Flight::route('/user/*', function () {
  // Cela sera appelé
});
```

Il est maintenant recommandé d'utiliser [middleware](/learn/middleware) pour gérer des cas d'utilisation complexes comme celui-ci.

### Aliasing de route
En assignant un alias à une route, vous pouvez plus tard appeler cet alias dans votre application de manière dynamique pour qu'il soit généré plus tard dans votre code (ex : un lien dans un modèle HTML, ou générer une URL de redirection).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// ou 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// plus tard dans le code quelque part
class UserController {
	public function update() {

		// code pour sauvegarder l'utilisateur...
		$id = $user['id']; // 5 par exemple

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // renverra '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Ceci est particulièrement utile si votre URL change. Dans l'exemple ci-dessus, supposons que users ait été déplacé vers `/admin/users/@id` à la place.
Avec l'aliasing en place pour la route, vous n'avez plus besoin de trouver toutes les anciennes URL dans votre code et de les changer car l'alias renverra maintenant `/admin/users/5` comme dans l'exemple ci-dessus.

L'aliasing de route fonctionne encore dans les groupes :

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// ou
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Inspection des informations de route
Si vous voulez inspecter les informations de la route correspondante, il y a 2 façons de faire ceci :

1. Vous pouvez utiliser une propriété `executedRoute` sur l'objet `Flight::router()`.
2. Vous pouvez demander que l'objet route soit passé à votre rappel en passant `true` comme troisième paramètre dans la méthode route. L'objet route sera toujours le dernier paramètre passé à votre fonction de rappel.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Faites quelque chose avec $route
  // Tableau des méthodes HTTP associées
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le motif d'URL
  $route->splat;

  // Montre le chemin d'URL....si vous en avez vraiment besoin
  $route->pattern;

  // Montre le middleware assigné à ceci
  $route->middleware;

  // Montre l'alias assigné à cette route
  $route->alias;
});
```

> **Note :** La propriété `executedRoute` ne sera définie qu'après qu'une route ait été exécutée. Si vous essayez d'y accéder avant qu'une route ait été exécutée, elle sera `NULL`. Vous pouvez aussi utiliser executedRoute dans [middleware](/learn/middleware) !

#### Passer `true` à la définition de route
```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP associées
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le motif d'URL
  $route->splat;

  // Montre le chemin d'URL....si vous en avez vraiment besoin
  $route->pattern;

  // Montre le middleware assigné à ceci
  $route->middleware;

  // Montre l'alias assigné à cette route
  $route->alias;
}, true);// <-- Ce paramètre true est ce qui rend cela possible
```

### Groupement de routes et Middleware
Il peut y avoir des moments où vous voulez grouper des routes liées ensemble (comme `/api/v1`).
Vous pouvez le faire en utilisant la méthode `group` :

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Correspond à /api/v1/users
  });

  Flight::route('/posts', function () {
	// Correspond à /api/v1/posts
  });
});
```

Vous pouvez même imbriquer des groupes de groupes :

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtient des variables, cela ne définit pas une route ! Voir le contexte objet ci-dessous
	Flight::route('GET /users', function () {
	  // Correspond à GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Correspond à POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Correspond à PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtient des variables, cela ne définit pas une route ! Voir le contexte objet ci-dessous
	Flight::route('GET /users', function () {
	  // Correspond à GET /api/v2/users
	});
  });
});
```

#### Groupement avec contexte objet

Vous pouvez toujours utiliser le groupement de routes avec l'objet `Engine` de la manière suivante :

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // utilisez la variable $router
  $router->get('/users', function () {
	// Correspond à GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Correspond à POST /api/v1/posts
  });
});
```

> **Note :** C'est la méthode préférée pour définir des routes et des groupes avec l'objet `$router`.

#### Groupement avec Middleware

Vous pouvez également assigner un middleware à un groupe de routes :

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Correspond à /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // ou [ new MyAuthMiddleware() ] si vous voulez utiliser une instance
```

Voir plus de détails sur la page [group middleware](/learn/middleware#grouping-middleware).

### Routage de ressources
Vous pouvez créer un ensemble de routes pour une ressource en utilisant la méthode `resource`. Cela créera un ensemble de routes pour une ressource qui suit les conventions RESTful.

Pour créer une ressource, faites ceci :

```php
Flight::resource('/users', UsersController::class);
```

Et ce qui se passera en arrière-plan est qu'il créera les routes suivantes :

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

Et votre contrôleur utilisera les méthodes suivantes :

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Note** : Vous pouvez visualiser les routes nouvellement ajoutées avec `runway` en exécutant `php runway routes`.

#### Personnalisation des routes de ressources

Il y a quelques options pour configurer les routes de ressources.

##### Alias de base

Vous pouvez configurer l'`aliasBase`. Par défaut, l'alias est la dernière partie de l'URL spécifiée.
Par exemple, `/users/` résulterait en un `aliasBase` de `users`. Lorsque ces routes sont créées, les alias sont `users.index`, `users.create`, etc. Si vous voulez changer l'alias, définissez l'`aliasBase` à la valeur que vous voulez.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only et Except

Vous pouvez également spécifier quelles routes vous voulez créer en utilisant les options `only` et `except`.

```php
// Liste blanche seulement ces méthodes et liste noire le reste
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Liste noire seulement ces méthodes et liste blanche le reste
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Ce sont essentiellement des options de liste blanche et liste noire pour que vous puissiez spécifier quelles routes vous voulez créer.

##### Middleware

Vous pouvez également spécifier un middleware à exécuter sur chacune des routes créées par la méthode `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Réponses en streaming

Vous pouvez maintenant diffuser des réponses au client en utilisant `stream()` ou `streamWithHeaders()`. 
Ceci est utile pour envoyer de grands fichiers, des processus à longue durée, ou générer de grandes réponses. 
Le streaming d'une route est géré un peu différemment qu'une route régulière.

> **Note :** Les réponses en streaming ne sont disponibles que si vous avez [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) défini à `false`.

#### Stream avec en-têtes manuels

Vous pouvez diffuser une réponse au client en utilisant la méthode `stream()` sur une route. Si vous 
faites cela, vous devez définir tous les en-têtes manuellement avant de sortir quoi que ce soit vers le client.
Ceci se fait avec la fonction php `header()` ou la méthode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// évidemment vous devriez sanitiser le chemin et tout ça.
	$fileNameSafe = basename($filename);

	// Si vous avez des en-têtes supplémentaires à définir ici après que la route ait été exécutée
	// vous devez les définir avant que quoi que ce soit ne soit échoé.
	// Ils doivent tous être un appel brut à la fonction header() ou 
	// un appel à Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// ou
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// définissez manuellement la longueur du contenu si vous le souhaitez
	header('Content-Length: '.filesize($filePath));
	// ou
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Diffusez le fichier au client au fur et à mesure qu'il est lu
	readfile($filePath);

// C'est la ligne magique ici
})->stream();
```

#### Stream avec en-têtes

Vous pouvez également utiliser la méthode `streamWithHeaders()` pour définir les en-têtes avant de commencer le streaming.

```php
Flight::route('/stream-users', function() {

	// vous pouvez ajouter n'importe quels en-têtes supplémentaires que vous voulez ici
	// vous devez juste utiliser header() ou Flight::response()->setRealHeader()

	// cependant que vous tirez vos données, juste comme exemple...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Ceci est requis pour envoyer les données au client
		ob_flush();
	}
	echo '}';

// C'est ainsi que vous définirez les en-têtes avant de commencer le streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// code de statut optionnel, par défaut 200
	'status' => 200
]);
```

## Voir aussi
- [Middleware](/learn/middleware) - Utiliser du middleware avec des routes pour l'authentification, la journalisation, etc.
- [Injection de dépendances](/learn/dependency-injection-container) - Simplifier la création et la gestion d'objets dans les routes.
- [Pourquoi un framework ?](/learn/why-frameworks) - Comprendre les avantages d'utiliser un framework comme Flight.
- [Extension](/learn/extending) - Comment étendre Flight avec votre propre fonctionnalité incluant la méthode `notFound`.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - Fonction PHP pour la correspondance d'expressions régulières.

## Dépannage
- Les paramètres de route sont associés par ordre, pas par nom. Assurez-vous que l'ordre des paramètres du rappel correspond à la définition de la route.
- Utiliser `Flight::get()` ne définit pas une route ; utilisez `Flight::route('GET /...')` pour le routage ou le contexte objet Router dans les groupes (ex. `$router->get(...)`).
- La propriété executedRoute n'est définie qu'après l'exécution d'une route ; elle est NULL avant l'exécution.
- Le streaming nécessite que la fonctionnalité de tampon de sortie legacy de Flight soit désactivée (`flight.v2.output_buffering = false`).
- Pour l'injection de dépendances, seules certaines définitions de routes supportent l'instanciation basée sur conteneur.

### 404 Non trouvé ou comportement de route inattendu

Si vous voyez une erreur 404 Non trouvé (mais vous jurez sur votre vie que c'est vraiment là et que ce n'est pas une faute de frappe), cela pourrait en fait être un problème avec le fait que vous retournez une valeur dans votre point de terminaison de route au lieu de simplement l'échoer. La raison de cela est intentionnelle mais pourrait surprendre certains développeurs.

```php
Flight::route('/hello', function(){
	// Cela pourrait causer une erreur 404 Non trouvé
	return 'Hello World';
});

// Ce que vous voulez probablement
Flight::route('/hello', function(){
	echo 'Hello World';
});
```

La raison de cela est en raison d'un mécanisme spécial intégré au routeur qui gère la sortie de retour comme un signal pour "aller à la route suivante". 
Vous pouvez voir le comportement documenté dans la section [Routage](/learn/routing#passing).

## Journal des modifications
- v3 : Ajout du routage de ressources, de l'aliasing de route, et du support de streaming, groupes de routes, et support de middleware.
- v1 : La grande majorité des fonctionnalités de base disponibles.