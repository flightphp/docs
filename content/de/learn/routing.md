# Routen

> **Hinweis:** Möchten Sie mehr über Routenverständnis erfahren? Besuchen Sie die Seite [Warum Frameworks](/learn/why-frameworks) für eine ausführlichere Erklärung.

Die grundlegende Routenverwendung in Flight erfolgt durch das Zuordnen eines URL-Musters mit einer Rückruffunktion oder einem Array einer Klasse und Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

Der Rückruf kann ein beliebiges Objekt sein, das aufrufbar ist. Sie können also eine normale Funktion verwenden:

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

Flight::route('/', array('Greeting','hello'));
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

Die Routen werden in der Reihenfolge übereinstimmend definiert. Die erste übereinstimmende Route für eine Anfrage wird aufgerufen.

## Methoden-Routing

Standardmäßig werden Routenmuster gegen alle Anforderungsmethoden abgeglichen. Sie können auf spezifische Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anfrage erhalten.';
});
```

Sie können auch mehrere Methoden auf einen einzigen Rückruf abbilden, indem Sie einen `|`-Trennzeichen verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anfrage erhalten.';
});
```

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie bereithält:

```php

$router = Flight::router();

// ordnet alle Methoden zu
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
  // Das entspricht z. B. /benutzer/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und wartungsfreundlicher sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Rückruffunktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern inkludieren, indem Sie den `:`-Trenner verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies entspricht z. B. /bob/123
  // Aber passt nicht zu /bob/12345
});
```

> **Hinweis:** Das Übereinstimmen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt. :'\(

## Optionale Parameter

Sie können benannte Parameter angeben, die optional zur Übereinstimmung sind, indem Sie Segmente in Klammern einschließen.

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

Alle optionalen Parameter, die nicht übereinstimmen, werden als `NULL` übergeben.

## Platzhalter

Die Zuordnung erfolgt nur für einzelne URL-Segmente. Wenn Sie mehrere Segmente übereinstimmen möchten, können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies entspricht z. B. /blog/2000/02/01
});
```

Um alle Anfragen an einen einzigen Rückruf zu routen, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Mach etwas
});
```

## Weiterleitung

Sie können die Ausführung an die nächste übereinstimmende Route weiterleiten, indem Sie `true` aus Ihrer Rückruffunktion zurückgeben.

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Prüfen einer Bedingung
  if ($name !== "Max") {
    // Weiter zur nächsten Route
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```

## Routenalias

Sie können einer Route einen Alias zuweisen, damit die URL später dynamisch in Ihrem Code generiert werden kann (z. B. wie eine Vorlage).

```php
Flight::route('/benutzer/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'benutzer_ansicht');

// später im Code an einer Stelle
Flight::getUrl('benutzer_ansicht', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders hilfreich, wenn sich Ihre URL zufällig ändert. In obigem Beispiel, nehmen wir an, dass Benutzer zu `/admin/benutzer/@id` verschoben wurden.
Mit dem Aliasierungssystem müssen Sie nirgendwo, wo Sie den Alias referenzieren, Änderungen vornehmen, da der Alias jetzt `/admin/benutzer/5` wie im obigen Beispiel zurückgeben wird.

Routenaliasierung funktioniert auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'benutzer_ansicht');
});


// später im Code an einer Stelle
Flight::getUrl('benutzer_ansicht', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routeninfo

Wenn Sie die übereinstimmenden Routeninformationen inspizieren möchten, können Sie anfordern, dass das Routenobjekt an Ihre Rückruffunktion übergeben wird, indem Sie `true` als dritten Parameter in der Routenmethode angeben. Das Routenobjekt wird immer als letzter Parameter an Ihre Rückruffunktion übergeben.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der mit übereinstimmenden HTTP-Methoden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von eventuellen '*' im URL-Muster
  $route->splat;

  // Zeigt den URL-Pfad... wenn Sie ihn wirklich brauchen
  $route->pattern;

  // Zeigt an, welches Middleware dieser Route zugewiesen ist
  $route->middleware;

  // Zeigt den dieser Route zugewiesenen Alias an
  $route->alias;
}, true);
```

## Routengruppierung

Es kann vorkommen, dass Sie verwandte Routen zusammenfassen möchten (wie `/api/v1`).
Sie können dies durch Verwendung der `group`-Methode tun:

```php
Flight::group('/api/v1', function () {
  Flight::route('/benutzer', function () {
	// Entsprechend für /api/v1/benutzer
  });

  Flight::route('/beiträge', function () {
	// Entsprechend für /api/v1/beiträge
  });
});
```

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() nimmt Variablen entgegen, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Entsprechend für GET /api/v1/benutzer
	});

	Flight::post('/posts', function () {
	  // Entsprechend für POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Entsprechend für PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() nimmt Variablen entgegen, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Entsprechend für GET /api/v2/benutzer
	});
  });
});
```

### Gruppierung mit Objektkontext

Sie können immer noch Routengruppierung mit dem `Engine`-Objekt auf folgende Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // Verwenden der Variable $router
  $router->get('/benutzer', function () {
	// Entsprechend für GET /api/v1/benutzer
  });

  $router->post('/posts', function () {
	// Entsprechend für POST /api/v1/posts
  });
});
```