# Routing

## Überblick
Routing in Flight PHP ordnet URL-Muster Callback-Funktionen oder Klassenmethoden zu, um schnelle und einfache Anfragenverarbeitung zu ermöglichen. Es ist für minimalen Overhead, benutzerfreundliche Nutzung für Anfänger und Erweiterbarkeit ohne externe Abhängigkeiten konzipiert.

## Verständnis
Routing ist der Kernmechanismus, der HTTP-Anfragen mit der Anwendungslogik in Flight verbindet. Durch das Definieren von Routen legen Sie fest, wie verschiedene URLs spezifischen Code auslösen, sei es durch Funktionen, Klassenmethoden oder Controller-Aktionen. Das Routing-System von Flight ist flexibel und unterstützt grundlegende Muster, benannte Parameter, reguläre Ausdrücke sowie erweiterte Funktionen wie Dependency Injection und ressourcenorientiertes Routing. Dieser Ansatz hält Ihren Code organisiert und einfach zu warten, während er für Anfänger schnell und einfach bleibt und für fortgeschrittene Nutzer erweiterbar ist.

> **Hinweis:** Möchten Sie mehr über Routing erfahren? Schauen Sie sich die Seite ["why a framework?"](/learn/why-frameworks) für eine detailliertere Erklärung an.

## Grundlegende Nutzung

### Definieren einer einfachen Route
Grundlegendes Routing in Flight erfolgt durch das Abgleichen eines URL-Musters mit einer Callback-Funktion oder einem Array aus einer Klasse und Methode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Routen werden in der Reihenfolge abgeglichen, in der sie definiert werden. Die erste Route, die zu einer Anfrage passt, wird aufgerufen.

### Verwendung von Funktionen als Callbacks
Der Callback kann jedes aufrufbare Objekt sein. Sie können also eine reguläre Funktion verwenden:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Verwendung von Klassen und Methoden als Controller
Sie können auch eine Methode (statisch oder nicht) einer Klasse verwenden:

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// oder
Flight::route('/', [ GreetingController::class, 'hello' ]); // bevorzugte Methode
// oder
Flight::route('/', [ 'GreetingController::hello' ]);
// oder 
Flight::route('/', [ 'GreetingController->hello' ]);
```

Oder indem Sie zuerst ein Objekt erstellen und dann die Methode aufrufen:

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

> **Hinweis:** Standardmäßig wird beim Aufruf eines Controllers im Framework die Klasse `flight\Engine` immer injiziert, es sei denn, Sie spezifizieren es über einen [Dependency Injection Container](/learn/dependency-injection-container).

### Methode-spezifisches Routing

Standardmäßig werden Routenmuster gegen alle Anfragemethoden abgeglichen. Sie können auf spezifische Methoden reagieren, indem Sie einen Bezeichner vor die URL setzen.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

Sie können auch mehrere Methoden auf einen einzelnen Callback abbilden, indem Sie den `|`-Trenner verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Verwendung des Router-Objekts

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie bietet:

```php

$router = Flight::router();

// maps all methods just like Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// GET request
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Reguläre Ausdrücke (Regex)
Sie können reguläre Ausdrücke in Ihren Routen verwenden:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu warten sind.

### Benannte Parameter
Sie können benannte Parameter in Ihren Routen spezifizieren, die an Ihre Callback-Funktion weitergegeben werden. **Dies dient hauptsächlich der Lesbarkeit der Route. Bitte sehen Sie den Abschnitt unten zu wichtigen Einschränkungen.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern kombinieren, indem Sie den `:`-Trenner verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Hinweis:** Das Abgleichen von Regex-Gruppen `()` mit positionsbasierten Parametern wird nicht unterstützt. Beispiel: `:'\(`

#### Wichtige Einschränkung

Im obigen Beispiel scheint es, als ob `@name` direkt mit der Variable `$name` verknüpft ist, aber das ist nicht der Fall. Die Reihenfolge der Parameter in der Callback-Funktion bestimmt, was an sie weitergegeben wird. Wenn Sie die Reihenfolge der Parameter in der Callback-Funktion umkehren, werden auch die Variablen umgekehrt. Hier ein Beispiel:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Und wenn Sie zur folgenden URL gehen: `/bob/123`, wäre die Ausgabe `hello, 123 (bob)!`. 
_Seien Sie bitte vorsichtig_, wenn Sie Ihre Routen und Callback-Funktionen einrichten!

### Optionale Parameter
Sie können benannte Parameter spezifizieren, die optional für das Abgleichen sind, indem Sie Segmente in Klammern setzen.

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

Jede optionale Parameter, die nicht abgeglichen werden, wird als `NULL` weitergegeben.

### Wildcard-Routing
Das Abgleichen erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere Segmente abgleichen möchten, können Sie das `*`-Wildcard verwenden.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

Um alle Anfragen an einen einzelnen Callback zu leiten, können Sie tun:

```php
Flight::route('*', function () {
  // Do something
});
```

### 404 Not Found Handler

Standardmäßig sendet Flight bei einer nicht gefundenen URL eine sehr einfache und schlichte `HTTP 404 Not Found`-Antwort.
Wenn Sie eine personalisiertere 404-Antwort haben möchten, können Sie Ihre eigene `notFound`-Methode [abbilden](/learn/extending):

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// You could also use Flight::render() with a custom template.
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

## Erweiterte Nutzung

### Dependency Injection in Routen
Wenn Sie Dependency Injection über einen Container (PSR-11, PHP-DI, Dice usw.) verwenden möchten, ist der einzige Routentyp, bei dem das verfügbar ist, entweder das direkte Erstellen des Objekts selbst und die Verwendung des Containers, um Ihr Objekt zu erstellen, oder Sie können Strings verwenden, um die Klasse und Methode zum Aufrufen zu definieren. Sie können zur [Dependency Injection](/learn/dependency-injection-container)-Seite für weitere Informationen gehen. 

Hier ein kurzes Beispiel:

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

### Übergabe der Ausführung an die nächste Route
<span class="badge bg-warning">Veraltet</span>
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

Es wird nun empfohlen, [Middleware](/learn/middleware) für komplexe Anwendungsfälle wie diesen zu verwenden.

### Route-Aliasing
Durch das Zuweisen eines Aliases zu einer Route können Sie diesen Alias später dynamisch in Ihrer App aufrufen, um ihn später in Ihrem Code zu generieren (z. B. ein Link in einer HTML-Vorlage oder das Generieren einer Weiterleitungs-URL).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// or 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// later in code somewhere
class UserController {
	public function update() {

		// code to save user...
		$id = $user['id']; // 5 for example

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // will return '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Dies ist besonders hilfreich, wenn sich Ihre URL ändert. Im obigen Beispiel, sagen wir, dass Benutzer zu `/admin/users/@id` verschoben wurden.
Mit Aliasing an Ort und Stelle müssen Sie nicht mehr alle alten URLs in Ihrem Code finden und ändern, da der Alias nun `/admin/users/5` zurückgibt, wie im obigen Beispiel.

Route-Aliasing funktioniert auch in Gruppen:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// or
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Überprüfen von Routeninformationen
Wenn Sie die passende Routeninformation überprüfen möchten, gibt es 2 Wege, dies zu tun:

1. Sie können die `executedRoute`-Eigenschaft auf dem `Flight::router()`-Objekt verwenden.
2. Sie können das Routenobjekt anfordern, das an Ihren Callback übergeben wird, indem Sie `true` als dritten Parameter in der Routenmethode übergeben. Das Routenobjekt wird immer der letzte Parameter sein, der an Ihre Callback-Funktion übergeben wird.

#### `executedRoute`
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

> **Hinweis:** Die `executedRoute`-Eigenschaft wird nur gesetzt, nachdem eine Route ausgeführt wurde. Wenn Sie versuchen, sie vor der Ausführung einer Route abzurufen, ist sie `NULL`. Sie können executedRoute auch in [Middleware](/learn/middleware) verwenden!

#### `true` in der Routendefinition übergeben
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
}, true);// <-- Dieser true-Parameter ist das, was das bewirkt
```

### Routengruppierung und Middleware
Es kann vorkommen, dass Sie verwandte Routen gruppieren möchten (z. B. `/api/v1`).
Sie können dies tun, indem Sie die `group`-Methode verwenden:

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

Sie können sogar Gruppen von Gruppen verschachteln:

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

#### Gruppierung mit Objektkontext

Sie können die Routengruppierung immer noch mit dem `Engine`-Objekt auf folgende Weise verwenden:

```php
$app = Flight::app();

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

> **Hinweis:** Dies ist die bevorzugte Methode, um Routen und Gruppen mit dem `$router`-Objekt zu definieren.

#### Gruppierung mit Middleware

Sie können auch Middleware einer Gruppe von Routen zuweisen:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

Weitere Details finden Sie auf der [Group Middleware](/learn/middleware#grouping-middleware)-Seite.

### Ressourcen-Routing
Sie können eine Reihe von Routen für eine Ressource mit der `resource`-Methode erstellen. Dies erstellt eine Reihe von Routen für eine Ressource, die den RESTful-Konventionen folgt.

Um eine Ressource zu erstellen, tun Sie Folgendes:

```php
Flight::resource('/users', UsersController::class);
```

Und im Hintergrund werden die folgenden Routen erstellt:

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

Und Ihr Controller wird die folgenden Methoden verwenden:

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

> **Hinweis**: Sie können die neu hinzugefügten Routen mit `runway` anzeigen, indem Sie `php runway routes` ausführen.

#### Anpassen von Ressourcen-Routen

Es gibt einige Optionen, um die Ressourcen-Routen zu konfigurieren.

##### Alias-Basis

Sie können die `aliasBase` konfigurieren. Standardmäßig ist der Alias der letzte Teil der angegebenen URL.
Zum Beispiel würde `/users/` zu einem `aliasBase` von `users` führen. Wenn diese Routen erstellt werden,
sind die Aliase `users.index`, `users.create` usw. Wenn Sie den Alias ändern möchten, setzen Sie `aliasBase`
auf den gewünschten Wert.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only und Except

Sie können auch spezifizieren, welche Routen Sie erstellen möchten, indem Sie die Optionen `only` und `except` verwenden.

```php
// Whitelist only these methods and blacklist the rest
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Blacklist only these methods and whitelist the rest
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Dies sind im Wesentlichen Whitelist- und Blacklist-Optionen, damit Sie spezifizieren können, welche Routen Sie erstellen möchten.

##### Middleware

Sie können auch Middleware spezifizieren, die auf jeder der Routen ausgeführt wird, die von der `resource`-Methode erstellt werden.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Streaming-Antworten

Sie können jetzt Antworten an den Client streamen, indem Sie `stream()` oder `streamWithHeaders()` verwenden. 
Dies ist nützlich zum Senden großer Dateien, lang laufender Prozesse oder zum Generieren großer Antworten. 
Das Streamen einer Route wird etwas anders gehandhabt als eine reguläre Route.

> **Hinweis:** Streaming-Antworten sind nur verfügbar, wenn Sie [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf `false` gesetzt haben.

#### Stream mit manuellen Headers

Sie können eine Antwort an den Client streamen, indem Sie die `stream()`-Methode auf einer Route verwenden. Wenn Sie 
das tun, müssen Sie alle Headers manuell setzen, bevor Sie etwas an den Client ausgeben.
Dies geschieht mit der `header()`-PHP-Funktion oder der `Flight::response()->setRealHeader()`-Methode.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// obviously you would sanitize the path and whatnot.
	$fileNameSafe = basename($filename);

	// If you have additional headers to set here after the route has executed
	// you must define them before anything is echoed out.
	// They must all be a raw call to the header() function or 
	// a call to Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// manually set the content length if you'd like
	header('Content-Length: '.filesize($filePath));
	// or
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Stream the file to the client as it's read
	readfile($filePath);

// This is the magic line here
})->stream();
```

#### Stream mit Headers

Sie können auch die `streamWithHeaders()`-Methode verwenden, um die Headers zu setzen, bevor Sie mit dem Streamen beginnen.

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

## Siehe auch
- [Middleware](/learn/middleware) - Verwendung von Middleware mit Routen für Authentifizierung, Logging usw.
- [Dependency Injection](/learn/dependency-injection-container) - Vereinfachung der Objekterstellung und -verwaltung in Routen.
- [Why a Framework?](/learn/why-frameworks) - Verständnis der Vorteile der Verwendung eines Frameworks wie Flight.
- [Erweitern](/learn/extending) - Wie man Flight mit eigener Funktionalität erweitert, einschließlich der `notFound`-Methode.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - PHP-Funktion für reguläre Ausdrucksabgleichung.

## Fehlerbehebung
- Routenparameter werden nach Reihenfolge abgeglichen, nicht nach Name. Stellen Sie sicher, dass die Reihenfolge der Callback-Parameter zur Routendefinition passt.
- Die Verwendung von `Flight::get()` definiert keine Route; verwenden Sie `Flight::route('GET /...')` für Routing oder den Router-Objektkontext in Gruppen (z. B. `$router->get(...)`).
- Die executedRoute-Eigenschaft wird nur nach der Ausführung einer Route gesetzt; sie ist NULL vor der Ausführung.
- Streaming erfordert, dass die Legacy-Flight-Output-Buffering-Funktionalität deaktiviert ist (`flight.v2.output_buffering = false`).
- Für Dependency Injection unterstützen nur bestimmte Routendefinitionen containerbasierte Instanziierung.

### 404 Not Found oder unerwartetes Routenverhalten

Wenn Sie einen 404 Not Found-Fehler sehen (aber Sie schwören bei Ihrem Leben, dass er wirklich da ist und es kein Tippfehler ist), könnte dies tatsächlich ein Problem damit sein, 
dass Sie einen Wert aus Ihrem Routen-Endpunkt zurückgeben, anstatt ihn nur auszugeben. Der Grund dafür ist absichtlich, könnte aber einige Entwickler überraschen.

```php

Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Hello World';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

Der Grund dafür ist ein spezieller Mechanismus, der in den Router eingebaut ist und die Rückgabeausgabe als Signal behandelt, um "zur nächsten Route zu gehen". 
Sie können das Verhalten in dem [Routing](/learn/routing#passing)-Abschnitt dokumentiert finden.

## Änderungsprotokoll
- v3: Hinzugefügt: Ressourcen-Routing, Route-Aliasing und Streaming-Unterstützung, Routengruppen und Middleware-Unterstützung.
- v1: Der Großteil der grundlegenden Funktionen ist verfügbar.