# Antworten

Flight hilft dabei, einen Teil der Antwortheader für Sie zu generieren, aber Sie haben die meiste Kontrolle darüber, was Sie dem Benutzer zurückschicken. Manchmal können Sie direkt auf das `Response`-Objekt zugreifen, aber die meiste Zeit verwenden Sie die `Flight`-Instanz, um eine Antwort zu senden.

## Senden einer einfachen Antwort

Flight verwendet ob_start(), um die Ausgabe zu puffern. Das bedeutet, dass Sie `echo` oder `print` verwenden können, um dem Benutzer eine Antwort zu senden und Flight wird dies erfassen und mit den entsprechenden Headern an den Benutzer zurücksenden.

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

Alternativ können Sie die `write()`-Methode aufrufen, um den Body ebenfalls hinzuzufügen.

```php

// Dies sendet "Hallo, Welt!" an den Browser des Benutzers
Flight::route('/', function() {
	// ausführlicher, aber erledigt manchmal den Job, wenn Sie es brauchen
	Flight::response()->write("Hallo, Welt!");

	// wenn Sie den Body, den Sie zu diesem Zeitpunkt festgelegt haben, abrufen möchten
	// können Sie dies wie folgt tun
	$body = Flight::response()->getBody();
});
```

## Statuscodes

Sie können den Statuscode der Antwort durch Verwendung der `status`-Methode festlegen:

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

Wenn Sie den aktuellen Statuscode abrufen möchten, können Sie die `status`-Methode ohne Argumente verwenden:

```php
Flight::response()->status(); // 200
```

## Festlegen eines Antwortbodys

Sie können den Antwortbody durch Verwendung der `write`-Methode festlegen, jedoch wird, wenn Sie etwas mit `echo` ausgeben, dies erfasst und als Antwortbody über Output-Pufferung gesendet.

```php
Flight::route('/', function() {
	Flight::response()->write("Hallo, Welt!");
});

// dasselbe wie

Flight::route('/', function() {
	echo "Hallo, Welt!";
});
```

### Löschen eines Antwortbodys

Wenn Sie den Antwortbody löschen möchten, können Sie die `clearBody`-Methode verwenden:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hallo, Welt!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Ausführen eines Callbacks auf dem Antwortbody

Sie können einen Callback auf dem Antwortbody ausführen, indem Sie die `addResponseBodyCallback`-Methode verwenden:

```php
Flight::route('/benutzer', function() {
	$db = Flight::db();
	$users = $db->fetchAll("WÄHLE * AUS benutzer");
	Flight::render('benutzertabelle', ['benutzer' => $benutzer]);
});

// Dies wird alle Antworten für eine Route komprimieren
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Sie können mehrere Callbacks hinzufügen, und sie werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden. Da dies jeden [aufrufbar](https://www.php.net/manual/en/language.types.callable.php) akzeptieren kann, kann es ein Klassenarray `[ $klasse, 'methode' ]`, eine Closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };` oder ein Funktionsname `'minify'` akzeptieren, wenn Sie beispielsweise eine Funktion haben, um Ihren HTML-Code zu verkleinern.

**Hinweis:** Routen-Callbacks funktionieren nicht, wenn Sie die Konfigurationsoption `flight.v2.output_buffering` verwenden.

### Spezifisches Routen-Callback

Wenn Sie möchten, dass dies nur für eine bestimmte Route gilt, können Sie den Callback direkt in der Route hinzufügen:

```php
Flight::route('/benutzer', function() {
	$db = Flight::db();
	$users = $db->fetchAll("WÄHLE * AUS benutzer");
	Flight::render('benutzertabelle', ['benutzer' => $benutzer]);

	// Dies wird nur die Antwort für diese Route komprimieren
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
		// wenden Sie den Callback hier auf das response() Objekt an.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// den Body irgendwie verkleinern
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

Sie können einen Header wie den Inhaltsprototyp der Antwort durch Verwendung der `header`-Methode festlegen:

```php

// Dies sendet "Hallo, Welt!" im Klartext an den Browser des Benutzers
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// oder
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hallo, Welt!";
});
```

## JSON

Flight bietet Unterstützung zum Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, übergeben Sie einige Daten, die JSON-codiert werden sollen:

```php
Flight::json(['id' => 123]);
```

### JSON mit Statuscode

Sie können auch als zweites Argument einen Statuscode übergeben:

```php
Flight::json(['id' => 123], 201);
```

### JSON mit Pretty Print

Sie können auch ein Argument in der letzten Position übergeben, um die Formatierung zu aktivieren:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Wenn Sie Optionen, die an `Flight::json()` übergeben werden, ändern und eine einfachere Syntax wünschen, können Sie die JSON-Methode einfach neu zuordnen:

```php
Flight::map('json', function($daten, $code = 200, $optionen = 0) {
	Flight::_json($daten, $code, true, 'utf-8', $optionen);
}

// Und jetzt kann es so verwendet werden
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON und Ausführung stoppen (v3.10.0)

Wenn Sie eine JSON-Antwort senden und die Ausführung stoppen möchten, können Sie die `jsonHalt`-Methode verwenden.
Dies ist nützlich für Fälle, in denen Sie möglicherweise eine Art Autorisierung überprüfen und wenn
der Benutzer nicht autorisiert ist, können Sie sofort eine JSON-Antwort senden, den aktuellen Body löschen
Inhalt und Anhalten der Ausführung.

```php
Flight::route('/benutzer', function() {
	$authorisiert = someAuthorizationCheck();
	// Überprüfen, ob der Benutzer autorisiert ist
	if($authorisiert === false) {
		Flight::jsonHalt(['Fehler' => 'Nicht autorisiert'], 401);
	}

	// Fortfahren mit dem Rest der Route
});
```

Vor v3.10.0 müssten Sie etwas ähnliches tun:

```php
Flight::route('/benutzer', function() {
	$authorisiert = someAuthorizationCheck();
	// Überprüfen, ob der Benutzer autorisiert ist
	if($authorisiert === false) {
		Flight::halt(401, json_encode(['Fehler' => 'Nicht autorisiert']));
	}

	// Fortfahren mit dem Rest der Route
});
```

### JSONP

Für JSONP-Anfragen können Sie optional den Abfrageparameter, den Sie verwenden, um Ihre Rückruffunktion zu definieren, übergeben:

```php
Flight::jsonp(['id' => 123], 'q');
```

So erhalten Sie beim Senden einer GET-Anfrage mit `?q=my_func` die Ausgabe:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameter angeben, wird standardmäßig `jsonp` verwendet.

## Weiterleitung zu einer anderen URL

Sie können die aktuelle Anfrage durch Verwendung der `redirect()`-Methode und Angabe
einer neuen URL umleiten:

```php
Flight::redirect('/neuer/ort');
```

Standardmäßig sendet Flight einen HTTP-Statuscode 303 ("Siehe Andere"). Sie können optional auch einen
benutzerdefinierten Code festlegen:

```php
Flight::redirect('/neuer/ort', 401);
```

## Beenden

Sie können das Framework jederzeit anhalten, indem Sie die `halt`-Methode aufrufen:

```php
Flight::halt();
```

Sie können auch optional einen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Bin gleich zurück...');
```

Das Aufrufen von `halt` verwirft jeglichen Antwortinhalt bis zu diesem Zeitpunkt. Wenn Sie das Framework anhalten und den aktuellen Antwortinhalt ausgeben möchten, verwenden Sie die `stop`-Methode:

```php
Flight::stop();
```

## Löschen von Antwortdaten

Sie können den Antwortbody und Header löschen, indem Sie die `clear()`-Methode verwenden. Dadurch werden
alle Header der Antwort zugewiesen, der Antwortbody gelöscht und der Statuscode auf `200` gesetzt.

```php
Flight::response()->clear();
```

### Nur Antwortbody löschen

Wenn Sie nur den Antwortbody löschen möchten, können Sie die `clearBody()`-Methode verwenden:

```php
// Dies behält weiterhin alle Header bei, die für das response() Objekt festgelegt wurden.
Flight::response()->clearBody();
```

## HTTP-Caching

Flight bietet eine integrierte Unterstützung für das Caching auf HTTP-Ebene. Wenn die Cachebedingung erfüllt ist,
gibt Flight eine HTTP `304 Not Modified`-Antwort zurück. Wenn der Client das nächste Mal die
gleiche Ressource anfordert, wird er aufgefordert, seine lokal
zwischengespeicherte Version zu verwenden.

### Caching auf Routenebene

Wenn Sie Ihre gesamte Antwort zwischenspeichern möchten, können Sie die `cache()`-Methode verwenden und die Zeit zum Zwischenspeichern angeben.

```php

// Dies zwischenspeichert die Antwort für 5 Minuten
Flight::route('/nachrichten', function () {
  Flight::response()->cache(time() + 300);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});

// Alternativ können Sie einen String verwenden, den Sie an die strtotime() Methode übergeben würden
Flight::route('/nachrichten', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### Zuletzt geändert

Sie können die `lastModified`-Methode verwenden und einen UNIX-Zeitstempel übergeben, um das Datum festzulegen
und Uhrzeit, zu der eine Seite zuletzt geändert wurde. Der Client wird weiterhin seinen Cache verwenden, bis
der zuletzt geänderte Wert geändert wird.

```php
Flight::route('/nachrichten', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### ETag

`ETag`-Caching ist ähnlich wie `Last-Modified`, außer dass Sie eine beliebige ID für die Ressource festlegen können:

```php
Flight::route('/nachrichten', function () {
  Flight::etag('meine-eindeutige-id');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Bitte beachten Sie, dass das Aufrufen von `lastModified` oder `etag` sowohl den Cache-Wert setzen als auch überprüfen wird.
Wenn der Cache-Wert zwischen den Anfragen gleich ist, sendet Flight sofort eine `HTTP 304`-Antwort und stoppt die Verarbeitung.

### Herunterladen einer Datei

Es gibt eine Hilfsmethode zum Herunterladen einer Datei. Sie können die `download`-Methode und den Dateipfad verwenden.

```php
Flight::route('/download', function () {
  Flight::download('/pfad/zur/datei.txt');
});
```