## Routen

> **Anmerkung:** Möchten Sie mehr über Routenverständnis erfahren? Werfen Sie einen Blick auf die ["warum ein Framework?"](/learn/why-frameworks) Seite für eine eingehendere Erklärung.

Die grundlegende Routenführung in Flight erfolgt durch das Abgleichen eines URL-Musters mit einer Rückruffunktion oder einem Array einer Klasse und Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

> Die Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste übereinstimmende Route für eine Anfrage wird aufgerufen.

### Rückruffunktionen/Funktionen
Der Rückruffunktion kann ein beliebiges Objekt sein, das aufrufbar ist. Sie können also eine reguläre Funktion verwenden:

```php
function hallo() {
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
$begrüßung = new Begrüßung();

Flight::route('/', [ $begrüßung, 'hallo' ]);
// Sie können dies auch ohne Erstellung des Objekts zuerst tun
// Anmerkung: Keine Argumente werden in den Konstruktor injiziert
Flight::route('/', [ 'Begrüßung', 'hallo' ]);
// Außerdem können Sie diese kürzere Syntax verwenden
Flight::route('/', 'Begrüßung->hallo');
// oder
Flight::route('/', Begrüßung::class.'->hallo');
```

#### Abhängigkeitsinjektion über DIC (Dependency Injection Container)
Wenn Sie die Abhängigkeitsinjektion über einen Container (PSR-11, PHP-DI, Dice usw.) verwenden möchten, sind die
einzigen Arten von Routen, bei denen dies verfügbar ist, entweder das direkte Erstellen des Objekts selbst
und die Verwendung des Containers zum Erstellen Ihres Objekts, oder Sie können Zeichenfolgen verwenden, um die Klasse festzulegen und
die aufzurufende Methode. Sie können zur [Abhängigkeitsinjektion](/learn/extending) Seite gehen für 
weitere Informationen. 

Hier ist ein schnelles Beispiel:

```php

use flight\database\PdoWrapper;

// Greeting.php
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

// Richten Sie den Container mit den erforderlichen Parametern ein
// Sehen Sie sich die Abhängigkeitsinjektionsseite für weitere Informationen zu PSR-11 an
$dice = new \Dice\Dice();

// Vergessen Sie nicht, die Variable mit '$dice = ' neu zuzuweisen!!!!
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
Flight::route('/hallo/@id', [ 'Begrüßung', 'hallo' ]);
// oder
Flight::route('/hallo/@id', 'Begrüßung->hallo');
// oder
Flight::route('/hallo/@id', 'Begrüßung::hallo');

Flight::start();
```

## Methodenrouten

Standardmäßig werden Routenmuster mit allen Anfragemethoden abgeglichen. Sie können
auf bestimmte Methoden antworten, indem Sie einen Identifikator vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anfrage erhalten.';
});

// Sie können Flight::get() nicht für Routen verwenden, da dies eine Methode ist, 
//    um Variablen abzurufen, nicht um eine Route zu erstellen.
// Flight::post('/', function() { /* Code */ });
// Flight::patch('/', function() { /* Code */ });
// Flight::put('/', function() { /* Code */ });
// Flight::delete('/', function() { /* Code */ });
```

Sie können auch mehrere Methoden auf eine einzelne Rückruffunktion abbilden, indem Sie ein `|`-Trennzeichen verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anfrage erhalten.';
});
```

Zusätzlich können Sie das Router-Objekt abrufen, das einige Hilfsmethoden für Sie bereithält:

```php

$router = Flight::router();

// alle Methoden mappen
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
  // Dies passt zu /benutzer/1234
});
```

Obwohl diese Methode verfügbar ist, wird empfohlen, benannte Parameter oder 
benannte Parameter mit regulären Ausdrücken zu verwenden, da sie lesbarer und einfacher zu pflegen sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an
Ihre Rückruffunktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einschließen, indem Sie
den `:`-Trennzeichen verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies passt zu /bob/123
  // Passt aber nicht zu /bob/12345
});
```

> **Anmerkung:** Das Anpassen von RegEx-Gruppen `()` mit benannten Parametern wird nicht unterstützt. :'\(

## Optionale Parameter

Sie können benannte Parameter angeben, die optional für die Übereinstimmung sind, indem Sie
Segmente in Klammern einschließen.

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

Alle nicht übereinstimmenden optionalen Parameter werden als `NULL` übergeben.

## Platzhalter

Die Übereinstimmung erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere übereinstimmen möchten
Segmente können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies passt zu /blog/2000/02/01
});
```

Um alle Anfragen an eine einzige Rückruffunktion zu routen, können Sie Folgendes tun:

```php
Flight::route('*', function () {
  // Etwas machen
});
```

## Weitergabe

Sie können die Ausführung an die nächste übereinstimmende Route weitergeben, indem Sie `true` aus
Ihrer Rückruffunktion zurückgeben.

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Bedingung überprüfen
  if ($name !== "Bob") {
    // Zur nächsten Route fortfahren
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```

## Routenalias

Sie können einem Pfad einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (wie in einer Vorlage beispielsweise).

```php
Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_ansicht');

// später im Code an einer Stelle
Flight::getUrl('benutzer_ansicht', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders hilfreich, wenn sich Ihre URL ändern sollte. Im obigen Beispiel wurde angenommen, dass die Benutzer in `/admin/benutzer/@id` verschoben wurden.
Mit dem Alias müssen Sie nirgendwo, wo Sie den Alias referenzieren, etwas ändern, da der Alias nun `/admin/benutzer/5` zurückgibt, wie im 
obigen Beispiel.

Routenaliasierung funktioniert auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_ansicht');
});


// später im Code an einer Stelle
Flight::getUrl('benutzer_ansicht', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routeninformationen

Wenn Sie die übereinstimmenden Routeninformationen untersuchen möchten, können Sie das Anfordern der Routen
Objekt, das Ihrer Rückruffunktion übergeben wird, indem Sie `true` als dritten Parameter in
die Routenmethode übergeben. Das Routenobjekt wird immer als letzten Parameter an Ihre
Rückruffunktion übergeben.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der übereinstimmenden HTTP-Methoden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält die Inhalte von etwaigen '*' verwendet im URL-Muster
  $route->splat;

  // Zeigt den URL-Pfad an....falls Sie ihn wirklich benötigen
  $route->pattern;

  // Zeigt an, welche Middleware dieser Route zugewiesen ist
  $route->middleware;

  // Zeigt den dem Alias zugewiesenen Alias an
  $route->alias;
}, true);
```

## Routengruppen

Es gibt Zeiten, in denen Sie zusammenhängende Routen gruppieren möchten (wie `/api/v1`).
Sie können dies durch Verwendung der `group` Methode tun:

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
	// Flight::get() holt Variablen, setzt keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Passt zu GET /api/v1/benutzer
	});

	Flight::post('/beiträge', function () {
	  // Passt zu POST /api/v1/beiträge
	});

	Flight::put('/beiträge/1', function () {
	  // Passt zu PUT /api/v1/beiträge/1
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() holt Variablen, setzt keine Route! Siehe Objektkontext unten
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

  // Verwenden Sie die $router Variable
  $router->get('/benutzer', function () {
	// Passt zu GET /api/v1/benutzer
  });

  $router->post('/beiträge', function () {
	// Passt zu POST /api/v1/beiträge
  });
});
```

## Streaming

Sie können jetzt Antworten an den Client streamen, indem Sie die `streamWithHeaders()`-Methode verwenden. 
Dies ist nützlich für den Versand großer Dateien, lang laufende Prozesse oder die Generierung großer Antworten. 
Das Streamen einer Route wird etwas anders gehandhabt als eine normale Route.

> **Anmerkung:** Das Streamen von Antworten ist nur verfügbar, wenn Sie [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf false gesetzt haben.

### Streamen mit manuellen Headern

Sie können ein Antwort an den Client streamen, indem Sie die Methode `stream()` auf einer Route verwenden. Wenn Sie
dies tun, müssen Sie alle Methoden manuell festlegen, bevor Sie etwas an den Client ausgeben.
Dies erfolgt mit der `header()`-PHP-Funktion oder der `Flight::response()->setRealHeader()`-Methode.

```php
Flight::route('/@dateiname', function($dateiname) {

	// offensichtlich würden Sie den Pfad usw. bereinigen.
	$dateinameSicher = basename($dateiname);

	// Wenn Sie hier nach Ausführung der Route zusätzliche Header setzen müssen
	// müssen Sie sie definieren, bevor irgendetwas für den Client ausgegeben wird.
	// Alle müssen ein direkter Aufruf der `header()`-Funktion oder 
	// eines Aufrufs der `Flight::response()->setRealHeader()`-Methode sein
	header('Content-Disposition: attachment; filename="'.$dateinameSicher.'"');
	// oder
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$dateinameSicher.'"');

	$dateiDaten = file_get_contents('/some/path/to/files/'.$dateinameSicher);

	// Fehlerbehandlung und so weiter
	if(empty($dateiDaten)) {
		Flight::halt(404, 'Datei nicht gefunden');
	}

	// Setzen Sie die Inhaltslänge manuell, wenn Sie möchten
	header('Content-Length: '.filesize($dateiname));

	// Streamen Sie die Daten an den Client
	echo $dateiDaten;

// Dies ist die magische Zeile hier
})->stream();
```

### Streamen mit Headern

Sie können auch die `streamWithHeaders()`-Methode verwenden, um die Header festzulegen, bevor Sie mit dem Streamen beginnen.

```php
Flight::route('/stream-benutzer', function() {

	// Sie können hier beliebige zusätzliche Header hinzufügen
	// Sie müssen nur `header()` oder `Flight::response()->setRealHeader()` verwenden

	// wie auch immer Sie Ihre Daten abrufen, nur als Beispiel...
	$benutzer_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$anzahlBenutzer = count($benutzer);
	while($benutzer = $benutzer_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($benutzer);
		if(--$anzahlBenutzer > 0) {
			echo ',';
		}

		// Dies ist erforderlich, um die Daten an den Client zu senden
		ob_flush();
	}
	echo '}';

// So setzen Sie die Header, bevor Sie mit dem Streaming beginnen.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="benutzer.json"',
	// optionaler Statuscode, standardmäßig 200
	'status' => 200
]);
```