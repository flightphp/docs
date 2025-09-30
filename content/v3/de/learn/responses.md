# Responses

## Overview

Flight hilft dabei, Teile der Response-Header für Sie zu generieren, aber Sie haben die meiste Kontrolle darüber, was Sie an den Benutzer zurücksenden. Meistens greifen Sie direkt auf das `response()`-Objekt zu, aber Flight bietet einige Hilfsmethoden, um einige der Response-Header für Sie zu setzen.

## Understanding

Nachdem der Benutzer seine [request](/learn/requests)-Anfrage an Ihre Anwendung gesendet hat, müssen Sie eine angemessene Response für sie generieren. Sie haben Ihnen Informationen wie die bevorzugte Sprache, ob sie bestimmte Kompressionstypen handhaben können, ihren User Agent usw. gesendet, und nach der Verarbeitung von allem ist es Zeit, eine angemessene Response zurückzusenden. Dies kann das Setzen von Headern sein, das Ausgeben eines HTML- oder JSON-Bodys für sie oder das Weiterleiten zu einer Seite.

## Basic Usage

### Sending a Response Body

Flight verwendet `ob_start()`, um die Ausgabe zu puffern. Das bedeutet, Sie können `echo` oder `print` verwenden, um eine Response an den Benutzer zu senden, und Flight wird sie erfassen und mit den entsprechenden Headern an den Benutzer zurücksenden.

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

Als Alternative können Sie auch die `write()`-Methode aufrufen, um zum Body hinzuzufügen.

```php
// Dies wird "Hello, World!" an den Browser des Benutzers senden
Flight::route('/', function() {
	// ausführlich, aber erledigt den Job manchmal, wenn Sie es brauchen
	Flight::response()->write("Hello, World!");

	// wenn Sie den Body abrufen möchten, den Sie zu diesem Zeitpunkt gesetzt haben
	// können Sie das so tun
	$body = Flight::response()->getBody();
});
```

### JSON

Flight bietet Unterstützung für das Senden von JSON- und JSONP-Responses. Um eine JSON-Response zu senden, übergeben Sie einige Daten, die JSON-kodiert werden sollen:

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// irgendwie Ihre Benutzer aus einer Datenbank ziehen, z. B.
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* more users */ ]
```

> **Note:** Standardmäßig sendet Flight einen `Content-Type: application/json`-Header mit der Response. Es verwendet auch die Flags `JSON_THROW_ON_ERROR` und `JSON_UNESCAPED_SLASHES`, wenn das JSON kodiert wird.

#### JSON with Status Code

Sie können auch einen Statuscode als zweiten Argument übergeben:

```php
Flight::json(['id' => 123], 201);
```

#### JSON with Pretty Print

Sie können auch ein Argument an der letzten Position übergeben, um Pretty Printing zu aktivieren:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### Changing JSON Argument Order

`Flight::json()` ist eine sehr alte Methode, aber das Ziel von Flight ist es, die Abwärtskompatibilität für Projekte aufrechtzuerhalten. Es ist eigentlich sehr einfach, wenn Sie die Reihenfolge der Argumente neu definieren möchten, um eine einfachere Syntax zu verwenden, können Sie die JSON-Methode einfach wie jede andere Flight-Methode [neu zuordnen](/learn/extending):

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// jetzt müssen Sie nicht mehr `true, 'utf-8'` verwenden, wenn Sie die json()-Methode nutzen!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Und jetzt kann sie so verwendet werden
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON and Stopping Execution

_v3.10.0_

Wenn Sie eine JSON-Response senden und die Ausführung stoppen möchten, können Sie die `jsonHalt()`-Methode verwenden. Dies ist nützlich für Fälle, in denen Sie auf eine Art von Autorisierung prüfen und wenn der Benutzer nicht autorisiert ist, können Sie sofort eine JSON-Response senden, den bestehenden Body-Inhalt löschen und die Ausführung stoppen.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Prüfen, ob der Benutzer autorisiert ist
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// kein exit; hier benötigt.
	}

	// Mit dem Rest der Route fortfahren
});
```

Vor v3.10.0 hätten Sie etwas wie das tun müssen:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Prüfen, ob der Benutzer autorisiert ist
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Mit dem Rest der Route fortfahren
});
```

### Clearing a Response Body

Wenn Sie den Response-Body löschen möchten, können Sie die `clearBody`-Methode verwenden:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

Der obige Anwendungsfall ist wahrscheinlich nicht üblich, könnte jedoch häufiger vorkommen, wenn dies in einem [Middleware](/learn/middleware) verwendet wird.

### Running a Callback on the Response Body

Sie können einen Callback auf dem Response-Body ausführen, indem Sie die `addResponseBodyCallback`-Methode verwenden:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Dies wird alle Responses für jede Route gzippen
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Sie können mehrere Callbacks hinzufügen, und sie werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden. Da dies jede [callable](https://www.php.net/manual/en/language.types.callable.php) akzeptieren kann, kann es ein Klass-Array `[ $class, 'method' ]`, eine Closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };` oder einen Funktionsnamen `'minify'` akzeptieren, wenn Sie z. B. eine Funktion haben, um Ihren HTML-Code zu minimieren.

**Note:** Route-Callbacks funktionieren nicht, wenn Sie die Konfigurationsoption `flight.v2.output_buffering` verwenden.

#### Specific Route Callback

Wenn Sie möchten, dass dies nur auf eine spezifische Route angewendet wird, können Sie den Callback direkt in der Route hinzufügen:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Dies wird nur die Response für diese Route gzippen
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Middleware Option

Sie können auch [Middleware](/learn/middleware) verwenden, um den Callback auf alle Routes über Middleware anzuwenden:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Den Callback hier auf dem response()-Objekt anwenden.
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

### Status Codes

Sie können den Statuscode der Response mit der `status`-Methode setzen:

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

Wenn Sie den aktuellen Statuscode abrufen möchten, können Sie die `status`-Methode ohne Argumente verwenden:

```php
Flight::response()->status(); // 200
```

### Setting a Response Header

Sie können einen Header wie den Content-Type der Response mit der `header`-Methode setzen:

```php
// Dies wird "Hello, World!" als Plain Text an den Browser des Benutzers senden
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// oder
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### Redirect

Sie können die aktuelle Anfrage mit der `redirect()`-Methode weiterleiten und eine neue URL übergeben:

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // dies ist notwendig, damit die Funktionalität unten nicht ausgeführt wird
	}

	// den neuen Benutzer hinzufügen...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Note:** Standardmäßig sendet Flight einen HTTP 303 ("See Other")-Statuscode. Sie können optional einen benutzerdefinierten Code setzen:

```php
Flight::redirect('/new/location', 301); // permanent
```

### Stopping Route Execution

Sie können das Framework stoppen und sofort beenden, indem Sie die `halt`-Methode aufrufen:

```php
Flight::halt();
```

Sie können auch einen optionalen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Be right back...');
```

Das Aufrufen von `halt` verwirft jeglichen Response-Inhalt bis zu diesem Punkt und stoppt die gesamte Ausführung. Wenn Sie das Framework stoppen und die aktuelle Response ausgeben möchten, verwenden Sie die `stop`-Methode:

```php
Flight::stop($httpStatusCode = null);
```

> **Note:** `Flight::stop()` hat einiges seltsames Verhalten, wie z. B. dass es die Response ausgibt, aber die Ausführung Ihres Skripts fortsetzt, was möglicherweise nicht das ist, was Sie wollen. Sie können `exit` oder `return` nach dem Aufruf von `Flight::stop()` verwenden, um weitere Ausführung zu verhindern, aber es wird allgemein empfohlen, `Flight::halt()` zu verwenden.

Dies speichert den Header-Schlüssel und -Wert im Response-Objekt. Am Ende des Request-Lebenszyklus wird es die Header aufbauen und eine Response senden.

## Advanced Usage

### Sending a Header Immediately

Es kann Fälle geben, in denen Sie etwas Benutzerdefiniertes mit dem Header tun müssen und den Header in genau dieser Code-Zeile senden müssen, an der Sie arbeiten. Wenn Sie eine [streamed route](/learn/routing) setzen, ist das, was Sie brauchen. Das ist durch `response()->setRealHeader()` erreichbar.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

Für JSONP-Anfragen können Sie optional den Query-Parameter-Namen übergeben, den Sie verwenden, um Ihre Callback-Funktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Also, wenn Sie eine GET-Anfrage mit `?q=my_func` stellen, sollten Sie die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Query-Parameter-Namen übergeben, wird standardmäßig `jsonp` verwendet.

> **Note:** Wenn Sie 2025 und später immer noch JSONP-Anfragen verwenden, springen Sie in den Chat und erzählen Sie uns warum! Wir lieben es, gute Kampf-/Horror-Geschichten zu hören!

### Clearing Response Data

Sie können den Response-Body und die Header mit der `clear()`-Methode löschen. Dies löscht alle dem Response zugewiesenen Header, löscht den Response-Body und setzt den Statuscode auf `200`.

```php
Flight::response()->clear();
```

#### Clearing Response Body Only

Wenn Sie nur den Response-Body löschen möchten, können Sie die `clearBody()`-Methode verwenden:

```php
// Dies behält immer noch alle auf dem response()-Objekt gesetzten Header.
Flight::response()->clearBody();
```

### HTTP Caching

Flight bietet integrierte Unterstützung für HTTP-Level-Caching. Wenn die Caching-Bedingung erfüllt ist, wird Flight eine HTTP `304 Not Modified`-Response zurückgeben. Beim nächsten Mal, wenn der Client die gleiche Ressource anfordert, wird er aufgefordert, seine lokal gecachte Version zu verwenden.

#### Route Level Caching

Wenn Sie Ihre gesamte Response cachen möchten, können Sie die `cache()`-Methode verwenden und eine Cache-Zeit übergeben.

```php

// Dies cached die Response für 5 Minuten
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Alternativ können Sie einen String verwenden, den Sie an die strtotime()-Methode übergeben würden
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Sie können die `lastModified`-Methode verwenden und einen UNIX-Timestamp übergeben, um das Datum und die Zeit zu setzen, zu der eine Seite zuletzt geändert wurde. Der Client wird seinen Cache weiterhin verwenden, bis der Last-Modified-Wert geändert wird.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag`-Caching ist ähnlich wie `Last-Modified`, außer dass Sie jede ID für die Ressource angeben können, die Sie möchten:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Beachten Sie, dass das Aufrufen von entweder `lastModified` oder `etag` beide den Cache-Wert setzt und prüft. Wenn der Cache-Wert zwischen den Anfragen gleich ist, wird Flight sofort eine `HTTP 304`-Response senden und die Verarbeitung stoppen.

### Download a File

_v3.12.0_

Es gibt eine Hilfsmethode, um eine Datei an den Endbenutzer zu streamen. Sie können die `download`-Methode verwenden und den Pfad übergeben.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```

## See Also
- [Routing](/learn/routing) - Wie man Routes zu Controllern zuweist und Views rendert.
- [Requests](/learn/requests) - Verständnis, wie man eingehende Anfragen handhabt.
- [Middleware](/learn/middleware) - Verwendung von Middleware mit Routes für Authentifizierung, Logging usw.
- [Why a Framework?](/learn/why-frameworks) - Verständnis der Vorteile der Verwendung eines Frameworks wie Flight.
- [Extending](/learn/extending) - Wie man Flight mit eigener Funktionalität erweitert.

## Troubleshooting
- Wenn Sie Probleme mit nicht funktionierenden Redirects haben, stellen Sie sicher, dass Sie ein `return;` zur Methode hinzufügen.
- `stop()` und `halt()` sind nicht dasselbe. `halt()` stoppt die Ausführung sofort, während `stop()` die Ausführung fortsetzt.

## Changelog
- v3.12.0 - Added downloadFile helper method.
- v3.10.0 - Added `jsonHalt`.
- v1.0 - Initial release.