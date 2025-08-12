# Routing

> **Hinweis:** Möchten Sie mehr über Routing verstehen? Schauen Sie sich die Seite ["why a framework?"](/learn/why-frameworks) für eine detailliertere Erklärung an.

Grundlegende Routing in Flight erfolgt durch Abgleich eines URL-Musters mit einer Callback-Funktion oder einem Array aus einer Klasse und einer Methode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste Route, die zu einer Anfrage passt, wird aufgerufen.

### Callbacks/Funktionen
Der Callback kann jedes aufrufbare Objekt sein. Sie können also eine reguläre Funktion verwenden:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Klassen
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
// Sie können das auch tun, ohne zuerst das Objekt zu erstellen
// Hinweis: Es werden keine Argumente in den Konstruktor injiziert
Flight::route('/', [ 'Greeting', 'hello' ]);
// Zusätzlich können Sie diese kürzere Syntax verwenden
Flight::route('/', 'Greeting->hello');
// oder
Flight::route('/', Greeting::class.'->hello');
```

#### Abhängigkeitsinjektion über DIC (Dependency Injection Container)
Wenn Sie Abhängigkeitsinjektion über einen Container (PSR-11, PHP-DI, Dice usw.) verwenden möchten, ist dies nur für Routen verfügbar, bei denen Sie das Objekt direkt erstellen und den Container verwenden, um Ihr Objekt zu erstellen, oder bei denen Sie Strings verwenden, um die Klasse und Methode zu definieren. Weitere Informationen finden Sie auf der Seite [Dependency Injection](/learn/extending).

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
		// etwas mit $this->pdoWrapper machen
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Richten Sie den Container mit den erforderlichen Parametern ein
// Siehe die Seite Dependency Injection für mehr Informationen zu PSR-11
$dice = new \Dice\Dice();

// Vergessen Sie nicht, die Variable mit '$dice =' neu zuzuweisen!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Registrieren Sie den Container-Handler
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routen wie gewohnt
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// oder
Flight::route('/hello/@id', 'Greeting->hello');
// oder
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Method Routing

Standardmäßig werden Routen-Muster mit allen Anfragemethoden abgeglichen. Sie können auf spezifische Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Sie können Flight::get() nicht für Routen verwenden, da dies eine Methode ist, 
// um Variablen abzurufen, nicht um eine Route zu erstellen.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Sie können auch mehrere Methoden auf einen einzelnen Callback abbilden, indem Sie einen `|`-Delimter verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie bietet:

```php
$router = Flight::router();

// ordnet alle Methoden zu
$router->map('/', function() {
	echo 'hello world!';
});

// GET-Anfrage
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Reguläre Ausdrücke

Sie können reguläre Ausdrücke in Ihren Routen verwenden:

```php
Flight::route('/user/[0-9]+', function () {
  // Dies passt zu /user/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu warten sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Callback-Funktion weitergegeben werden. **Das dient hauptsächlich der Lesbarkeit der Route. Bitte beachten Sie den wichtigen Hinweis weiter unten.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern kombinieren, indem Sie den `:`-Delimter verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies passt zu /bob/123
  // Aber nicht zu /bob/12345
});
```

> **Hinweis:** Das Abgleichen von Regex-Gruppen `()` mit positionsbezogenen Parametern wird nicht unterstützt. :'\(

### Wichtiger Hinweis

Obwohl im obigen Beispiel erscheint, als ob `@name` direkt mit der Variablen `$name` verbunden ist, ist das nicht der Fall. Die Reihenfolge der Parameter in der Callback-Funktion bestimmt, was an sie weitergegeben wird. Wenn Sie die Reihenfolge der Parameter in der Callback-Funktion ändern, werden die Variablen ebenfalls gewechselt. Hier ein Beispiel:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Und wenn Sie zur folgenden URL gehen: `/bob/123`, wäre die Ausgabe `hello, 123 (bob)!`. 
Seien Sie vorsichtig, wenn Sie Ihre Routen und Callback-Funktionen einrichten.

## Optionale Parameter

Sie können benannte Parameter angeben, die optional für das Abgleichen sind, indem Sie Segmente in Klammern setzen.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Dies passt zu den folgenden URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Jegliche optionale Parameter, die nicht übereinstimmen, werden als `NULL` weitergegeben.

## Platzhalter

Das Abgleichen erfolgt nur für einzelne URL-Segmente. Wenn Sie mehrere Segmente abgleichen möchten, können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies passt zu /blog/2000/02/01
});
```

Um alle Anfragen an einen einzelnen Callback zu leiten, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Etwas tun
});
```

## Weitergabe

Sie können die Ausführung an die nächste passende Route weitergeben, indem Sie `true` aus Ihrer Callback-Funktion zurückgeben.

```php
Flight::route('/user/@name', function (string $name) {
  // Eine Bedingung prüfen
  if ($name !== "Bob") {
    // Zur nächsten Route fortfahren
    return true;
  }
});

Flight::route('/user/*', function () {
  // Dies wird aufgerufen
});
```

## Routen-Aliasing

Sie können einer Route einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (z. B. in einem Template).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// Später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/users/5' zurück
```

Das ist besonders hilfreich, wenn sich Ihre URL ändert. Im obigen Beispiel, sagen wir, dass Benutzer zu `/admin/users/@id` verschoben wurden.
Mit Aliasing müssen Sie nirgendwo ändern, wo Sie den Alias referenzieren, da der Alias jetzt `/admin/users/5` wie im Beispiel zurückgibt.

Routen-Aliasing funktioniert auch in Gruppen:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// Später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/users/5' zurück
```

## Routen-Informationen

Wenn Sie die passende Routen-Information inspizieren möchten, können Sie das Routen-Objekt anfordern, indem Sie `true` als dritten Parameter in der Route-Methode übergeben. Das Routen-Objekt ist immer der letzte Parameter, der an Ihre Callback-Funktion übergeben wird.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der HTTP-Methoden, die abgeglichen wurden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Passender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von jeglichem '*' in dem URL-Muster
  $route->splat;

  // Zeigt den URL-Pfad an....wenn Sie ihn wirklich brauchen
  $route->pattern;

  // Zeigt, welches Middleware diesem zugewiesen ist
  $route->middleware;

  // Zeigt den Alias, der dieser Route zugewiesen ist
  $route->alias;
}, true);
```

## Routen-Gruppierung

Es könnte Fälle geben, in denen Sie verwandte Routen zusammen gruppieren möchten (z. B. `/api/v1`). Sie können das tun, indem Sie die `group`-Methode verwenden:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Passt zu /api/v1/users
  });

  Flight::route('/posts', function () {
	// Passt zu /api/v1/posts
  });
});
```

Sie können sogar Gruppen in Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() holt Variablen ab, es setzt keine Route! Siehe Objekt-Kontext unten
	Flight::route('GET /users', function () {
	  // Passt zu GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Passt zu POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Passt zu PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() holt Variablen ab, es setzt keine Route! Siehe Objekt-Kontext unten
	Flight::route('GET /users', function () {
	  // Passt zu GET /api/v2/users
	});
  });
});
```

### Gruppierung mit Objekt-Kontext

Sie können die Routen-Gruppierung weiterhin mit dem `Engine`-Objekt auf folgende Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // Verwenden Sie die $router-Variable
  $router->get('/users', function () {
	// Passt zu GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Passt zu POST /api/v1/posts
  });
});
```

### Gruppierung mit Middleware

Sie können auch Middleware einer Gruppe von Routen zuweisen:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Passt zu /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // oder [ new MyAuthMiddleware() ], wenn Sie eine Instanz verwenden möchten
```

Weitere Details finden Sie auf der Seite [group middleware](/learn/middleware#grouping-middleware).

## Resource Routing

Sie können eine Reihe von Routen für eine Resource erstellen, indem Sie die `resource`-Methode verwenden. Das erstellt eine Reihe von Routen für eine Resource, die den RESTful-Konventionen folgt.

Um eine Resource zu erstellen, tun Sie Folgendes:

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

> **Hinweis**: Sie können die neu hinzugefügten Routen mit `runway` anzeigen, indem Sie `php runway routes` ausführen.

### Anpassung von Resource-Routen

Es gibt einige Optionen, um die Resource-Routen zu konfigurieren.

#### Alias-Basis

Sie können die `aliasBase` konfigurieren. Standardmäßig ist der Alias der letzte Teil der angegebenen URL.
Zum Beispiel `/users/` ergibt einen `aliasBase` von `users`. Wenn diese Routen erstellt werden,
sind die Aliase `users.index`, `users.create` usw. Wenn Sie den Alias ändern möchten, setzen Sie `aliasBase`
auf den gewünschten Wert.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only und Except

Sie können auch angeben, welche Routen Sie erstellen möchten, indem Sie die Optionen `only` und `except` verwenden.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Das sind im Wesentlichen Whitelisting- und Blacklisting-Optionen, damit Sie angeben können, welche Routen Sie erstellen möchten.

#### Middleware

Sie können auch Middleware angeben, die auf jeder der durch die `resource`-Methode erstellten Routen ausgeführt wird.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Sie können jetzt Antworten an den Client streamen, indem Sie die Methode `streamWithHeaders()` verwenden. 
Das ist nützlich für das Senden großer Dateien, langer Prozesse oder das Generieren großer Antworten. 
Das Streamen einer Route wird ein wenig anders gehandhabt als eine reguläre Route.

> **Hinweis:** Streaming-Antworten ist nur verfügbar, wenn [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf false gesetzt ist.

### Stream mit manuellen Headers

Sie können eine Antwort an den Client streamen, indem Sie die `stream()`-Methode auf einer Route verwenden. In diesem Fall müssen Sie alle Header manuell setzen, bevor Sie etwas an den Client ausgeben.
Das geschieht mit der PHP-Funktion `header()` oder der Methode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// Offensichtlich würden Sie den Pfad und Ähnliches sanitisieren.
	$fileNameSafe = basename($filename);

	// Wenn Sie zusätzliche Header nach der Ausführung der Route setzen möchten
	// müssen Sie sie definieren, bevor etwas ausgegeben wird.
	// Sie müssen alle als direkten Aufruf der header()-Funktion oder 
	// als Aufruf von Flight::response()->setRealHeader() machen.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// oder
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Fehlerabfang und Ähnliches
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// Manuell die Content-Länge setzen, wenn Sie möchten
	header('Content-Length: '.filesize($filename));

	// Die Daten an den Client streamen
	echo $fileData;

// Dies ist die magische Zeile hier
})->stream();
```

### Stream mit Headers

Sie können auch die Methode `streamWithHeaders()` verwenden, um die Header zu setzen, bevor Sie mit dem Streamen beginnen.

```php
Flight::route('/stream-users', function() {

	// Sie können hier alle zusätzlichen Header hinzufügen, die Sie wollen
	// Sie müssen jedoch header() oder Flight::response()->setRealHeader() verwenden

	// Wie auch immer Sie Ihre Daten abrufen, nur als Beispiel...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Das ist erforderlich, um die Daten an den Client zu senden
		ob_flush();
	}
	echo '}';

// So setzen Sie die Header, bevor Sie mit dem Streamen beginnen.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optionaler Statuscode, standardmäßig 200
	'status' => 200
]);
```