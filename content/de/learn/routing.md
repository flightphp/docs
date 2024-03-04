# Routen

> **Hinweis:** Möchten Sie mehr über das Routing erfahren? Schauen Sie sich die ["warum ein Framework?"](/learn/why-frameworks) Seite für eine ausführlichere Erklärung an.

Die grundlegende Routenfunktion in Flight erfolgt durch das Abgleichen eines URL-Musters mit einer Rückruffunktion oder einem Array einer Klasse und einer Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

Der Rückruf kann jedes Objekt sein, das aufrufbar ist. Sie können also eine reguläre Funktion verwenden:

```php
function hallo(){
    echo 'Hallo Welt!';
}

Flight::route('/', 'hallo');
```

Oder eine Klassenmethode:

```php
class Begrüßung {
    public static function hallo() {
        echo 'Hallo Welt!';
    }
}

Flight::route('/', array('Begrüßung','hallo'));
```

Oder eine Objektmethode:

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
$begrüßung = new Begrüßung();

Flight::route('/', array($begrüßung, 'hallo'));
```

Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste übereinstimmende Route für eine Anfrage wird aufgerufen.

## Methoden-Routing

Standardmäßig werden Routenmuster gegen alle Anfrage-Methoden abgeglichen. Sie können auf bestimmte Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anfrage erhalten.';
});
```

Sie können auch mehrere Methoden auf eine einzelne Rückruffunktion mappen, indem Sie einen `|`-Trenner verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder POST-Anfrage erhalten.';
});
```

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie bereitstellt:

```php

$router = Flight::router();

// mapped alle Methoden
$router->map('/', function() {
	echo 'Hallo Welt!';
});

// GET-Anfrage
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
  // Dies entspricht /benutzer/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder benannte Parameter mit regulären Ausdrücken zu verwenden, da sie besser lesbar und einfacher zu pflegen sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Rückruffunktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern verwenden, indem Sie den `:`-Trenner verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies entspricht /bob/123
  // Aber nicht /bob/12345
});
```

> **Hinweis:** Das Übereinstimmen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt. :'\(

## Optionale Parameter

Sie können benannte Parameter angeben, die optional für die Übereinstimmung sind, indem Sie Segmente in Klammern einschließen.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Dies entspricht den folgenden URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Alle nicht übereinstimmenden optionalen Parameter werden als `NULL` übergeben.

## Platzhalter

Die Übereinstimmung erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere Segmente übereinstimmen möchten, können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies entspricht /blog/2000/02/01
});
```

Um alle Anfragen auf eine einzelne Rückruffunktion zu routen, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Mach etwas
});
```

## Weiterleitung

Sie können die Ausführung an die nächste übereinstimmende Route weitergeben, indem Sie `true` aus Ihrer Rückruffunktion zurückgeben.

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Überprüfe eine Bedingung
  if ($name !== "Bob") {
    // Weiter zur nächsten Route
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```

## Routen-Aliasing

Sie können einer Route ein Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (zum Beispiel wie eine Vorlage).

```php
Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'user_view');

// später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders hilfreich, wenn sich Ihre URL ändern sollte. In dem obigen Beispiel wurde beispielsweise der Benutzer auf `/admin/benutzer/@id` verschoben.
Dank des Aliasings müssen Sie nirgends, wo Sie den Alias referenzieren, etwas ändern, da der Alias nun `/admin/benutzer/5` wie im oben genannten Beispiel zurückgibt.

Das Routen-Aliasing funktioniert auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'benutzer:'.$id; }, false, 'user_view');
});


// später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routeninformation

Wenn Sie Informationen zur übereinstimmenden Route inspizieren möchten, können Sie anfordern, dass das Routenobjekt Ihrer Rückruffunktion übergeben wird, indem Sie `true` als dritten Parameter in
die Routenmethode übergeben. Das Routenobjekt wird immer als letzter Parameter an Ihre Rückruffunktion übergeben.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der mit Methoden abgeglichenen HTTP-Methoden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält die Inhalte von '*' die im URL-Muster verwendet werden
  $route->splat;

  // Zeigt den URL-Pfad an....falls wirklich benötigt
  $route->pattern;

  // Zeigt an, welches Middleware dieser Route zugewiesen ist
  $route->middleware;

  // Zeigt das dieser Route zugeordnete Alias an
  $route->alias;
}, true);
```

## Routen-Gruppierung

Es kann Situationen geben, in denen Sie verwandte Routen gruppieren möchten (z. B. `/api/v1`). Sie können dies durch Verwendung der `group` Methode tun:

```php
Flight::group('/api/v1', function () {
  Flight::route('/benutzer', function () {
	// Übereinstimmung mit /api/v1/benutzer
  });

  Flight::route('/beiträge', function () {
	// Übereinstimmung mit /api/v1/beiträge
  });
});
```

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() macht Variablen, es setzt keine Route! Siehe Objektkontext weiter unten
	Flight::route('GET /benutzer', function () {
	  // Übereinstimmung mit GET /api/v1/benutzer
	});

	Flight::post('/beiträge', function () {
	  // Übereinstimmung mit POST /api/v1/beiträge
	});

	Flight::put('/beiträge/1', function () {
	  // Übereinstimmung mit PUT /api/v1/beiträge
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() macht Variablen, es setzt keine Route! Siehe Objektkontext weiter unten
	Flight::route('GET /benutzer', function () {
	  // Übereinstimmung mit GET /api/v2/benutzer
	});
  });
});
```

### Gruppierung mit Objektkontext

Sie können die Routengruppierung weiterhin mit dem `Engine`-Objekt wie folgt verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // nutzen Sie die $router Variable
  $router->get('/benutzer', function () {
	// Übereinstimmung mit GET /api/v1/benutzer
  });

  $router->post('/beiträge', function () {
	// Übereinstimmung mit POST /api/v1/beiträge
  });
});
```

## Streaming

Sie können nun Antworten an den Client streamen, indem Sie die `streamWithHeaders()` Methode verwenden. 
Dies ist nützlich zum Senden großer Dateien, lang laufender Prozesse oder generieren großer Antworten. 
Das Streamen einer Route wird etwas anders als eine normale Route behandelt.

> **Hinweis:** Das Streamen von Antworten ist nur verfügbar, wenn [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf false gesetzt ist.

```php
Flight::route('/stream-benutzer', function() {

	// wie auch immer Sie Ihre Daten abrufen, nur als Beispiel...
	$users_stmt = Flight::db()->query("SELECT id, vorname, nachname FROM benutzer");

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
	// optionaler Statuscode, Standardwert ist 200
	'status' => 200
]);
```  