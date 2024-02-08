# Routen

> **Hinweis:** Möchten Sie mehr über Routing erfahren? Schauen Sie sich die [Warum Frameworks](/learn/why-frameworks) Seite für eine ausführlichere Erklärung an.

Das grundlegende Routing in Flight erfolgt durch das Zuordnen eines URL-Musters mit einer Rückruffunktion oder einem Array einer Klasse und Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

Der Rückruf kann ein beliebiges Objekt sein, das aufrufbar ist. Sie können also eine reguläre Funktion verwenden:

```php
function hallo(){
    echo 'Hallo Welt!';
}

Flight::route('/', 'hallo');
```

Oder eine Klassenmethode:

```php
class Gruß {
    public static function hallo() {
        echo 'Hallo Welt!';
    }
}

Flight::route('/', array('Gruß','hallo'));
```

Oder eine Objektmethode:

```php

// Gruß.php
class Gruß
{
    public function __construct() {
        $this->name = 'Max Mustermann';
    }

    public function hallo() {
        echo "Hallo, {$this->name}!";
    }
}

// index.php
$gruß = new Gruß();

Flight::route('/', array($gruß, 'hallo'));
```

Die Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste passende Route einer Anforderung wird aufgerufen.

## Methoden-Routing

Standardmäßig werden Routenmuster gegen alle Anforderungsmethoden abgeglichen. Sie können auf spezifische Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anforderung erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anforderung erhalten.';
});
```

Sie können auch mehrere Methoden einem einzelnen Rückruf zuordnen, indem Sie einen `|`-Trennzeichen verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anforderung erhalten.';
});
```

Zusätzlich können Sie das Routenobjekt erhalten, das einige Hilfsmethoden für Sie bereitstellt:

```php

$router = Flight::router();

// ordnet alle Methoden zu
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
  // Dies wird mit /benutzer/1234 übereinstimmen
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder 
benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu pflegen sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Rückruffunktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einschließen, indem Sie den `:`-Trennzeichen verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies wird mit /bob/123 übereinstimmen
  // Aber nicht mit /bob/12345
});
```

> **Hinweis:** Das Zuordnen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt. :'\(

## Optionale Parameter

Sie können benannte Parameter spezifizieren, die optional für den Abgleich sind, indem Sie Segmente in Klammern einschließen.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Dies wird mit den folgenden URLs übereinstimmen:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Alle optionalen Parameter, die nicht abgeglichen werden, werden als `NULL` übergeben.

## Platzhalter

Der Abgleich erfolgt nur an einzelnen URL-Segmenten. Wenn Sie mehrere
Segmente abgleichen möchten, können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies wird mit /blog/2000/02/01 übereinstimmen
});
```

Um alle Anforderungen an einen einzelnen Rückruf zu routen, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Mach etwas
});
```

## Weitergabe

Sie können die Ausführung an die nächste passende Route weitergeben, indem Sie `true` aus Ihrer Rückruffunktion zurückgeben.

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Überprüfen einer Bedingung
  if ($name !== "Bob") {
    // Zur nächsten Route weitergehen
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```

## Routen-Alias

Sie können einer Route einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (zum Beispiel wie eine Vorlage).

```php
Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_anzeigen');

// später im Code an anderer Stelle
Flight::getUrl('benutzer_anzeigen', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders hilfreich, wenn sich Ihre URL zufällig ändert. Im obigen Beispiel, wenn Benutzer nach `/admin/benutzer/@id` verschoben wurde.
Dank des Aliasierens müssen Sie nirgendwo, wo Sie den Alias referenzieren, Änderungen vornehmen, da der Alias nun wie im
obigen Beispiel `/admin/benutzer/5` zurückgibt.

Das Aliasieren von Routen funktioniert auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_anzeigen');
});


// später im Code an anderer Stelle
Flight::getUrl('benutzer_anzeigen', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routeninformationen

Wenn Sie die übereinstimmenden Routeninformationen inspizieren möchten, können Sie die Route
Objekt anfordern, das Ihrer Rückruffunktion übergeben wird, indem Sie `true` als drittes Argument in
der Routenmethode übergeben. Das Routenobjekt wird immer als letzter Parameter an Ihre
Rückruffunktion übergeben.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der gegenübergestellten HTTP-Methoden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von '*' in dem URL-Muster
  $route->splat;

  // Zeigt den URL-Pfad an.... wenn Sie ihn wirklich benötigen
  $route->pattern;

  // Zeigt an, welcher Middleware dieser Route zugewiesen ist
  $route->middleware;

  // Zeigt den diesem Route zugewiesenen Alias an
  $route->alias;
}, true);
```

## Routengruppierung

Manchmal möchten Sie verwandte Routen zusammenfassen (z. B. `/api/v1`).
Das können Sie durch Verwendung der `group` Methode tun:

```php
Flight::group('/api/v1', function () {
  Flight::route('/benutzer', function () {
	// Passt zu /api/v1/benutzer
  });

  Flight::route('/beiträge', function () {
	// Passt zu /api/v1/beiträge
  });
});
```

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() holt Variablen, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Passt zu GET /api/v1/benutzer
	});

	Flight::post('/beiträge', function () {
	  // Passt zu POST /api/v1/beiträge
	});

	Flight::put('/beiträge/1', function () {
	  // Passt zu PUT /api/v1/beiträge
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() holt Variablen, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Passt zu GET /api/v2/benutzer
	});
  });
});
```

### Gruppierung mit Objektkontext

Sie können die Routengruppierung immer noch mit dem `Engine`-Objekt auf folgende Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // benutze die Variable $router
  $router->get('/benutzer', function () {
	// Passt zu GET /api/v1/benutzer
  });

  $router->post('/beiträge', function () {
	// Passt zu POST /api/v1/beiträge
  });
});
```