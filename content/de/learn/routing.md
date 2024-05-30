```de
# Routing

> **Hinweis:** Möchten Sie mehr über Routing erfahren? Schauen Sie sich die ["warum ein Framework?"](/learn/why-frameworks) Seite für eine ausführlichere Erklärung an.

Die grundlegende Routenführung in Flight erfolgt durch das Zuordnen eines URL-Musters mit einer Callback-Funktion oder einem Array einer Klasse und Methode.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

> Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste passende Route für eine Anfrage wird aufgerufen.

### Callbacks/Funktionen
Das Callback kann jedes Objekt sein, das aufrufbar ist. Sie können also eine reguläre Funktion verwenden:

```php
function hallo(){
    echo 'Hallo Welt!';
}

Flight::route('/', 'hallo');
```

### Klassen
Sie können auch eine statische Methode einer Klasse verwenden:

```php
class Gruß {
    public static function hallo() {
        echo 'Hallo Welt!';
    }
}

Flight::route('/', [ 'Gruß','hallo' ]);
```

Oder indem Sie zuerst ein Objekt erstellen und dann die Methode aufrufen:

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

Flight::route('/', [ $gruß, 'hallo' ]);
// Sie können dies auch ohne zuerst das Objekt zu erstellen
// beachten: Keine Argumente werden in den Konstruktor eingefügt
Flight::route('/', [ 'Gruß', 'hallo' ]);
```

#### Abhängigkeitsinjektion über DIC (Dependency Injection Container)
Wenn Sie die Abhängigkeitsinjektion über einen Container verwenden möchten (PSR-11, PHP-DI, Dice usw.), ist der
einzige Typ von Routen, bei dem dies möglich ist, entweder das direkte Erstellen des Objekts selbst
und die Verwendung des Containers zum Erstellen Ihres Objekts oder Sie können Zeichenfolgen verwenden, um die Klasse zu definieren und
Methode zum Aufruf. Sie können zur [Dependency Injection](/learn/extending)-Seite gehen, um
mehr Informationen zu erhalten.

Hier ist ein schnelles Beispiel:

```php

use flight\database\PdoWrapper;

// Gruß.php
class Gruß
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
// Sehen Sie sich die Dependency Injection-Seite für weitere Informationen zu PSR-11 an
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

// Melden Sie den Container-Handler an
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routen wie gewohnt
Flight::route('/hallo/@id', [ 'Gruß', 'hallo' ]);
// oder
Flight::route('/hallo/@id', 'Gruß->hallo');
// oder
Flight::route('/hallo/@id', 'Gruß::hallo');

Flight::start();
```

## Methodenrouten

Standardmäßig werden Routenmuster gegen alle Anfragemethoden abgeglichen. Sie können
auf spezifische Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anfrage erhalten.';
});

// Sie können Flight::get() nicht für Routen verwenden, da dies eine Methode ist
//    zum Abrufen von Variablen, keine Route erstellen.
// Flight::post('/', function() { /* Code */ });
// Flight::patch('/', function() { /* Code */ });
// Flight::put('/', function() { /* Code */ });
// Flight::delete('/', function() { /* Code */ });
```

Sie können auch mehrere Methoden zu einer einzelnen Callbackfunktion zuordnen, indem Sie einen `|`-Delimiter verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder POST-Anfrage erhalten.';
});
```

Außerdem können Sie auf das Router-Objekt zugreifen, das einige Hilfsmethoden für Sie bereitstellt:

```php

$router = Flight::router();

// alle Methoden zuordnen
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
benannte Parameter mit regulären Ausdrücken zu verwenden, da diese besser lesbar und einfacher zu pflegen sind.

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an
Ihre Callback-Funktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einschließen, indem
Sie den `:`-Delimiter verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies passt zu /bob/123
  // Passt jedoch nicht zu /bob/12345
});
```

> **Hinweis:** Das Übereinstimmen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt. :'\(

## Optionale Parameter

Sie können benannte Parameter angeben, die optional übereinstimmen, indem Sie
Segmente in Klammern setzen.

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

Nicht übereinstimmende optionale Parameter werden als `NULL` übergeben.

## Platzhalter

Die Übereinstimmung erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere übereinstimmen möchten
Segmente können Sie den `*`-Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies passt zu /blog/2000/02/01
});
```

Um alle Anfragen an eine einzelne Callbackfunktion zu routen, können Sie folgendes tun:

```php
Flight::route('*', function () {
  // Mach etwas
});
```

## Weitergabe

Sie können die Ausführung an die nächste passende Route weitergeben, indem Sie `true` aus
Ihrer Callback-Funktion zurückgeben.

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Überprüfen Sie eine Bedingung
  if ($name !== "Bob") {
    // Zur nächsten Route fortsetzen
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```

## Routenalias

Sie können einem Route einen Alias zuweisen, damit die URL später dynamisch in Ihrem Code generiert werden kann (beispielsweise in einem Template).

```php
Flight::route('/benutzer/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders nützlich, wenn sich Ihre URL zufällig ändert. Im obigen Beispiel wurde Benutzer beispielsweise nach `/admin/benutzer/@id` verschoben.
Dank der Aliasfunktion müssen Sie überall dort, wo Sie auf den Alias verweisen, keine Änderungen vornehmen, da der Alias nun `/admin/benutzer/5` wie im Beispiel oben zurückgibt.

Routenalias funktionieren auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// später im Code irgendwo
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routeninformationen

Wenn Sie die Übereinstimmungsinformationen der Route inspizieren möchten, können Sie anfordern, dass das
Routenobjekt an Ihre Callback-Funktion übergeben wird, indem Sie `true` als dritten Parameter in
die Routenmethode übergeben. Das Routenobjekt wird immer als letzter Parameter an Ihre
Callback-Funktion übergeben.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der mit übereinstimmenden HTTP-Methoden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von '*' im URL-Muster
  $route->splat;

  // Zeigt den URL-Pfad an.... falls wirklich benötigt
  $route->pattern;

  // Zeigt an, welches Middleware für diese Route zugewiesen ist
  $route->middleware;

  // Zeigt den diesem Route zugewiesenen Alias an
  $route->alias;
}, true);
```

## Routengruppierung

Es gibt möglicherweise Zeiten, in denen Sie zusammenhängende Routen gruppieren möchten (z. B. `/api/v1`).
Dies können Sie durch Verwendung der `Gruppen`-Methode erreichen:

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
	  // Passt zu PUT /api/v1/beiträge
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

  // Verwenden Sie die Variable $router
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
Dies ist nützlich für das Senden großer Dateien, lang laufende Prozesse oder die Generierung großer Antworten. 
Das Streamen einer Route wird etwas anders behandelt als eine normale Route.

> **Hinweis:** Das Streamen von Antworten ist nur verfügbar, wenn Sie [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) auf false gesetzt haben.

### Stream mit manuellen Headern

Sie können eine Antwort an den Client streamen, indem Sie die `stream()`-Methode auf einer Route verwenden. Wenn Sie das tun, müssen Sie alle Methoden von Hand festlegen, bevor Sie etwas an den Client ausgeben.
Dies geschieht mit der `header()`-PHP-Funktion oder der `Flight::response()->setRealHeader()`-Methode.

```php
Flight::route('/@dateiname', function($dateiname) {

	// offensichtlich sollten Sie den Pfad und dergleichen bereinigen.
	$dateinameSicher = basename($dateiname);

	// Wenn Sie hier nach der Route zusätzliche Header setzen müssen
	// müssen Sie diese definieren, bevor etwas an den Client ausgegeben wird.
	// Sie müssen alle ein Aufruf der header() Funktion oder ein Aufruf von Flight::response()->setRealHeader()
	header('Content-Disposition: Anhang; filename="'.$dateinameSicher.'"');
	// oder
	Flight::response()->setRealHeader('Content-Disposition', 'Anhang; filename="'.$dateinameSicher.'"');

	$dateiDaten = file_get_contents('/some/path/to/files/'.$dateinameSicher);

	// Fehlerbehandlung usw.
	if(empty($fileData)) {
		Flight::halt(404, 'Datei nicht gefunden');
	}

	// manuell die Inhaltslänge setzen, wenn Sie möchten
	header('Inhaltslänge: '.filesize($filename));

	// Streamen der Daten an den Client
	echo $dateiDaten;

// Dies ist die magische Zeile hier
})->stream();
```

### Stream mit Headern

Sie können auch die `streamWithHeaders()`-Methode verwenden, um die Header festzulegen, bevor Sie mit dem Streamen beginnen.

```php
Flight::route('/benutzer-stream', function() {

	// Sie können hier beliebige zusätzliche Header hinzufügen
	// Sie müssen nur header() oder Flight::response()->setRealHeader() verwenden

	// wie auch immer Sie Ihre Daten abrufen, nur als Beispiel...
	$benutzer_stmt = Flight::db()->query("SELECT id, vorname, nachname FROM benutzer");

	echo '{';
	$benutzerZähler = count($users);
	while($benutzer = $benutzer_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($benutzer);
		if(--$benutzerZähler > 0) {
			echo ',';
		}

		// Hiermit senden Sie die Daten an den Client
		ob_flush();
	}
	echo '}';

// So setzen Sie die Header, bevor Sie mit dem Streamen beginnen.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'Anhang; filename="benutzer.json"',
	// optionaler Statuscode, standardmäßig 200
	'status' => 200
]);
```