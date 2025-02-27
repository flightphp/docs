# Antworten

Flight hilft Ihnen, einen Teil der Antwort-Header zu generieren, aber Sie haben die meiste Kontrolle darüber, was Sie an den Benutzer zurücksenden. Manchmal können Sie das `Response`-Objekt direkt aufrufen, aber meistens verwenden Sie die `Flight`-Instanz, um eine Antwort zu senden.

## Senden einer einfachen Antwort

Flight verwendet ob_start(), um die Ausgabe zu puffern. Das bedeutet, dass Sie `echo` oder `print` verwenden können, um eine Antwort an den Benutzer zu senden, und Flight wird sie erfassen und mit den entsprechenden Headern zurücksenden.

```php

// Dies wird "Hallo, Welt!" an den Browser des Benutzers senden
Flight::route('/', function() {
	echo "Hallo, Welt!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hallo, Welt!
```

Alternativ können Sie die Methode `write()` aufrufen, um den Body zu ergänzen.

```php

// Dies wird "Hallo, Welt!" an den Browser des Benutzers senden
Flight::route('/', function() {
	// ausführlich, aber manchmal notwendig, wenn Sie es brauchen
	Flight::response()->write("Hallo, Welt!");

	// wenn Sie den Body abrufen möchten, den Sie an diesem Punkt festgelegt haben
	// können Sie dies so tun
	$body = Flight::response()->getBody();
});
```

## Statuscodes

Sie können den Statuscode der Antwort festlegen, indem Sie die Methode `status` verwenden:

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

Wenn Sie den aktuellen Statuscode abrufen möchten, können Sie die Methode `status` ohne Argumente verwenden:

```php
Flight::response()->status(); // 200
```

## Festlegen eines Antwort-Body

Sie können den Antwort-Body mit der Methode `write` festlegen. Wenn Sie jedoch etwas ausgeben (echo oder print), 
wird es erfasst und als Antwort-Body über die Ausgabe-Pufferung gesendet.

```php
Flight::route('/', function() {
	Flight::response()->write("Hallo, Welt!");
});

// gleichwertig mit

Flight::route('/', function() {
	echo "Hallo, Welt!";
});
```

### Löschen eines Antwort-Bodys

Wenn Sie den Antwort-Body löschen möchten, können Sie die Methode `clearBody` verwenden:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hallo, Welt!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Ausführen eines Callbacks auf dem Antwort-Body

Sie können einen Callback auf dem Antwort-Body mit der Methode `addResponseBodyCallback` ausführen:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Dies wird alle Antworten für jede Route gzippen
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Sie können mehrere Callbacks hinzufügen, und sie werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden. Da dies jede [aufrufbare](https://www.php.net/manual/en/language.types.callable.php) Funktion akzeptieren kann, kann es ein Klassenarray `[ $class, 'method' ]`, eine Closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };` oder einen Funktionsnamen `'minify'` akzeptieren, wenn Sie beispielsweise eine Funktion hätten, um Ihren HTML-Code zu minimieren.

**Hinweis:** Route-Callbacks funktionieren nicht, wenn Sie die Konfigurationsoption `flight.v2.output_buffering` verwenden.

### Spezifischer Routen-Callback

Wenn Sie möchten, dass dies nur für eine bestimmte Route gilt, können Sie den Callback in der Route selbst hinzufügen:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Dies wird nur die Antwort für diese Route gzippen
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Middleware-Option

Sie können auch Middleware verwenden, um den Callback auf alle Routen anzuwenden:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Wenden Sie den Callback hier auf das response() Objekt an.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// Minimieren Sie den Body auf irgendeine Weise
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

Sie können einen Header wie den Inhaltstyp der Antwort festlegen, indem Sie die Methode `header` verwenden:

```php

// Dies wird "Hallo, Welt!" im Klartext an den Browser des Benutzers senden
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// oder
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hallo, Welt!";
});
```

## JSON

Flight bietet Unterstützung für das Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, 
geben Sie einige Daten an, die JSON-codiert werden sollen:

```php
Flight::json(['id' => 123]);
```

> **Hinweis:** Standardmäßig sendet Flight einen `Content-Type: application/json` Header mit der Antwort. Er wird auch die Konstanten `JSON_THROW_ON_ERROR` und `JSON_UNESCAPED_SLASHES` beim Codieren des JSON verwenden.

### JSON mit Statuscode

Sie können auch einen Statuscode als zweiten Parameter übergeben:

```php
Flight::json(['id' => 123], 201);
```

### JSON mit schöner Ausgabe

Sie können auch ein Argument in der letzten Position übergeben, um eine schöne Ausgabe zu aktivieren:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Wenn Sie die Optionen, die Sie an `Flight::json()` übergeben, ändern und eine einfachere Syntax wünschen, können Sie die JSON-Methode einfach umbenennen:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Und jetzt kann es so verwendet werden
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON und Ausführung anhalten (v3.10.0)

Wenn Sie eine JSON-Antwort senden und die Ausführung anhalten möchten, können Sie die Methode `jsonHalt` verwenden.
Dies ist nützlich für Fälle, in denen Sie möglicherweise eine Art von Autorisierung überprüfen und wenn 
der Benutzer nicht autorisiert ist, können Sie sofort eine JSON-Antwort senden, den vorhandenen Body-Inhalt löschen und die Ausführung anhalten.

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

Vor v3.10.0 müssten Sie etwas in dieser Art tun:

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

Für JSONP-Anfragen können Sie optional den Abfrageparameternamen übergeben, den Sie
verwenden, um Ihre Callback-Funktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Wenn Sie also eine GET-Anfrage mit `?q=my_func` durchführen, sollten Sie die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameternamen übergeben, wird standardmäßig `jsonp` verwendet.

## Weiterleitung zu einer anderen URL

Sie können die aktuelle Anfrage umleiten, indem Sie die Methode `redirect()` verwenden und
eine neue URL übergeben:

```php
Flight::redirect('/new/location');
```

Standardmäßig sendet Flight einen HTTP 303 ("See Other") Statuscode. Sie können optional einen
maßgeschneiderten Code festlegen:

```php
Flight::redirect('/new/location', 401);
```

## Stopp

Sie können das Framework jederzeit stoppen, indem Sie die Methode `halt` aufrufen:

```php
Flight::halt();
```

Sie können auch einen optionalen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Gleich zurück...');
```

Wenn Sie `halt` aufrufen, werden alle Antwortinhalte bis zu diesem Punkt verworfen. Wenn Sie das Framework stoppen und die aktuelle Antwort ausgeben möchten, verwenden Sie die Methode `stop`:

```php
Flight::stop();
```

## Antworten-Daten löschen

Sie können den Antwort-Body und die Header löschen, indem Sie die Methode `clear()` verwenden. Dies wird alle Header, die der Antwort zugeordnet sind, löschen, den Antwort-Body löschen und den Statuscode auf `200` setzen.

```php
Flight::response()->clear();
```

### Nur den Antwort-Body löschen

Wenn Sie nur den Antwort-Body löschen möchten, können Sie die Methode `clearBody()` verwenden:

```php
// Dies wird weiterhin alle Header beibehalten, die im response() Objekt festgelegt sind.
Flight::response()->clearBody();
```

## HTTP-Caching

Flight bietet integrierte Unterstützung für HTTP-Level-Caching. Wenn die Cache-Bedingung erfüllt ist, gibt Flight eine HTTP `304 Not Modified` Antwort zurück. Beim nächsten Mal, wenn der Client dieselbe Ressource anfordert, wird er aufgefordert, seine lokal zwischengespeicherte Version zu verwenden.

### Routing-Level-Caching

Wenn Sie Ihre gesamte Antwort zwischenspeichern möchten, können Sie die Methode `cache()` verwenden und die Cache-Zeit übergeben.

```php

// Dies wird die Antwort für 5 Minuten zwischenspeichern
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});

// Alternativ können Sie einen String verwenden, den Sie 
// an die Methode strtotime() übergeben würden
Flight::route('/news', function () {
  Flight::response()->cache('+5 Minuten');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### Last-Modified

Sie können die Methode `lastModified` verwenden und einen UNIX-Zeitstempel übergeben, um das Datum und die Uhrzeit festzulegen, an dem eine Seite zuletzt geändert wurde. Der Client wird weiterhin seinen Cache verwenden, bis der Wert der letzten Änderung geändert wird.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### ETag

Caching mit `ETag` ähnelt `Last-Modified`, mit dem Unterschied, dass Sie jede ID angeben können, die Sie für die Ressource möchten:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Beachten Sie, dass das Aufrufen von `lastModified` oder `etag` den Cache-Wert sowohl festlegt als auch überprüft. Wenn der Cache-Wert bei den Anfragen gleich ist, sendet Flight sofort eine `HTTP 304` Antwort und stoppt die Verarbeitung.

## Eine Datei herunterladen (v3.12.0)

Es gibt eine Hilfsmethode, um eine Datei herunterzuladen. Sie können die Methode `download` verwenden und den Pfad übergeben.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```