# Antworten

Flight hilft Ihnen, Teile der Antwort-Header zu generieren, aber Sie haben die meiste Kontrolle darüber, was Sie an den Benutzer senden. Manchmal können Sie direkt auf das `Response`-Objekt zugreifen, aber meistens verwenden Sie die `Flight`-Instanz, um eine Antwort zu senden.

## Eine einfache Antwort senden

Flight verwendet ob_start(), um die Ausgabe zu puffern. Das bedeutet, Sie können `echo` oder `print` verwenden, um eine Antwort an den Benutzer zu senden, und Flight wird sie erfassen und mit den entsprechenden Headern an den Benutzer zurücksenden.

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

Als Alternative können Sie die Methode `write()` aufrufen, um den Body hinzuzufügen.

```php
// Dies wird "Hello, World!" an den Browser des Benutzers senden
Flight::route('/', function() {
	// detailliert, aber nützlich in manchen Fällen, wenn Sie es benötigen
	Flight::response()->write("Hello, World!");

	// Wenn Sie den Body abrufen möchten, den Sie zu diesem Zeitpunkt gesetzt haben
	// können Sie das wie folgt tun
	$body = Flight::response()->getBody();
});
```

## Status-Codes

Sie können den Status-Code der Antwort mit der Methode `status` festlegen:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

Wenn Sie den aktuellen Status-Code abrufen möchten, können Sie die Methode `status` ohne Argumente verwenden:

```php
Flight::response()->status(); // 200
```

## Einen Antwort-Body festlegen

Sie können den Antwort-Body mit der Methode `write` festlegen, aber wenn Sie etwas mit `echo` oder `print` ausgeben, wird es erfasst und als Antwort-Body über die Ausgabepufferung gesendet.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// dasselbe wie

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Einen Antwort-Body löschen

Wenn Sie den Antwort-Body löschen möchten, können Sie die Methode `clearBody` verwenden:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Einen Callback auf dem Antwort-Body ausführen

Sie können einen Callback auf dem Antwort-Body mit der Methode `addResponseBodyCallback` ausführen:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Dies wird alle Antworten für jede Route komprimieren
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Sie können mehrere Callbacks hinzufügen, und sie werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden. Da dies jeden [callable](https://www.php.net/manual/en/language.types.callable.php) akzeptiert, kann es ein Klass-Array wie `[ $class, 'method' ]`, eine Closure wie `$strReplace = function($body) { str_replace('hi', 'there', $body); };` oder einen Funktionsnamen wie `'minify'` akzeptieren, wenn Sie eine Funktion haben, um Ihren HTML-Code zu minimieren.

**Hinweis:** Route-Callbacks funktionieren nicht, wenn Sie die Konfigurationsoption `flight.v2.output_buffering` verwenden.

### Callback für eine bestimmte Route

Wenn Sie möchten, dass dies nur auf eine bestimmte Route angewendet wird, können Sie den Callback in der Route selbst hinzufügen:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Dies wird nur die Antwort für diese Route komprimieren
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
		// Minifizieren Sie den Body auf irgendeine Weise
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Einen Antwort-Header festlegen

Sie können einen Header wie den Content-Typ der Antwort mit der Methode `header` festlegen:

```php
// Dies wird "Hello, World!" als einfachen Text an den Browser des Benutzers senden
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// oder
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight bietet Unterstützung für das Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, übergeben Sie einige Daten, die JSON-codiert werden sollen:

```php
Flight::json(['id' => 123]);
```

> **Hinweis:** Standardmäßig sendet Flight einen `Content-Type: application/json`-Header mit der Antwort. Es verwendet auch die Konstanten `JSON_THROW_ON_ERROR` und `JSON_UNESCAPED_SLASHES` beim Codieren des JSON.

### JSON mit Status-Code

Sie können auch einen Status-Code als zweites Argument übergeben:

```php
Flight::json(['id' => 123], 201);
```

### JSON mit Pretty Print

Sie können ein Argument an der letzten Position übergeben, um das Pretty Printing zu aktivieren:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Wenn Sie Optionen, die an `Flight::json()` übergeben werden, ändern und eine einfachere Syntax wollen, können Sie die JSON-Methode neu zuweisen:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Und nun kann es so verwendet werden
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON und Ausführung stoppen (v3.10.0)

Wenn Sie eine JSON-Antwort senden und die Ausführung stoppen möchten, können Sie die Methode `jsonHalt()` verwenden.
Das ist nützlich für Fälle, in denen Sie eine Autorisierung prüfen und, wenn der Benutzer nicht autorisiert ist, sofort eine JSON-Antwort senden, den vorhandenen Body-Inhalt löschen und die Ausführung stoppen.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Überprüfen, ob der Benutzer autorisiert ist
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Mit dem Rest der Route fortfahren
});
```

Vor v3.10.0 müssten Sie etwas wie dies tun:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Überprüfen, ob der Benutzer autorisiert ist
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Mit dem Rest der Route fortfahren
});
```

### JSONP

Für JSONP-Anfragen können Sie optional den Namen des Query-Parameters übergeben, den Sie verwenden, um Ihre Callback-Funktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Also, bei einer GET-Anfrage mit `?q=my_func`, sollten Sie die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Query-Parameternamen übergeben, wird er standardmäßig auf `jsonp` gesetzt.

## Umleitung zu einer anderen URL

Sie können die aktuelle Anfrage umleiten, indem Sie die Methode `redirect()` verwenden und eine neue URL übergeben:

```php
Flight::redirect('/new/location');
```

Standardmäßig sendet Flight einen HTTP-Status-Code 303 ("See Other"). Sie können optional einen benutzerdefinierten Code festlegen:

```php
Flight::redirect('/new/location', 401);
```

## Stoppen

Sie können das Framework an jedem Punkt stoppen, indem Sie die Methode `halt` aufrufen:

```php
Flight::halt();
```

Sie können auch einen optionalen HTTP-Status-Code und eine Nachricht angeben:

```php
Flight::halt(200, 'Be right back...');
```

Das Aufrufen von `halt` wird jeglichen Antwort-Inhalt bis zu diesem Punkt verwerfen. Wenn Sie das Framework stoppen und den aktuellen Antwort-Inhalt ausgeben möchten, verwenden Sie die Methode `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Hinweis:** `Flight::stop()` hat ein ungewöhnliches Verhalten, wie z.B. dass es die Antwort ausgibt, aber die Ausführung des Skripts fortsetzt. Sie können `exit` oder `return` nach dem Aufruf von `Flight::stop()` verwenden, um eine weitere Ausführung zu verhindern, aber es wird empfohlen, `Flight::halt()` zu verwenden.

## Antwort-Daten löschen

Sie können den Antwort-Body und die Header mit der Methode `clear()` löschen. Das wird alle Header, die der Antwort zugewiesen wurden, löschen, den Antwort-Body löschen und den Status-Code auf `200` setzen.

```php
Flight::response()->clear();
```

### Nur Antwort-Body löschen

Wenn Sie nur den Antwort-Body löschen möchten, können Sie die Methode `clearBody()` verwenden:

```php
// Dies behält alle Header, die auf das response()-Objekt gesetzt wurden.
Flight::response()->clearBody();
```

## HTTP-Caching

Flight bietet integrierte Unterstützung für HTTP-Level-Caching. Wenn die Caching-Bedingung erfüllt ist, wird Flight eine HTTP-`304 Not Modified`-Antwort zurückgeben. Beim nächsten Mal, wenn der Client die gleiche Ressource anfordert, wird er aufgefordert, seine lokal gecachte Version zu verwenden.

### Caching auf Route-Ebene

Wenn Sie Ihre gesamte Antwort cachen möchten, können Sie die Methode `cache()` verwenden und die Cache-Dauer übergeben.

```php
// Dies wird die Antwort für 5 Minuten cachen
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Dieser Inhalt wird gecacht.';
});

// Alternativ können Sie einen String verwenden, den Sie an strtotime() übergeben würden
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Dieser Inhalt wird gecacht.';
});
```

### Last-Modified

Sie können die Methode `lastModified` verwenden und einen UNIX-Zeitstempel übergeben, um das Datum und die Uhrzeit festzulegen, wann eine Seite zuletzt geändert wurde. Der Client wird seine Cache weiter verwenden, bis der letzte geänderte Wert geändert wird.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird gecacht.';
});
```

### ETag

`ETag`-Caching ist ähnlich wie `Last-Modified`, außer dass Sie eine beliebige ID für die Ressource angeben können:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Dieser Inhalt wird gecacht.';
});
```

Beachten Sie, dass das Aufrufen von entweder `lastModified` oder `etag` sowohl den Cache-Wert setzt als auch überprüft. Wenn der Cache-Wert zwischen Anfragen identisch ist, sendet Flight sofort eine `HTTP 304`-Antwort und stoppt die Verarbeitung.

## Eine Datei herunterladen (v3.12.0)

Es gibt eine Hilfsmethode, um eine Datei herunterzuladen. Sie können die Methode `download` verwenden und den Pfad übergeben.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```