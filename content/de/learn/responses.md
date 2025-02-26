# Antworten

Flight hilft Ihnen, einen Teil der Antwort-Header zu generieren, aber Sie haben die meiste Kontrolle darüber, was Sie an den Benutzer zurücksenden. Manchmal können Sie direkt auf das `Response`-Objekt zugreifen, aber die meiste Zeit verwenden Sie die `Flight`-Instanz, um eine Antwort zu senden.

## Senden einer grundlegenden Antwort

Flight verwendet ob_start(), um die Ausgabe zwischenzuspeichern. Das bedeutet, dass Sie `echo` oder `print` verwenden können, um eine Antwort an den Benutzer zu senden, und Flight wird sie erfassen und mit den entsprechenden Headern zurücksenden.

```php

// Dies wird "Hello, World!" an den Browser des Benutzers senden
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

Alternativ können Sie die Methode `write()` aufrufen, um den Body ebenfalls hinzuzufügen.

```php

// Dies wird "Hello, World!" an den Browser des Benutzers senden
Flight::route('/', function() {
	// ausführlich, aber manchmal nützlich, wenn Sie es benötigen
	Flight::response()->write("Hello, World!");

	// wenn Sie den Body abrufen möchten, den Sie zu diesem Zeitpunkt festgelegt haben
	// können Sie das so machen
	$body = Flight::response()->getBody();
});
```

## Statuscodes

Sie können den Statuscode der Antwort festlegen, indem Sie die Methode `status` verwenden:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Verboten";
	}
});
```

Wenn Sie den aktuellen Statuscode abrufen möchten, können Sie die Methode `status` ohne Argumente verwenden:

```php
Flight::response()->status(); // 200
```

## Festlegen eines Antwortkörpers

Sie können den Antwortkörper festlegen, indem Sie die Methode `write` verwenden. Wenn Sie jedoch etwas echo oder drucken, 
wird es erfasst und als Antwortkörper über die Ausgabe-Pufferung gesendet.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// dasselbe wie

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Bereinigen eines Antwortkörpers

Wenn Sie den Antwortkörper löschen möchten, können Sie die Methode `clearBody` verwenden:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Ausführen eines Callback für den Antwortkörper

Sie können einen Callback für den Antwortkörper mit der Methode `addResponseBodyCallback` ausführen:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Dies wird alle Antworten für jede Route gzip-komprimieren
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Sie können mehrere Callbacks hinzufügen, und sie werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden. Da dies jedes [Callable](https://www.php.net/manual/en/language.types.callable.php) akzeptieren kann, kann es ein Klassenarray `[ $class, 'method' ]`, einen Closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };` oder einen Funktionsnamen `'minify'` akzeptieren, wenn Sie z. B. eine Funktion haben, um Ihren HTML-Code zu minimieren.

**Hinweis:** Routen-Callbacks funktionieren nicht, wenn Sie die Konfigurationsoption `flight.v2.output_buffering` verwenden.

### Spezifischer Routen-Callback

Wenn Sie möchten, dass dies nur für eine bestimmte Route gilt, können Sie den Callback direkt in der Route hinzufügen:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Dies wird nur die Antwort für diese Route gzip-komprimieren
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Middleware-Option

Sie können auch Middleware verwenden, um den Callback auf alle Routen über Middleware anzuwenden:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Wenden Sie den Callback hier auf das response()-Objekt an.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// den Body irgendwie minimieren
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Festlegen eines Antwort-Headers

Sie können einen Header, wie den Inhaltstyp der Antwort, festlegen, indem Sie die Methode `header` verwenden:

```php

// Dies wird "Hello, World!" als Klartext an den Browser des Benutzers senden
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// oder
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight unterstützt das Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, übergeben Sie einige Daten, die JSON-codiert werden sollen:

```php
Flight::json(['id' => 123]);
```

> **Hinweis:** Standardmäßig sendet Flight einen `Content-Type: application/json`-Header mit der Antwort. Es werden auch die Konstanten `JSON_THROW_ON_ERROR` und `JSON_UNESCAPED_SLASHES` beim Kodieren des JSON verwendet.

### JSON mit Statuscode

Sie können auch einen Statuscode als zweites Argument übergeben:

```php
Flight::json(['id' => 123], 201);
```

### JSON mit schöner Formatierung

Sie können auch ein Argument in die letzte Position übergeben, um die hübsche Formatierung zu aktivieren:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Wenn Sie die in `Flight::json()` übergebenen Optionen ändern und eine einfachere Syntax wünschen, können Sie die JSON-Methode einfach umbenennen:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Und jetzt kann es so verwendet werden
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON und Ausführung stoppen (v3.10.0)

Wenn Sie eine JSON-Antwort senden und die Ausführung anhalten möchten, können Sie die Methode `jsonHalt` verwenden.
Dies ist nützlich für Fälle, in denen Sie vielleicht eine Art von Autorisierung überprüfen und wenn
der Benutzer nicht autorisiert ist, können Sie sofort eine JSON-Antwort senden, den vorhandenen Body-Inhalt löschen und die Ausführung stoppen.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Überprüfen, ob der Benutzer autorisiert ist
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Nicht autorisiert'], 401);
	}

	// Fahren Sie mit dem Rest der Route fort
});
```

Vor v3.10.0 müssten Sie etwas wie dies tun:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Überprüfen, ob der Benutzer autorisiert ist
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Nicht autorisiert']));
	}

	// Fahren Sie mit dem Rest der Route fort
});
```

### JSONP

Für JSONP-Anfragen können Sie optional den Namen des Abfrageparameters übergeben, den Sie verwenden, um Ihre Callback-Funktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Wenn Sie also eine GET-Anfrage mit `?q=my_func` stellen, sollten Sie die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameternamen übergeben, wird standardmäßig `jsonp` verwendet.

## Umleitung zu einer anderen URL

Sie können die aktuelle Anfrage umleiten, indem Sie die Methode `redirect()` verwenden und eine neue URL übergeben:

```php
Flight::redirect('/new/location');
```

Standardmäßig sendet Flight einen HTTP 303 ("Siehe Andere") Statuscode. Sie können optional einen benutzerdefinierten Code festlegen:

```php
Flight::redirect('/new/location', 401);
```

## Stoppen

Sie können das Framework an jedem Punkt anhalten, indem Sie die Methode `halt` aufrufen:

```php
Flight::halt();
```

Sie können auch einen optionalen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Gleich zurück...');
```

Der Aufruf von `halt` führt dazu, dass alle bis zu diesem Punkt gesendeten Antwortinhalte verworfen werden. Wenn Sie das Framework anhalten und die aktuelle Antwort ausgeben möchten, verwenden Sie die Methode `stop`:

```php
Flight::stop();
```

## Löschen von Antwortdaten

Sie können den Antwortkörper und die Header löschen, indem Sie die Methode `clear()` verwenden. Dadurch werden alle Header, die der Antwort zugeordnet sind, gelöscht, der Antwortkörper gelöscht und der Statuscode auf `200` gesetzt.

```php
Flight::response()->clear();
```

### Nur den Antwortkörper löschen

Wenn Sie nur den Antwortkörper löschen möchten, können Sie die Methode `clearBody()` verwenden:

```php
// Dadurch bleiben alle auf das response()-Objekt gesetzten Header erhalten.
Flight::response()->clearBody();
```

## HTTP-Caching

Flight bietet integrierte Unterstützung für Caching auf HTTP-Ebene. Wenn die Cache-Bedingung erfüllt ist, gibt Flight eine HTTP `304 Not Modified`-Antwort zurück. Das nächste Mal, wenn der Client dieselbe Ressource anfordert, wird er aufgefordert, seine lokal zwischengespeicherte Version zu verwenden.

### Caching auf Routenebene

Wenn Sie die gesamte Antwort zwischenspeichern möchten, können Sie die Methode `cache()` verwenden und die Zeit zum Zwischenspeichern übergeben.

```php

// Dies wird die Antwort für 5 Minuten zwischenspeichern
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});

// Alternativ können Sie einen String verwenden, den Sie an die strtotime()-Methode übergeben
Flight::route('/news', function () {
  Flight::response()->cache('+5 Minuten');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### Last-Modified

Sie können die Methode `lastModified` verwenden und einen UNIX-Zeitstempel übergeben, um das Datum und die Uhrzeit festzulegen, zu der die Seite zuletzt geändert wurde. Der Client wird sein Cache weiter nutzen, bis der Wert der letzten Änderung sich ändert.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### ETag

`ETag`-Caching ist ähnlich wie `Last-Modified`, außer dass Sie jede gewünschte ID für die Ressource angeben können:

```php
Flight::route('/news', function () {
  Flight::etag('meine-einzigartige-id');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Beachten Sie, dass das Aufrufen von `lastModified` oder `etag` den Cachewert sowohl festlegt als auch überprüft. Wenn der Cachewert zwischen den Anfragen gleich bleibt, sendet Flight sofort eine `HTTP 304`-Antwort und stoppt die Verarbeitung.

## Eine Datei herunterladen (v3.12.0)

Es gibt eine Hilfsmethode zum Herunterladen einer Datei. Sie können die Methode `download` verwenden und den Pfad übergeben.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```