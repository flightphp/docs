# Antworten

Flight hilft Ihnen dabei, einen Teil der Antwortheader zu generieren, aber Sie haben die meiste Kontrolle darüber, was Sie an den Benutzer zurücksenden. Manchmal können Sie direkt auf das `Response`-Objekt zugreifen, aber meistens verwenden Sie die `Flight`-Instanz, um eine Antwort zu senden.

## Senden einer einfachen Antwort

Flight verwendet ob_start(), um die Ausgabe zu puffern. Das bedeutet, Sie können `echo` oder `print` verwenden, um eine Antwort an den Benutzer zu senden und Flight wird dies erfassen und mit den entsprechenden Headern zurückschicken.

```php

// Dies sendet "Hallo, Welt!" an den Browser des Benutzers
Flight::route('/', function() {
	echo "Hallo, Welt!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hallo, Welt!
```

Alternativ können Sie auch die `write()` Methode aufrufen, um etwas zum Body hinzuzufügen.

```php

// Dies sendet "Hallo, Welt!" an den Browser des Benutzers
Flight::route('/', function() {
	// ausführlich, aber manchmal sinnvoll, wenn Sie es brauchen
	Flight::response()->write("Hallo, Welt!");

	// Wenn Sie den Body, den Sie zu diesem Zeitpunkt festgelegt haben, abrufen möchten
	// können Sie das so tun
	$body = Flight::response()->getBody();
});
```

## Statuscodes

Sie können den Statuscode der Antwort mit der `status` Methode festlegen:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hallo, Welt!";
	} else {
		Flight::response()->status(403);
		echo "Verboten";
	}
});
```

Wenn Sie den aktuellen Statuscode abrufen möchten, können Sie die `status` Methode ohne Argumente verwenden:

```php
Flight::response()->status(); // 200
```

## Ausführen eines Callbacks auf dem Antwort-Body

Sie können einen Callback auf dem Antwort-Body ausführen, indem Sie die `addResponseBodyCallback` Methode verwenden:

```php
Flight::route('/benutzer', function() {
	$db = Flight::db();
	$benutzer = $db->fetchAll("SELECT * FROM benutzer");
	Flight::render('benutzer_tabelle', ['benutzer' => $benutzer]);
});

// Dies wird alle Antworten für jede Route komprimieren
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Sie können mehrere Callbacks hinzufügen, die in der Reihenfolge ausgeführt werden, in der sie hinzugefügt wurden. Da dies jeden [callable](https://www.php.net/manual/en/language.types.callable.php) akzeptieren kann, kann es ein Klassenarray `[ $klasse, 'methode' ]`, ein Closure `$strErsetzen = function($body) { str_replace('hallo', 'da', $body); };` oder einen Funktionsnamen `'minify'` akzeptieren, wenn Sie beispielsweise eine Funktion zum Verkleinern Ihres HTML-Codes haben.

**Hinweis:** Routen-Callbacks funktionieren nicht, wenn Sie die `flight.v2.output_buffering` Konfigurationsoption verwenden.

### Spezifisches Routen-Callback

Wenn Sie möchten, dass dies nur für eine bestimmte Route gilt, können Sie den Callback auch in die Route selbst einfügen:

```php
Flight::route('/benutzer', function() {
	$db = Flight::db();
	$benutzer = $db->fetchAll("SELECT * FROM benutzer");
	Flight::render('benutzer_tabelle', ['benutzer' => $benutzer]);

	// Dies komprimiert nur die Antwort für diese Route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Middleware-Option

Sie können auch Middleware verwenden, um den Callback über Middleware auf alle Routen anzuwenden:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($body) {
			// Dies ist ein 
			return $this->verkleinern($body);
		});
	}

	protected function verkleinern(string $body): string {
		// Body verkleinern
		return $body;
	}
}

// index.php
Flight::group('/benutzer', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Festlegen eines Antwort-Headers

Sie können einen Header wie den Inhaltstyp der Antwort durch Verwendung der `header` Methode festlegen:

```php

// Dies sendet "Hallo, Welt!" im Klartext an den Browser des Benutzers
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hallo, Welt!";
});
```

## JSON

Flight bietet Unterstützung zum Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, geben Sie einige Daten an, die in JSON kodiert werden sollen:

```php
Flight::json(['id' => 123]);
```

### JSON mit Statuscode

Sie können auch als zweites Argument einen Statuscode angeben:

```php
Flight::json(['id' => 123], 201);
```

### JSON mit Pretty Print

Sie können auch ein Argument an letzter Stelle übergeben, um das Pretty-Printing zu aktivieren:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Wenn Sie die in `Flight::json()` übergebenen Optionen ändern und eine einfachere Syntax wünschen, können Sie die JSON-Methode einfach neu zuordnen:

```php
Flight::map('json', function($daten, $code = 200, $optionen = 0) {
	Flight::_json($daten, $code, true, 'utf-8', $optionen);
}

// Und jetzt kann es so verwendet werden
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON und Ausführung stoppen

Wenn Sie eine JSON-Antwort senden und die Ausführung stoppen möchten, können Sie die `jsonHalt` Methode verwenden.
Dies ist nützlich für Fälle, in denen Sie möglicherweise eine Art Autorisierung überprüfen und wenn
der Benutzer nicht autorisiert ist, können Sie sofort eine JSON-Antwort senden, den vorhandenen Body
leeren und die Ausführung stoppen.

```php
Flight::route('/benutzer', function() {
	$autorisiert = einigeAutorisierungsüberprüfung();
	// Überprüfen, ob der Benutzer autorisiert ist
	if($autorisiert === false) {
		Flight::jsonHalt(['fehler' => 'Unberechtigt'], 401);
	}

	// Mit dem Rest der Route fortfahren
});
```

### JSONP

Für JSONP-Anfragen können Sie optional den Query-Parameter-Namen übergeben, den Sie
verwenden, um Ihre Callback-Funktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Daher sollten Sie beim Senden einer GET-Anfrage mit `?q=my_func` die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Query-Parameter-Namen angeben, wird standardmäßig `jsonp` verwendet.

## Weiterleitung zu einer anderen URL

Sie können die aktuelle Anfrage mit der `redirect()` Methode umleiten, indem Sie
eine neue URL angeben:

```php
Flight::redirect('/neuer/ort');
```

Standardmäßig sendet Flight einen HTTP 303 ("See Other")-Statuscode. Sie können optional auch einen
benutzerdefinierten Code festlegen:

```php
Flight::redirect('/neuer/ort', 401);
```

## Stoppen

Sie können das Framework an jeder Stelle anhalten, indem Sie die `halt` Methode aufrufen:

```php
Flight::halt();
```

Sie können auch optional einen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Bin gleich wieder da...');
```

Durch den Aufruf von `halt` werden alle bisherigen Response-Inhalte verworfen. Wenn Sie das Framework anhalten und den aktuellen Response ausgeben möchten, verwenden Sie die `stop` Methode:

```php
Flight::stop();
```

## HTTP-Caching

Flight bietet integrierte Unterstützung für das Caching auf HTTP-Ebene. Wenn die Caching-Bedingung
erfüllt ist, wird Flight eine HTTP `304 Not Modified`-Antwort zurückgeben. Das nächste Mal, wenn der
Client die Ressource erneut anfordert, wird er aufgefordert, seine lokal zwischengespeicherte Version zu
verwenden.

### Caching auf Routenebene

Wenn Sie Ihre gesamte Antwort zwischenspeichern möchten, können Sie die `cache()` Methode verwenden und die Zeit zum Zwischenspeichern angeben.

```php

// Dadurch wird die Antwort für 5 Minuten zwischengespeichert
Flight::route('/neuigkeiten', function () {
  Flight::response()->cache(time() + 300);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});

// Alternativ können Sie einen String verwenden, den Sie an die strtotime() Methode übergeben würden
Flight::route('/neuigkeiten', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### Geändert am

Sie können die `lastModified` Methode verwenden und einen UNIX-Zeitstempel übergeben, um das Datum
und die Uhrzeit festzulegen, wann eine Seite zuletzt geändert wurde. Der Client wird weiterhin seinen
Cache verwenden, bis der zuletzt geänderte Wert geändert wird.

```php
Flight::route('/neuigkeiten', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### ETag

`ETag`-Caching ist ähnlich wie `Last-Modified`, außer dass Sie eine beliebige ID
für die Ressource angeben können:

```php
Flight::route('/neuigkeiten', function () {
  Flight::etag('meine-eindeutige-id');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Beachten Sie, dass der Aufruf von `lastModified` oder `etag` sowohl den Cache-Wert setzt als auch überprüft. Wenn der Cache-Wert zwischen den Anfragen gleich ist, sendet Flight sofort eine `HTTP 304`-Antwort und stoppt die Verarbeitung.