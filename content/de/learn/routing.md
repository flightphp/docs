# Routen

> **Hinweis:** Möchten Sie mehr über Routen erfahren? Schauen Sie sich die ["warum ein Framework?"](/learn/why-frameworks) Seite für eine ausführlichere Erklärung an.

Die grundlegende Routenführung in Flight erfolgt durch das Zuordnen eines URL-Musters mit einer Rückruffunktion oder einem Array einer Klasse und Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

Der Rückruf kann jedes Objekt sein, das aufrufbar ist. Sie können also eine normale Funktion verwenden:

```php
function hello(){
    echo 'Hallo Welt!';
}

Flight::route('/', 'hello');
```

Oder eine Klassenmethode:

```php
class Begrüßung {
    public static function hello() {
        echo 'Hallo Welt!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Oder eine Objektmethode:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'Max Mustermann';
    }

    public function hello() {
        echo "Hallo, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Die Routen werden in der Reihenfolge übereinstimmend definiert. Die erste übereinstimmende Route für eine Anforderung wird ausgeführt.

## Methoden-Routing

Standardmäßig werden Routenmuster gegen alle Anforderungsmethoden abgeglichen. Sie können auf bestimmte Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anforderung erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anforderung erhalten.';
});
```

Sie können auch mehrere Methoden auf einen einzelnen Rückruf abbilden, indem Sie einen `|`-Trenner verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anforderung erhalten.';
});
```

Außerdem können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie bereithält:

```php

$router = Flight::router();

// abbildet alle Methoden
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
  // Dies entspricht /benutzer/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu pflegen sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Rückruffunktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einschließen, indem Sie den `:`-Trenner verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies entspricht /bob/123
  // Aber nicht /bob/12345
});
```

> **Hinweis:** Das Übereinstimmen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt. :'\(

## Optionale Parameter

Sie können benannte Parameter angeben, die optional übereinstimmen, indem Sie Segmente in Klammern einschließen.

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

Nicht übereinstimmende optionale Parameter werden als `NULL` übergeben.

## Platzhalter

Die Übereinstimmung erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere Segmente matchen möchten, können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies entspricht /blog/2000/02/01
});
```

Um alle Anforderungen an einen einzelnen Rückruf zu leiten, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Etwas tun
});
```

## Weiterleitung

Sie können die Ausführung an die nächste übereinstimmende Route weiterleiten, indem Sie `true` aus Ihrer Rückruffunktion zurückgeben.

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Überprüfen Sie eine Bedingung
  if ($name !== "Bob") {
    // Zur nächsten Route weitergehen
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```

## Routenalias

Sie können einem Routen einen Alias zuweisen, sodass die URL später dynamisch in Ihrem Code generiert werden kann (zum Beispiel wie bei einer Vorlage).

```php
Flight::route('/benutzer/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'benutzer_ansehen');

// später im Code an irgendeiner Stelle
Flight::getUrl('benutzer_ansehen', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders hilfreich, wenn sich Ihre URL zufällig ändert. Im obigen Beispiel wurde beispielsweise Benutzer zu `/admin/benutzer/@id` verschoben.
Durch das Alias müssen Sie nirgendwo, wo Sie auf das Alias verweisen, etwas ändern, da das Alias nun `/admin/benutzer/5` zurückgibt, wie im obigen Beispiel.

Routenalias funktionieren auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'benutzer_ansehen');
});


// später im Code an irgendeiner Stelle
Flight::getUrl('benutzer_ansehen', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routeninformation

Wenn Sie die übereinstimmenden Routeninformationen überprüfen möchten, können Sie verlangen, dass das Routenobjekt an Ihre Rückruffunktion übergeben wird, indem Sie `true` als dritten Parameter in der Routenmethode angeben. Das Routenobjekt wird immer als letzter Parameter an Ihre Rückruffunktion übergeben.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der abgeglichenen HTTP-Methoden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält die Inhalte von verwendetem '*' im URL-Muster
  $route->splat;

  // Zeigt den URL-Pfad an....wenn Sie ihn wirklich benötigen
  $route->pattern;

  // Zeigt an, welches Middleware dieser Route zugewiesen ist
  $route->middleware;

  // Zeigt den diesem Route zugewiesenen Alias an
  $route->alias;
}, true);
```

## Routen-Gruppierung

Es kann Zeiten geben, in denen Sie verwandte Routen gruppieren möchten (wie `/api/v1`).
Dies können Sie durch Verwendung der `group`-Methode tun:

```php
Flight::group('/api/v1', function () {
  Flight::route('/benutzer', function () {
	// Entsprechend /api/v1/benutzer
  });

  Flight::route('/posts', function () {
	// Entsprechend /api/v1/posts
  });
});
```

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() holt Variablen, setzt keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Entsprechend GET /api/v1/benutzer
	});

	Flight::post('/posts', function () {
	  // Entsprechend POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Entsprechend PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() holt Variablen, setzt keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Entsprechend GET /api/v2/benutzer
	});
  });
});
```

### Gruppierung mit Objektkontext

Sie können weiterhin die Routengruppierung mit dem `Engine`-Objekt auf folgende Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // nutzt die $router-Variable
  $router->get('/benutzer', function () {
	// Entsprechend GET /api/v1/benutzer
  });

  $router->post('/posts', function () {
	// Entsprechend POST /api/v1/posts
  });
});
```

## Streaming

Sie können jetzt Antworten an den Client streamen, indem Sie die Methode `streamWithHeaders()` verwenden. 
Dies ist nützlich für das Senden großer Dateien, langlaufende Prozesse oder das Generieren großer Antworten. 
Das Streamen einer Route wird etwas anders behandelt als eine normale Route.

> **Hinweis:** Das Streamen von Antworten ist nur möglich, wenn [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf falsch gesetzt ist.

```php
Flight::route('/benutzer-streamen', function() {

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
	// optionaler Statuscode, Standardwert ist 200
	'status' => 200
]);
```