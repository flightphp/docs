# Antworten

Flight helft dabei, einen Teil der Antwortheader für Sie zu generieren, aber Sie haben die meiste Kontrolle darüber, was Sie an den Benutzer zurücksenden. Manchmal können Sie direkt auf das `Response`-Objekt zugreifen, aber meistens verwenden Sie die `Flight`-Instanz, um eine Antwort zu senden.

## Senden einer einfachen Antwort

Flight verwendet ob_start(), um die Ausgabe zu puffern. Das bedeutet, dass Sie `echo` oder `print` verwenden können, um eine Antwort an den Benutzer zu senden, und Flight wird sie erfassen und mit den entsprechenden Headern an den Benutzer zurücksenden.

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

Alternativ können Sie die Methode `write()` aufrufen, um den Inhalt des Body zu ergänzen.

```php

// Dies sendet "Hallo, Welt!" an den Browser des Benutzers
Flight::route('/', function() {
	// ausführlich, aber manchmal erforderlich, wenn Sie es brauchen
	Flight::response()->write("Hallo, Welt!");

	// wenn Sie den Body abrufen möchten, den Sie zu diesem Zeitpunkt festgelegt haben
	// können Sie dies wie folgt tun
	$body = Flight::response()->getBody();
});
```

## Statuscodes

Sie können den Statuscode der Antwort mit der Methode `status` festlegen:

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

## Festlegen eines Antwort-Headers

Sie können einen Header wie den Inhalts-Typ der Antwort festlegen, indem Sie die Methode `header` verwenden:

```php

// Dies sendet "Hallo, Welt!" im Klartext an den Browser des Benutzers
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hallo, Welt!";
});
```



## JSON

Flight bietet Unterstützung zum Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, übergeben Sie einige Daten, die in JSON codiert werden sollen:

```php
Flight::json(['id' => 123]);
```

### JSONP

Für JSONP-Anfragen können Sie optional den Abfrageparameter angeben, den Sie verwenden, um Ihre Callback-Funktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Wenn Sie eine GET-Anforderung mit `?q=my_func` senden, sollten Sie die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameter angeben, wird standardmäßig `jsonp` verwendet.

## Umleiten zu einer anderen URL

Sie können die aktuelle Anforderung mit der Methode `redirect()` umleiten, indem Sie eine neue URL angeben:

```php
Flight::redirect('/neuer/ort');
```

Standardmäßig sendet Flight einen HTTP-Statuscode 303 ("Siehe Andere"). Sie können optional einen benutzerdefinierten Code festlegen:

```php
Flight::redirect('/neuer/ort', 401);
```

## Stoppen

Sie können das Framework jederzeit anhalten, indem Sie die Methode `halt` aufrufen:

```php
Flight::halt();
```

Sie können auch optional einen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Bin gleich zurück...');
```

Ein Aufruf von `halt` verwirft jeglichen Antwortinhalt bis zu diesem Zeitpunkt. Wenn Sie das Framework stoppen und die aktuelle Antwort ausgeben möchten, verwenden Sie die Methode `stop`:

```php
Flight::stop();
```

## HTTP-Caching

Flight bietet integrierte Unterstützung für Caching auf HTTP-Ebene. Wenn die Caching-Bedingung erfüllt ist, sendet Flight eine HTTP `304 Not Modified`-Antwort zurück. Wenn der Client das nächste Mal auf die gleiche Ressource zugreift, wird er aufgefordert, seine lokal zwischengespeicherte Version zu verwenden.

### Caching auf Routenebene

Wenn Sie Ihre gesamte Antwort zwischenspeichern möchten, können Sie die `cache()`-Methode verwenden und die Zwischenspeicherungszeit angeben.

```php

// Dies zwischenspeichert die Antwort für 5 Minuten
Flight::route('/nachrichten', function () {
  Flight::cache(time() + 300);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});

// Alternativ können Sie einen String verwenden, den Sie an die strtotime() Methode übergeben würden
Flight::route('/nachrichten', function () {
  Flight::cache('+5 Minuten');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### Zuletzt geändert

Sie können die Methode `lastModified` verwenden und einen UNIX-Zeitstempel angeben, um das Datum und die Uhrzeit der letzten Modifikation einer Seite festzulegen. Der Client wird seinen Zwischenspeicher weiterhin nutzen, bis sich der Wert der letzten Modifikation ändert.

```php
Flight::route('/nachrichten', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### ETag

Das `ETag`-Caching ähnelt `Last-Modified`, außer dass Sie eine beliebige Kennung für die Ressource angeben können, die Sie möchten:

```php
Flight::route('/nachrichten', function () {
  Flight::etag('meine-eindeutige-id');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Beachten Sie, dass das Aufrufen von `lastModified` oder `etag` sowohl den Cache-Wert festlegt als auch überprüft. Wenn der Cache-Wert zwischen den Anfragen gleich ist, sendet Flight sofort eine `HTTP 304`-Antwort und bricht die Verarbeitung ab.