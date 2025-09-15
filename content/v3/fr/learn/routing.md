# Routage

> **Note :** Vous voulez en savoir plus sur le routage ? Consultez la page ["pourquoi un framework ?"] (/learn/why-frameworks) pour une explication plus détaillée.

Le routage de base dans Flight se fait en faisant correspondre un motif d'URL à une fonction de rappel ou à un tableau contenant une classe et une méthode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Les routes sont mises en correspondance dans l'ordre dans lequel elles sont définies. La première route qui correspond à une requête sera invoquée.

### Fonctions de rappel
Le rappel peut être n'importe quel objet qui est appelable. Vous pouvez donc utiliser une fonction régulière :

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Classes
Vous pouvez également utiliser une méthode statique d'une classe :

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';  // Affiche un message de salutation
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Ou en créant d'abord un objet puis en appelant la méthode :

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';  // Initialise le nom de l'utilisateur
    }

    public function hello() {
        echo "Hello, {$this->name}!";  // Affiche une salutation avec le nom
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Vous pouvez aussi faire cela sans créer l'objet d'abord
// Note : Aucun argument ne sera injecté dans le constructeur
Flight::route('/', [ 'Greeting', 'hello' ]);
// De plus, vous pouvez utiliser cette syntaxe plus courte
Flight::route('/', 'Greeting->hello');
// ou
Flight::route('/', Greeting::class.'->hello');
```

#### Injection de dépendances via DIC (Conteneur d'injection de dépendances)
Si vous souhaitez utiliser l'injection de dépendances via un conteneur (PSR-11, PHP-DI, Dice, etc.), le seul type de routes où cela est disponible est soit en créant directement l'objet vous-même et en utilisant le conteneur pour créer votre objet, soit en utilisant des chaînes pour définir la classe et la méthode à appeler. Vous pouvez consulter la page [Injection de dépendances] (/learn/extending) pour plus d'informations.

Voici un exemple rapide :

```php
use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;  // Assigne le wrapper PDO
	}

	public function hello(int $id) {
		// Faites quelque chose avec $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";  // Affiche un message avec le nom récupéré
	}
}

// index.php

// Configurez le conteneur avec les paramètres nécessaires
// Consultez la page sur l'injection de dépendances pour plus d'informations sur PSR-11
$dice = new \Dice\Dice();

// N'oubliez pas de réaffecter la variable avec '$dice = ' !!!!
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
	return $dice->create($class, $params);  // Crée une instance avec le conteneur
});

// Routes comme d'habitude
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// ou
Flight::route('/hello/@id', 'Greeting->hello');
// ou
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Routage par méthode

Par défaut, les motifs de routes sont mis en correspondance avec toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';  // Affiche un message pour une requête GET
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';  // Affiche un message pour une requête POST
});

// Vous ne pouvez pas utiliser Flight::get() pour les routes car c'est une méthode
//    pour obtenir des variables, pas pour créer une route.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Vous pouvez également mapper plusieurs méthodes à un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';  // Affiche un message pour une requête GET ou POST
});
```

De plus, vous pouvez récupérer l'objet Router qui dispose de certaines méthodes d'aide pour vous :

```php
$router = Flight::router();

// Mappe toutes les méthodes
$router->map('/', function() {
	echo 'hello world!';  // Affiche un message simple
});

// Requête GET
$router->get('/users', function() {
	echo 'users';  // Affiche 'users'
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Expressions régulières

Vous pouvez utiliser des expressions régulières dans vos routes :

```php
Flight::route('/user/[0-9]+', function () {
  // Cela correspondra à /user/1234
});
```

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou des paramètres nommés avec des expressions régulières, car ils sont plus lisibles et plus faciles à maintenir.

## Paramètres nommés

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis à votre fonction de rappel. **Ceci est plus pour la lisibilité de la route que pour autre chose. Veuillez consulter la section ci-dessous sur l'avertissement important.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";  // Affiche une salutation avec le nom et l'ID
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Cela correspondra à /bob/123
  // Mais pas à /bob/12345
});
```

> **Note :** Les groupes d'expressions régulières correspondants `()` avec des paramètres positionnels ne sont pas pris en charge. :'\(

### Avertissement important

Bien que dans l'exemple ci-dessus, il semble que `@name` soit directement lié à la variable `$name`, ce n'est pas le cas. L'ordre des paramètres dans la fonction de rappel détermine ce qui lui est passé. Donc, si vous inversiez l'ordre des paramètres dans la fonction de rappel, les variables seraient également inversées. Voici un exemple :

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";  // Affiche une salutation avec l'ID et le nom inversés
});
```

Et si vous accédez à l'URL suivante : `/bob/123`, la sortie serait `hello, 123 (bob)!`. Soyez prudent lorsque vous configurez vos routes et vos fonctions de rappel.

## Paramètres optionnels

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

Tous les paramètres optionnels qui ne correspondent pas seront passés en tant que `NULL`.

## Joker

La correspondance ne se fait que sur les segments d'URL individuels. Si vous souhaitez faire correspondre plusieurs segments, vous pouvez utiliser le joker `*`.

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

## Passage

Vous pouvez passer l'exécution à la route correspondante suivante en renvoyant `true` depuis votre fonction de rappel.

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

## Alias de route

Vous pouvez assigner un alias à une route, afin que l'URL puisse être générée dynamiquement plus tard dans votre code (comme dans un modèle par exemple).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // Crée une route avec un alias

// Plus tard dans le code
Flight::getUrl('user_view', [ 'id' => 5 ]);  // Renvoie '/users/5'
```

Ceci est particulièrement utile si votre URL change. Dans l'exemple ci-dessus, supposons que les utilisateurs soient déplacés vers `/admin/users/@id` à la place. Avec l'aliasing en place, vous n'avez pas besoin de modifier les endroits où vous référencez l'alias car l'alias renverra maintenant `/admin/users/5` comme dans l'exemple.

L'aliasage de route fonctionne également dans les groupes :

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // Crée une route avec un alias dans un groupe
});


// Plus tard dans le code
Flight::getUrl('user_view', [ 'id' => 5 ]);  // Renvoie '/users/5'
```

## Informations sur la route

Si vous souhaitez inspecter les informations de la route correspondante, il y a 2 façons de le faire. Vous pouvez utiliser la propriété `executedRoute` ou demander que l'objet de route soit passé à votre rappel en passant `true` comme troisième paramètre dans la méthode de route. L'objet de route sera toujours le dernier paramètre passé à votre fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP mises en correspondance
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le motif d'URL
  $route->splat;

  // Affiche le chemin d'URL... si vous en avez vraiment besoin
  $route->pattern;

  // Affiche le middleware assigné à cela
  $route->middleware;

  // Affiche l'alias assigné à cette route
  $route->alias;
}, true);
```

Ou si vous souhaitez inspecter la dernière route exécutée, vous pouvez faire :

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;  // Récupère la route exécutée
  // Faites quelque chose avec $route
  // Tableau des méthodes HTTP mises en correspondance
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le motif d'URL
  $route->splat;

  // Affiche le chemin d'URL... si vous en avez vraiment besoin
  $route->pattern;

  // Affiche le middleware assigné à cela
  $route->middleware;

  // Affiche l'alias assigné à cette route
  $route->alias;
});
```

> **Note :** La propriété `executedRoute` ne sera définie qu'après qu'une route ait été exécutée. Si vous essayez d'y accéder avant qu'une route ait été exécutée, elle sera `NULL`. Vous pouvez également utiliser executedRoute dans le middleware !

## Groupement de routes

Il peut y avoir des moments où vous souhaitez regrouper des routes liées (telles que `/api/v1`). Vous pouvez le faire en utilisant la méthode `group` :

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

Vous pouvez même imbriquer des groupes dans des groupes :

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtient des variables, cela ne définit pas une route ! Voir le contexte d'objet ci-dessous
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

	// Flight::get() obtient des variables, cela ne définit pas une route ! Voir le contexte d'objet ci-dessous
	Flight::route('GET /users', function () {
	  // Correspond à GET /api/v2/users
	});
  });
});
```

### Groupement avec contexte d'objet

Vous pouvez toujours utiliser le groupement de routes avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // Utilisez la variable $router
  $router->get('/users', function () {
	// Correspond à GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Correspond à POST /api/v1/posts
  });
});
```

### Groupement avec middleware

Vous pouvez également assigner un middleware à un groupe de routes :

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Correspond à /api/v1/users
  });
}, [ MyAuthMiddleware::class ]);  // ou [ new MyAuthMiddleware() ] si vous souhaitez utiliser une instance
```

Consultez plus de détails sur la page [middleware de groupe] (/learn/middleware#grouping-middleware).

## Routage de ressources

Vous pouvez créer un ensemble de routes pour une ressource en utilisant la méthode `resource`. Cela créera un ensemble de routes pour une ressource qui suit les conventions RESTful.

Pour créer une ressource, procédez comme suit :

```php
Flight::resource('/users', UsersController::class);  // Crée les routes pour la ressource
```

Et ce qui se produira en arrière-plan, c'est qu'il créera les routes suivantes :

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

Et votre contrôleur ressemblera à ceci :

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

> **Note** : Vous pouvez afficher les routes nouvellement ajoutées avec `runway` en exécutant `php runway routes`.

### Personnalisation des routes de ressources

Il y a quelques options pour configurer les routes de ressources.

#### Base d'alias

Vous pouvez configurer la `aliasBase`. Par défaut, l'alias est la dernière partie de l'URL spécifiée. Par exemple, `/users/` résulterait en une `aliasBase` de `users`. Lorsque ces routes sont créées, les aliases sont `users.index`, `users.create`, etc. Si vous souhaitez modifier l'alias, définissez `aliasBase` sur la valeur souhaitée.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);  // Définit une base d'alias personnalisée
```

#### Seulement et Excepté

Vous pouvez également spécifier quelles routes vous souhaitez créer en utilisant les options `only` et `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);  // Crée seulement certaines routes
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);  // Exclut certaines routes
```

Ce sont essentiellement des options de liste blanche et de liste noire afin que vous puissiez spécifier quelles routes vous souhaitez créer.

#### Middleware

Vous pouvez également spécifier un middleware à exécuter sur chacune des routes créées par la méthode `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);  // Ajoute un middleware
```

## Diffusion en continu

Vous pouvez maintenant diffuser des réponses au client en utilisant la méthode `streamWithHeaders()`. Ceci est utile pour envoyer de grands fichiers, des processus longs ou pour générer de grandes réponses. La diffusion d'une route est gérée un peu différemment d'une route régulière.

> **Note :** Les réponses de diffusion en continu ne sont disponibles que si vous avez [`flight.v2.output_buffering`] (/learn/migrating-to-v3#output_buffering) défini sur false.

### Diffusion avec en-têtes manuels

Vous pouvez diffuser une réponse au client en utilisant la méthode `stream()` sur une route. Si vous faites cela, vous devez définir toutes les méthodes à la main avant de sortir quoi que ce soit vers le client. Cela se fait avec la fonction PHP `header()` ou la méthode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// Évidemment, vous devriez assainir le chemin et tout cela.
	$fileNameSafe = basename($filename);  // Rend le nom de fichier sûr

	// Si vous avez des en-têtes supplémentaires à définir ici après l'exécution de la route
	// vous devez les définir avant que quoi que ce soit ne soit affiché.
	// Ils doivent tous être un appel brut à la fonction header() ou
	// un appel à Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// ou
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');  // Arrête si le fichier n'est pas lisible
	}

	// Définissez manuellement la longueur du contenu si vous le souhaitez
	header('Content-Length: '.filesize($filePath));

	// Diffusez le fichier vers le client au fur et à mesure qu'il est lu
	readfile($filePath);

// C'est la ligne magique ici
})->stream();
```

### Diffusion avec en-têtes

Vous pouvez également utiliser la méthode `streamWithHeaders()` pour définir les en-têtes avant de commencer la diffusion.

```php
Flight::route('/stream-users', function() {

	// Vous pouvez ajouter n'importe quels en-têtes supplémentaires que vous voulez ici
	// Vous devez simplement utiliser header() ou Flight::response()->setRealHeader()

	// Cependant que vous récupérez vos données, juste comme un exemple...
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

// C'est ainsi que vous définirez les en-têtes avant de commencer la diffusion.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// Code de statut optionnel, par défaut à 200
	'status' => 200
]);
```