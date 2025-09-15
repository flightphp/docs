# Routing

> **Hinweis:** Möchten Sie mehr über Routing erfahren? Schauen Sie sich die ["why a framework?"](/learn/why-frameworks) Seite für eine detailliertere Erklärung an.

Grundlegendes Routing in Flight erfolgt durch Abgleich eines URL-Musters mit einer Callback-Funktion oder einem Array aus einer Klasse und einer Methode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste Route, die zu einer Anfrage passt, wird aufgerufen.

### Callbacks/Functions
Die Callback kann jedes aufrufbare Objekt sein. Sie können also eine reguläre Funktion verwenden:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Classes
Sie können auch eine statische Methode einer Klasse verwenden:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Oder indem Sie zuerst ein Objekt erstellen und dann die Methode aufrufen:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// You also can do this without creating the object first
// Note: No args will be injected into the constructor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Additionally you can use this shorter syntax
Flight::route('/', 'Greeting->hello');
// or
Flight::route('/', Greeting::class.'->hello');
```

#### Dependency Injection via DIC (Dependency Injection Container)
Wenn Sie Dependency Injection über einen Container (PSR-11, PHP-DI, Dice usw.) verwenden möchten, ist dies nur für Routen verfügbar, bei denen Sie das Objekt direkt erstellen oder Strings verwenden, um die Klasse und Methode zu definieren. Weitere Informationen finden Sie auf der [Dependency Injection](/learn/extending) Seite.

Hier ein schnelles Beispiel:

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

// Setup the container with whatever params you need
// See the Dependency Injection page for more information on PSR-11
$dice = new \Dice\Dice();

// Don't forget to reassign the variable with '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Register the container handler
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes like normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// or
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Method Routing

Standardmäßig werden Routenmustern mit allen Anfragemethoden abgeglichen. Sie können auf spezifische Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Sie können auch mehrere Methoden auf eine einzelne Callback zuweisen, indem Sie einen `|`-Delimter verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie bereitstellt:

```php
$router = Flight::router();

// maps all methods
$router->map('/', function() {
	echo 'hello world!';
});

// GET request
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Regular Expressions

Sie können reguläre Ausdrücke in Ihren Routen verwenden:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu warten sind.

## Named Parameters

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Callback-Funktion weitergegeben werden. **Das dient hauptsächlich der Lesbarkeit der Route. Bitte beachten Sie den wichtigen Hinweis weiter unten.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern kombinieren, indem Sie den `:`-Delimter verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Hinweis:** Abgleich von Regex-Gruppen `()` mit positionsbezogenen Parametern wird nicht unterstützt. :'\(

### Wichtiger Hinweis

Obwohl im obigen Beispiel erscheint, als ob `@name` direkt mit der Variable `$name` verbunden ist, ist das nicht der Fall. Die Reihenfolge der Parameter in der Callback-Funktion bestimmt, was an sie weitergegeben wird. Wenn Sie die Reihenfolge der Parameter in der Callback-Funktion ändern, werden die Variablen ebenfalls umgeschaltet. Hier ein Beispiel:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Und wenn Sie zur folgenden URL gehen: `/bob/123`, wäre die Ausgabe `hello, 123 (bob)!`. 
Seien Sie vorsichtig, wenn Sie Ihre Routen und Callback-Funktionen einrichten.

## Optional Parameters

Sie können benannte Parameter angeben, die optional für den Abgleich sind, indem Sie Segmente in Klammern setzen.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Jegliche optionale Parameter, die nicht abgeglichen werden, werden als `NULL` weitergegeben.

## Wildcards

Der Abgleich erfolgt nur auf einzelne URL-Segmente. Wenn Sie mehrere Segmente abgleichen möchten, können Sie das `*`-Wildcard verwenden.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

Um alle Anfragen an eine einzelne Callback zu leiten, können Sie tun:

```php
Flight::route('*', function () {
  // Do something
});
```

## Passing

Sie können die Ausführung an die nächste passende Route weitergeben, indem Sie `true` aus Ihrer Callback-Funktion zurückgeben.

```php
Flight::route('/user/@name', function (string $name) {
  // Check some condition
  if ($name !== "Bob") {
    // Continue to next route
    return true;
  }
});

Flight::route('/user/*', function () {
  // This will get called
});
```

## Route Aliasing

Sie können einer Route einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (z. B. in einer Vorlage).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

Das ist besonders hilfreich, wenn sich Ihre URL ändert. Im obigen Beispiel, sagen wir, dass "users" zu `/admin/users/@id` verschoben wird.
Mit Aliasing müssen Sie nirgendwo ändern, wo Sie den Alias referenzieren, da der Alias nun `/admin/users/5` zurückgibt, wie im Beispiel.

Route-Aliasing funktioniert auch in Gruppen:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

## Route Info

Wenn Sie die Informationen zur passenden Route überprüfen möchten, gibt es 2 Wege, dies zu tun.
Sie können die `executedRoute`-Eigenschaft verwenden oder das Route-Objekt anfordern, indem Sie `true` als dritten Parameter in der Route-Methode übergeben.
Das Route-Objekt wird immer als letzter Parameter an Ihre Callback-Funktion übergeben.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
}, true);
```

Oder wenn Sie die zuletzt ausgeführte Route überprüfen möchten, können Sie tun:

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Do something with $route
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
});
```

> **Hinweis:** Die `executedRoute`-Eigenschaft wird nur nach der Ausführung einer Route gesetzt. Wenn Sie versuchen, darauf zuzugreifen, bevor eine Route ausgeführt wurde, ist sie `NULL`. Sie können executedRoute auch in Middleware verwenden!

## Route Grouping

Es könnte Fälle geben, in denen Sie verwandte Routen zusammen gruppieren möchten (z. B. `/api/v1`).
Sie können das tun, indem Sie die `group`-Methode verwenden:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts
  });
});
```

Sie können sogar Gruppen in Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users
	});
  });
});
```

### Grouping with Object Context

Sie können Route-Grouping immer noch mit dem `Engine`-Objekt auf die folgende Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // user the $router variable
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

### Grouping with Middleware

Sie können auch Middleware einer Gruppe von Routen zuweisen:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

Weitere Details finden Sie auf der [group middleware](/learn/middleware#grouping-middleware) Seite.

## Resource Routing

Sie können eine Reihe von Routen für eine Ressource mit der `resource`-Methode erstellen. Das erstellt eine Reihe von Routen für eine Ressource, die den RESTful-Konventionen folgt.

Um eine Ressource zu erstellen, tun Sie Folgendes:

```php
Flight::resource('/users', UsersController::class);
```

Und was im Hintergrund passiert, ist, dass es die folgenden Routen erstellt:

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

Und Ihr Controller sieht so aus:

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

> **Hinweis**: You can view the newly added routes with `runway` by running `php runway routes`.

### Customizing Resource Routes

There are a few options to configure the resource routes.

#### Alias Base

You can configure the `aliasBase`. By default the alias is the last part of the URL specified.
For example `/users/` would result in an `aliasBase` of `users`. When these routes are created,
the aliases are `users.index`, `users.create`, etc. If you want to change the alias, set the `aliasBase`
to the value you want.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only and Except

You can also specify which routes you want to create by using the `only` and `except` options.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

These are basically whitelisting and blacklisting options so you can specify which routes you want to create.

#### Middleware

You can also specify middleware to be run on each of the routes created by the `resource` method.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Sie können jetzt Antworten an den Client streamen, indem Sie die `streamWithHeaders()`-Methode verwenden. 
Das ist nützlich für das Senden großer Dateien, langer Prozesse oder die Generierung großer Antworten. 
Das Streamen einer Route wird etwas anders gehandhabt als eine reguläre Route.

> **Hinweis:** Streaming-Antworten ist nur verfügbar, wenn [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf false gesetzt ist.

### Stream with Manual Headers

Sie können eine Antwort an den Client streamen, indem Sie die `stream()`-Methode auf einer Route verwenden. Wenn Sie 
das tun, müssen Sie alle Header manuell setzen, bevor Sie etwas an den Client ausgeben.
Das wird mit der `header()`-PHP-Funktion oder der `Flight::response()->setRealHeader()`-Methode erledigt.

```php
Flight::route('/@filename', function($filename) {

	// obviously you would sanitize the path and whatnot.
	$fileNameSafe = basename($filename);

	// If you have additional headers to set here after the route has executed
	// you must define them before anything is echoed out.
	// They must all be a raw call to the header() function or 
	// a call to Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// manually set the content length if you'd like
	header('Content-Length: '.filesize($filePath));

	// Stream the file to the client as it's read
	readfile($filePath);

// This is the magic line here
})->stream();
```

### Stream with Headers

Sie können auch die `streamWithHeaders()`-Methode verwenden, um die Header zu setzen, bevor Sie mit dem Streamen beginnen.

```php
Flight::route('/stream-users', function() {

	// you can add any additional headers you want here
	// you just must use header() or Flight::response()->setRealHeader()

	// however you pull your data, just as an example...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optional status code, defaults to 200
	'status' => 200
]);
```