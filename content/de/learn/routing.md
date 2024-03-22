# Routen

> **Hinweis:** Möchten Sie mehr über Routenverständnis erfahren? Werfen Sie einen Blick auf die ["Warum ein Framework?"](/learn/why-frameworks) Seite für eine detailliertere Erklärung.

Die grundlegende Routenführung in Flight erfolgt durch das Zuordnen eines URL-Musters mit einer Rückruffunktion oder einem Array einer Klasse und Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

> Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste Route, die eine Anfrage abgleicht, wird aufgerufen.

### Rückrufe/Funktionen
Der Rückruf kann jedes Objekt sein, das aufrufbar ist. Sie können also eine reguläre Funktion verwenden:

```php
function hallo(){
    echo 'Hallo Welt!';
}

Flight::route('/', 'hallo');
```

### Klassen
Sie können auch eine statische Methode einer Klasse verwenden:

```php
class Begrüßung {
    public static function hallo() {
        echo 'Hallo Welt!';
    }
}

Flight::route('/', [ 'Begrüßung','hallo' ]);
```

Oder indem Sie zuerst ein Objekt erstellen und dann die Methode aufrufen:

```php

// Greeting.php
class Begrüßung
{
    public function __construct() {
        $this->name = 'Max Mustermann';
    }

    public function hallo() {
        echo "Hallo, {$this->name}!";
    }
}

// index.php
$begruessung = new Begrüßung();

Flight::route('/', [ $begruessung, 'hallo' ]);
// Sie können dies auch tun, ohne das Objekt zuerst zu erstellen
// Hinweis: Es werden keine Argumente in den Konstruktor eingesetzt
Flight::route('/', [ 'Begrüßung', 'hallo' ]);
```

#### Dependency Injection via DIC (Dependency Injection Container)
Wenn Sie die Dependency Injection über einen Container verwenden möchten (PSR-11, PHP-DI, Dice, etc), sind die
einzigen Arten von Routen, bei denen dies verfügbar ist, entweder das direkte Erstellen des Objekts selbst
und Verwendung des Containers zur Erstellung Ihres Objekts oder Sie können Zeichenfolgen verwenden, um die Klasse und
Methode zum Aufrufen zu definieren. Sie können zur [Dependency Injection](/learn/extending) Seite gehen
für weitere Informationen.

Hier ist ein schnelles Beispiel:

```php

use flight\database\PdoWrapper;

// Begrüßung.php
class Begrüßung
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hallo(int $id) {
		// etwas mit $this->pdoWrapper machen
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hallo, Welt! Mein Name ist {$name}!";
	}
}

// index.php

// Container mit den erforderlichen Parametern einrichten
// Siehe die Dependency Injection-Seite für mehr Informationen zu PSR-11
$dice = new \Dice\Dice();

// Vergessen Sie nicht, die Variable mit '$dice = 'neu zuzuweisen!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'passwort'
	]
]);

// Container-Handler registrieren
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routen wie gewohnt
Flight::route('/hallo/@id', [ 'Begrüßung', 'hallo' ]);
// oder
Flight::route('/hallo/@id', 'Begrüßung->hallo');
// oder
Flight::route('/hallo/@id', 'Begrüßung::hallo');

Flight::start();
```

## Methoden-Routen

Standardmäßig werden Routenmuster gegen alle Anforderungsmethoden abgeglichen. Sie können
auf bestimmte Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anforderung erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anforderung erhalten.';
});

// Sie können Flight::get() nicht für Routen verwenden, da dies eine Methode ist
//     um Variablen zu erhalten, nicht um eine Route zu erstellen.
// Flight::post('/', function() { /* Code */ });
// Flight::patch('/', function() { /* Code */ });
// Flight::put('/', function() { /* Code */ });
// Flight::delete('/', function() { /* Code */ });
```

Sie können auch mehrere Methoden auf einen einzigen Rückruf abbilden, indem Sie einen `|`-Trenner verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anforderung erhalten.';
});
```

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden zum Verwenden hat:

```php

$router = Flight::router();

// Verknüpft alle Methoden
$router->map('/', function() {
	echo 'Hallo Welt!';
});

// GET-Anforderung
$router->get('/benutzer', function() {
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
Flight::route('/benutzer/[0-9]+', function () {
  // Dies passt zu /benutzer/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder
benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu warten sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an
Ihre Rückruffunktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern verwenden, indem Sie
den `:`-Trenner verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies passt zu /bob/123
  // Passt aber nicht zu /bob/12345
});
```

> **Hinweis:** Das Abgleichen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt. :'\(

## Optionale Parameter

Sie können benannte Parameter angeben, die optional für den Abgleich sind, indem Sie
Segmente in Klammern einschließen.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Dies passt zu folgenden URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Nicht übereinstimmende optionale Parameter werden als `NULL` übergeben.

## Platzhalter

Das Abgleichen erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere
Segmenten abgleichen möchten, können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies passt zu /blog/2000/02/01
});
```

Um alle Anforderungen an einen einzelnen Rückruf zu leiten, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Mach etwas
});
```

## Weiterleitung

Sie können die Ausführung an die nächste übereinstimmende Route weiterleiten, indem Sie `true` aus
Ihrer Rückruffunktion zurückgeben.

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Eine Bedingung prüfen
  if ($name !== "Bob") {
    // Zu nächster Route fortsetzen
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```

## Routenalias

Sie können einem Routen einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (z. B. in einer Vorlage).

```php
Flight::route('/benutzer/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'benutzer_ansehen');

// später im Code irgendwo
Flight::getUrl('benutzer_ansehen', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders hilfreich, wenn sich Ihre URL ändern sollte. Im obigen Beispiel, nehmen wir an, dass Benutzer zu `/admin/benutzer/@id` verschoben wurde.
Mit Aliasbildung müssen Sie nirgendwo, wo Sie auf den Alias verweisen, etwas ändern, da der Alias jetzt `/admin/benutzer/5` wie im
obigen Beispiel zurückgeben wird.

Routenaliasierung funktioniert auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'benutzer_ansehen');
});


// später im Code irgendwo
Flight::getUrl('benutzer_ansehen', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routeninformationen

Wenn Sie die übereinstimmenden Routeninformationen inspizieren möchten, können Sie die Routen
Objekt anfordern, das an Ihre Rückruffunktion übergeben werden soll, indem Sie `true` als dritten Parameter in
dem Routenmethode. Das Routenobjekt wird immer der letzte Parameter sein, der an Ihre
Rückruffunktion übergeben wird.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der HTTP-Methoden, die abgeglichen sind
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von * in dem URL-Muster
  $route->splat;

  // Zeigt den URL-Pfad an....wenn Sie dies wirklich benötigen
  $route->pattern;

  // Zeigt an, welches Middleware dieser Route zugewiesen ist
  $route->middleware;

  // Zeigt den diesem Routen zugewiesenen Alias an
  $route->alias;
}, true);
```

## Routengruppierung

Es kann Zeiten geben, in denen Sie verwandte Routen zusammenfassen möchten (z. B. `/api/v1`).
Sie können dies durch Verwendung der `group` Methode tun:

```php
Flight::group('/api/v1', function () {
  Flight::route('/benutzer', function () {
	// Passt zu /api/v1/benutzer
  });

  Flight::route('/posten', function () {
	// Passt zu /api/v1/posten
  });
});
```

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() ruft Variablen ab, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Passt zu GET /api/v1/benutzer
	});

	Flight::post('/posten', function () {
	  // Passt zu POST /api/v1/posten
	});

	Flight::put('/posten/1', function () {
	  // Passt zu PUT /api/v1/posten
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() ruft Variablen ab, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Passt zu GET /api/v2/benutzer
	});
  });
});
```

### Gruppierung mit Objektkontext

Sie können weiterhin Routengruppierung mit dem `Engine`-Objekt auf die folgende Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // Verwenden Sie die $router Variable
  $router->get('/benutzer', function () {
	// Passt zu GET /api/v1/benutzer
  });

  $router->post('/posten', function () {
	// Passt zu POST /api/v1/posten
  });
});
```

## Streamen

Sie können jetzt Antworten an den Client streamen, indem Sie die `streamWithHeaders()`-Methode verwenden.
Dies ist nützlich zum Senden großer Dateien, länger laufender Prozesse oder zur Erzeugung großer Antworten.
Das Streamen einer Route wird etwas anders behandelt als eine reguläre Route.

> **Hinweis:** Das Streamen von Antworten ist nur verfügbar, wenn Sie [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf false gesetzt haben.

```php
Flight::route('/benutzer-streamen', function() {

	// Wenn Sie hier nach Ausführung der Route zusätzliche Header setzen müssen
	// müssen Sie sie definieren, bevor irgendetwas ausgegeben wird.
	// Sie müssen alle ein direkter Aufruf der header() Funktion oder
	// ein Aufruf von Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="benutzer.json"');
	// oder
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="benutzer.json"');

	// wie auch immer Sie Ihre Daten abrufen, nur als Beispiel...
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

// So setzen Sie die Header, bevor Sie mit dem Streamen beginnen.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// optionaler Statuscode, Standard: 200
	'status' => 200
]);
```