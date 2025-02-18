# Routage

> **Remarque :** Vous souhaitez en savoir plus sur le routage ? Consultez la page ["pourquoi un framework?"](/learn/why-frameworks) pour une explication plus approfondie.

Le routage de base dans Flight se fait en faisant correspondre un modèle d'URL avec une fonction de rappel ou un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'bonjour le monde!';
});
```

> Les routes sont appariées dans l'ordre où elles sont définies. La première route correspondant à une requête sera invoquée.

### Fonctions de rappel
Le rappel peut être n'importe quel objet qui est appelable. Vous pouvez donc utiliser une fonction régulière :

```php
function hello() {
    echo 'bonjour le monde!';
}

Flight::route('/', 'hello');
```

### Classes
Vous pouvez également utiliser une méthode statique d'une classe :

```php
class Greeting {
    public static function hello() {
        echo 'bonjour le monde!';
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
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Bonjour, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Vous pouvez également faire cela sans créer d'abord l'objet
// Remarque : Aucun argument ne sera injecté dans le constructeur
Flight::route('/', [ 'Greeting', 'hello' ]);
// De plus, vous pouvez utiliser cette syntaxe plus courte
Flight::route('/', 'Greeting->hello');
// ou
Flight::route('/', Greeting::class.'->hello');
```

#### Injection de dépendance via DIC (Conteneur d'Injection de Dépendance)
Si vous souhaitez utiliser l'injection de dépendance via un conteneur (PSR-11, PHP-DI, Dice, etc.), le seul type de routes où cela est disponible est soit en créant directement l'objet vous-même et en utilisant le conteneur pour créer votre objet, soit vous pouvez utiliser des chaînes pour définir la classe et la méthode à appeler. Vous pouvez consulter la page [Injection de Dépendance](/learn/extending) pour plus d'informations.

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
		// faire quelque chose avec $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Bonjour, monde! Mon nom est {$name}!";
	}
}

// index.php

// Configurez le conteneur avec tous les paramètres dont vous avez besoin
// Consultez la page d'Injection de Dépendance pour plus d'informations sur PSR-11
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

## Routage par méthode

Par défaut, les modèles de route sont associés à toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J\'ai reçu une requête GET.';
});

Flight::route('POST /', function () {
  echo 'J\'ai reçu une requête POST.';
});

// Vous ne pouvez pas utiliser Flight::get() pour les routes, car c'est une méthode 
// pour obtenir des variables, pas pour créer une route.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Vous pouvez également mapper plusieurs méthodes à un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J\'ai reçu soit une requête GET soit une requête POST.';
});
```

De plus, vous pouvez obtenir l'objet Routeur qui a quelques méthodes d'aide à utiliser :

```php

$router = Flight::router();

// mappe toutes les méthodes
$router->map('/', function() {
	echo 'bonjour le monde!';
});

// Requête GET
$router->get('/users', function() {
	echo 'utilisateurs';
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

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis à votre fonction de rappel. **C'est plus pour la lisibilité de la route qu'autre chose. Veuillez voir la section ci-dessous sur l'avertissement important.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "bonjour, $name ($id)!";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Remarque :** La correspondance des groupes regex `()` avec des paramètres positionnels n'est pas prise en charge. :'\(

### Avertissement important

Bien que dans l'exemple ci-dessus, il semble que `@name` soit directement lié à la variable `$name`, ce n'est pas le cas. L'ordre des paramètres dans la fonction de rappel détermine ce qui y est passé. Donc, si vous deviez changer l'ordre des paramètres dans la fonction de rappel, les variables seraient également échangées. Voici un exemple :

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "bonjour, $name ($id)!";
});
```

Et si vous accédez à l'URL suivante : `/bob/123`, la sortie sera `bonjour, 123 (bob)!`. 
Veuillez faire attention lorsque vous configurez vos routes et vos fonctions de rappel.

## Paramètres optionnels

Vous pouvez spécifier des paramètres nommés qui sont optionnels pour la correspondance en englobant des segments entre parenthèses.

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

Tout paramètre optionnel qui n'est pas apparié sera transmis comme `NULL`.

## Jokers

La correspondance ne se fait que sur des segments individuels d'URL. Si vous souhaitez correspondre à plusieurs segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour envoyer toutes les demandes à un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Passage

Vous pouvez passer l'exécution à la prochaine route correspondante en retournant `true` depuis votre fonction de rappel.

```php
Flight::route('/user/@name', function (string $name) {
  // Vérifiez certaines conditions
  if ($name !== "Bob") {
    // Continuez à la prochaine route
    return true;
  }
});

Flight::route('/user/*', function () {
  // Cela sera appelé
});
```

## Alias de route

Vous pouvez attribuer un alias à une route, afin que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple).

```php
Flight::route('/users/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'user_view');

// plus tard dans le code quelque part
Flight::getUrl('user_view', [ 'id' => 5 ]); // renverra '/users/5'
```

C'est particulièrement utile si votre URL venait à changer. Dans l'exemple ci-dessus, disons que les utilisateurs ont été déplacés vers `/admin/users/@id` à la place. 
Avec l'alias en place, vous n'avez pas à changer partout où vous référencez l'alias car l'alias renverra maintenant `/admin/users/5` comme dans l'exemple ci-dessus.

L'alias de route fonctionne également dans les groupes :

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'user_view');
});

// plus tard dans le code quelque part
Flight::getUrl('user_view', [ 'id' => 5 ]); // renverra '/users/5'
```

## Informations sur la route

Si vous souhaitez inspecter les informations sur la route correspondante, vous pouvez demander à ce que l'objet de la route soit transmis à votre fonction de rappel en passant `true` en tant que troisième paramètre dans la méthode de route. L'objet de route sera toujours le dernier paramètre passé à votre fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP correspondantes
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le modèle d'URL
  $route->splat;

  // Montre le chemin d'URL....si vous en avez vraiment besoin
  $route->pattern;

  // Montre quel middleware est attribué à cela
  $route->middleware;

  // Montre l'alias attribué à cette route
  $route->alias;
}, true);
```

## Groupement de routes

Il peut y avoir des fois où vous souhaitez regrouper des routes liées ensemble (comme `/api/v1`).
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
	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
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

	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
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

  // utilisez la variable $router
  $router->get('/users', function () {
	// Correspond à GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Correspond à POST /api/v1/posts
  });
});
```

## Routage de ressources

Vous pouvez créer un ensemble de routes pour une ressource en utilisant la méthode `resource`. Cela créera un ensemble de routes pour une ressource qui suit les conventions RESTful.

Pour créer une ressource, faites ce qui suit :

```php
Flight::resource('/users', UsersController::class);
```

Et ce qui se passera en arrière-plan, c'est qu'il créera les routes suivantes :

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

> **Remarque :** Vous pouvez visualiser les nouvelles routes ajoutées avec `runway` en exécutant `php runway routes`.

### Personnalisation des routes de ressources

Il existe plusieurs options pour configurer les routes de ressources.

#### Alias de base

Vous pouvez configurer l'`aliasBase`. Par défaut, l'alias est la dernière partie de l'URL spécifiée. 
Par exemple, `/users/` donnerait un `aliasBase` de `users`. Lorsque ces routes sont créées, les alias sont `users.index`, `users.create`, etc. Si vous souhaitez changer l'alias, définissez l'`aliasBase` à la valeur que vous souhaitez.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Seulement et Excepté

Vous pouvez également spécifier quelles routes vous souhaitez créer en utilisant les options `only` et `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Ce sont essentiellement des options de liste blanche et de liste noire afin que vous puissiez spécifier quelles routes vous souhaitez créer.

#### Middleware

Vous pouvez également spécifier un middleware à exécuter sur chacune des routes créées par la méthode `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Vous pouvez désormais diffuser des réponses au client en utilisant la méthode `streamWithHeaders()`. 
Cela est utile pour l'envoi de fichiers volumineux, de processus longs ou la génération de grandes réponses. 
Diffuser une route est géré un peu différemment d'une route normale.

> **Remarque :** La diffusion de réponses n'est disponible que si vous avez [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) défini sur false.

### Diffusion avec en-têtes manuels

Vous pouvez diffuser une réponse au client en utilisant la méthode `stream()` sur une route. Si vous faites cela, vous devez définir toutes les méthodes à la main avant de sortir quoi que ce soit au client. Cela se fait avec la fonction php `header()` ou la méthode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// Évidemment, vous devez assainir le chemin et ce genre de choses.
	$fileNameSafe = basename($filename);

	// Si vous avez des en-têtes supplémentaires à définir ici après l'exécution de la route
	// vous devez les définir avant que quoi que ce soit ne soit écho.
	// Ils doivent tous être un appel brut à la fonction header() ou 
	// un appel à Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// ou
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Gestion des erreurs et ce genre de choses
	if(empty($fileData)) {
		Flight::halt(404, 'Fichier introuvable');
	}

	// définissez manuellement la longueur du contenu si vous le souhaitez
	header('Content-Length: '.filesize($filename));

	// Diffusez les données au client
	echo $fileData;

// C'est la ligne magique ici
})->stream();
```

### Diffusion avec en-têtes

Vous pouvez également utiliser la méthode `streamWithHeaders()` pour définir les en-têtes avant de commencer à diffuser.

```php
Flight::route('/stream-users', function() {

	// vous pouvez ajouter tous les en-têtes supplémentaires que vous voulez ici
	// vous devez juste utiliser header() ou Flight::response()->setRealHeader()

	// peu importe comment vous tirez vos données, juste comme exemple...
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

// Voici comment vous définirez les en-têtes avant de commencer à diffuser.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// code d'état optionnel, par défaut à 200
	'status' => 200
]);
```