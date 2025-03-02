# Routing

> **Hinweis:** Möchten Sie mehr über das Routing erfahren? Schauen Sie sich die ["Warum ein Framework?"](/learn/why-frameworks) Seite für eine tiefere Erklärung an.

Das grundlegende Routing in Flight erfolgt durch das Abgleichen eines URL-Musters mit einer Callback-Funktion oder einem Array aus einer Klasse und einer Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

> Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste Route, die mit einer Anfrage übereinstimmt, wird aufgerufen.

### Callbacks/Funktionen
Der Callback kann jedes aufrufbare Objekt sein. Sie können also eine normale Funktion verwenden:

```php
function hello() {
    echo 'Hallo Welt!';
}

Flight::route('/', 'hello');
```

### Klassen
Sie können auch eine statische Methode einer Klasse verwenden:

```php
class Greeting {
    public static function hello() {
        echo 'Hallo Welt!';
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
        echo "Hallo, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Sie können dies auch tun, ohne das Objekt zuerst zu erstellen
// Hinweis: Es werden keine Argumente in den Konstruktor injiziert
Flight::route('/', [ 'Greeting', 'hello' ]);
// Zusätzlich können Sie diese kürzere Syntax verwenden
Flight::route('/', 'Greeting->hello');
// oder
Flight::route('/', Greeting::class.'->hello');
```

#### Abhängigkeitsinjektion über DIC (Dependency Injection Container)
Wenn Sie die Abhängigkeitsinjektion über einen Container (PSR-11, PHP-DI, Dice usw.) verwenden möchten,
ist die einzige Art von Routen, wo dies verfügbar ist, entweder durch die direkte Erstellung des Objekts selbst
und die Verwendung des Containers zur Erstellung Ihres Objekts oder indem Sie Strings verwenden, um die Klasse und
methode zu definieren, die aufgerufen werden sollen. Sie können zur [Abhängigkeitsinjektion](/learn/extending) Seite gehen für 
weitere Informationen. 

Hier ist ein schnelles Beispiel:

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
		// Mach etwas mit $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hallo, Welt! Mein Name ist {$name}!";
	}
}

// index.php

// Richten Sie den Container mit den benötigten Parametern ein
// Siehe die Seite zur Abhängigkeitsinjektion für weitere Informationen zu PSR-11
$dice = new \Dice\Dice();

// Vergessen Sie nicht, die Variable mit '$dice = ' neu zuzuweisen!!!!!
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

## Methoden-Routing

Standardmäßig werden Routenmuster mit allen Anfrage-Methoden abgeglichen. Sie können auf spezifische Methoden reagieren, indem Sie einen Bezeichner vor die URL setzen.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anfrage erhalten.';
});

// Sie können Flight::get() für Routen nicht verwenden, da dies eine Methode ist 
//    um Variablen zu erhalten, nicht um eine Route zu erstellen.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Sie können auch mehrere Methoden auf einen einzigen Callback abbilden, indem Sie ein `|` Trennzeichen verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anfrage erhalten.';
});
```

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie hat:

```php

$router = Flight::router();

// mappt alle Methoden
$router->map('/', function() {
	echo 'Hallo Welt!';
});

// GET-Anfrage
$router->get('/users', function() {
	echo 'Benutzer';
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
  // Dies wird /user/1234 abgleichen
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder 
benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu warten sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an
Ihre Callback-Funktion übergeben werden. **Das dient mehr der Lesbarkeit der Route als allem anderen. Bitte sehen Sie den Abschnitt unten über wichtige Vorbehalte.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einschließen, indem Sie das `:` Trennzeichen verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies wird /bob/123 abgleichen
  // Wird aber nicht abgleichen mit /bob/12345
});
```

> **Hinweis:** Das Abgleichen von Regex-Gruppen `()` mit Positionsparametern wird nicht unterstützt. :'\(

### Wichtiger Vorbehalt

Obwohl im obigen Beispiel scheint, dass `@name` direkt an die Variable `$name` gebunden ist, ist dem nicht so. Die Reihenfolge der Parameter in der Callback-Funktion bestimmt, was an sie übergeben wird. Wenn Sie also die Reihenfolge der Parameter in der Callback-Funktion wechseln, würden sich auch die Variablen ändern. Hier ist ein Beispiel:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "Hallo, $name ($id)!";
});
```

Und wenn Sie zur folgenden URL gehen: `/bob/123`, wäre die Ausgabe `Hallo, 123 (bob)!`. 
Bitte seien Sie vorsichtig, wenn Sie Ihre Routen und Callback-Funktionen einrichten.

## Optionale Parameter

Sie können benannte Parameter angeben, die optional für die Übereinstimmung sind, indem Sie Segmente in Klammern setzen.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Dies wird die folgenden URLs abgleichen:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Alle optionalen Parameter, die nicht übereinstimmen, werden als `NULL` übergeben.

## Wildcards

Die Übereinstimmung erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere
Segmente abgleichen möchten, können Sie das `*` Wildcard verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies wird /blog/2000/02/01 abgleichen
});
```

Um alle Anfragen an einen einzigen Callback zu routen, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Mach irgendetwas
});
```

## Übergabe

Sie können die Ausführung an die nächste übereinstimmende Route weitergeben, indem Sie `true` aus
Ihrer Callback-Funktion zurückgeben.

```php
Flight::route('/user/@name', function (string $name) {
  // Überprüfen Sie eine Bedingung
  if ($name !== "Bob") {
    // Fahren Sie mit der nächsten Route fort
    return true;
  }
});

Flight::route('/user/*', function () {
  // Dies wird aufgerufen
});
```

## Route-Aliasing

Sie können einer Route ein Alias zuweisen, damit die URL später dynamisch in Ihrem Code generiert werden kann (wie zum Beispiel in einer Vorlage).

```php
Flight::route('/users/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_view');

// später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // wird '/users/5' zurückgeben
```

Dies ist besonders hilfreich, wenn sich Ihre URL ändert. Im obigen Beispiel, nehmen wir an, dass Benutzer nach `/admin/users/@id` verschoben wurden.
Mit dem Alias müssen Sie nicht überall, wo Sie auf den Alias verweisen, Änderungen vornehmen, weil der Alias jetzt `/admin/users/5` zurückgibt, wie im obigen 
Beispiel.

Route-Aliasing funktioniert auch in Gruppen:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_view');
});

// später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // wird '/users/5' zurückgeben
```

## Routeninfo

Wenn Sie die übereinstimmenden Routeninformationen inspizieren möchten, können Sie anfordern, dass das Routenobjekt an Ihre Callback-Funktion übergeben wird, indem Sie `true` als drittes Parameter in der Routenmethode übergeben. Das Routenobjekt wird immer das letzte Parameter sein, das an Ihre Callback-Funktion übergeben wird.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der HTTP-Methoden, die abgeglichen wurden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von '*' , der im URL-Muster verwendet wurde
  $route->splat;

  // Zeigt den URL-Pfad an.... wenn Sie es wirklich benötigen
  $route->pattern;

  // Zeigt, welches Middleware zugewiesen ist
  $route->middleware;

  // Zeigt den Alias an, der dieser Route zugewiesen ist
  $route->alias;
}, true);
```

## Routen Gruppierung

Es kann Zeiten geben, in denen Sie verwandte Routen zusammen gruppieren möchten (wie `/api/v1`).
Sie können dies tun, indem Sie die `group` Methode verwenden:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Entspricht /api/v1/users
  });

  Flight::route('/posts', function () {
	// Entspricht /api/v1/posts
  });
});
```

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() erhält Variablen, es setzt keine Route! Siehe Objektkontext unten
	Flight::route('GET /users', function () {
	  // Entspricht GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Entspricht POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Entspricht PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() erhält Variablen, es setzt keine Route! Siehe Objektkontext unten
	Flight::route('GET /users', function () {
	  // Entspricht GET /api/v2/users
	});
  });
});
```

### Gruppierung mit Objektkontext

Sie können die Routen-Gruppierung auch mit dem `Engine`-Objekt in folgender Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // Verwenden Sie die $router-Variable
  $router->get('/users', function () {
	// Entspricht GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Entspricht POST /api/v1/posts
  });
});
```

## Ressourcen-Routing

Sie können eine Reihe von Routen für eine Ressource mit der Methode `resource` erstellen. Dies erstellt
eine Reihe von Routen für eine Ressource, die den RESTful-Konventionen folgt.

Um eine Ressource zu erstellen, tun Sie Folgendes:

```php
Flight::resource('/users', UsersController::class);
```

Und was im Hintergrund passieren wird, ist, dass die folgenden Routen erstellt werden:

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

Und Ihr Controller wird so aussehen:

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

### Anpassen von Ressourcenrouten

Es gibt einige Optionen zur Konfiguration der Ressourcenrouten.

#### Alias-Basis

Sie können die `aliasBase` konfigurieren. Standardmäßig ist der Alias der letzte Teil der angegebenen URL.
Zum Beispiel würde `/users/` in einem `aliasBase` von `users` resultieren. Wenn diese Routen erstellt werden,
sind die Aliase `users.index`, `users.create` usw. Wenn Sie den Alias ändern möchten, setzen Sie `aliasBase`
auf den Wert, den Sie möchten.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Nur und Ausgeschlossen

Sie können auch angeben, welche Routen Sie erstellen möchten, indem Sie die Optionen `only` und `except` verwenden.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Dies sind im Grunde Optionen zum Whitelisting und Blacklisting, damit Sie angeben können, welche Routen Sie erstellen möchten.

#### Middleware

Sie können auch Middleware angeben, die für jede der durch die Methode `resource` erstellten Routen ausgeführt wird.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Sie können jetzt Antworten an den Client senden, indem Sie die Methode `streamWithHeaders()` verwenden. 
Das ist nützlich, um große Dateien, lang laufende Prozesse oder große Antworten zu senden. 
Das Streamen einer Route wird etwas anders behandelt als eine reguläre Route.

> **Hinweis:** Streaming-Antworten sind nur verfügbar, wenn Sie [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf false gesetzt haben.

### Stream mit manuellen Headern

Sie können eine Antwort an den Client streamen, indem Sie die Methode `stream()` für eine Route verwenden. Wenn Sie 
das tun, müssen Sie alle Methoden manuell setzen, bevor Sie irgendetwas an den Client ausgeben.
Dies geschieht mit der PHP-Funktion `header()` oder der Methode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// Offensichtlich würden Sie den Pfad und dergleichen sanieren.
	$fileNameSafe = basename($filename);

	// Wenn Sie hier nach der Ausführung der Route zusätzliche Header setzen möchten
	// müssen Sie sie definieren, bevor etwas ausgegeben wird.
	// Sie müssen alle einen rohen Aufruf der `header()` Funktion oder 
	// einen Aufruf von `Flight::response()->setRealHeader()` sein.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// oder
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Fehlerbehandlung und dergleichen
	if(empty($fileData)) {
		Flight::halt(404, 'Datei nicht gefunden');
	}

	// setzen Sie manuell die Inhaltlänge, wenn Sie möchten
	header('Content-Length: '.filesize($filename));

	// Streamen Sie die Daten an den Client
	echo $fileData;

// Das ist die magische Zeile hier
})->stream();
```

### Stream mit Headern

Sie können auch die Methode `streamWithHeaders()` verwenden, um die Header zu setzen, bevor Sie mit dem Streaming beginnen.

```php
Flight::route('/stream-users', function() {

	// Sie können hier beliebige zusätzliche Header hinzufügen
	// Sie müssen entweder `header()` oder `Flight::response()->setRealHeader()` verwenden

	// Wie auch immer Sie Ihre Daten abrufen, nur als Beispiel...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Dies ist erforderlich, um die Daten an den Client zu senden
		ob_flush();
	}
	echo '}';

// So setzen Sie die Header, bevor Sie mit dem Streaming beginnen.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optionaler Statuscode, Standardwert ist 200
	'status' => 200
]);
```