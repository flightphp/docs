# Antworten

Flight hilft dabei, einen Teil der Antwortheader für Sie zu generieren, aber die meiste Kontrolle darüber, was an den Benutzer zurückgesendet wird, haben Sie. Manchmal können Sie direkt auf das `Response`-Objekt zugreifen, aber in den meisten Fällen verwenden Sie die `Flight`-Instanz, um eine Antwort zu senden.

## Senden einer einfachen Antwort

Flight verwendet ob_start(), um die Ausgabe zu puffern. Dies bedeutet, dass Sie `echo` oder `print` verwenden können, um eine Antwort an den Benutzer zu senden, und Flight wird diese erfassen und mit den entsprechenden Headern zurücksenden.

```php

// Dies sendet "Hallo Welt!" an den Browser des Benutzers
Flight::route('/', function() {
	echo "Hallo Welt!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hallo Welt!
```

Alternativ können Sie die Methode `write()` aufrufen, um auch zum Inhalt hinzuzufügen.

```php

// Dies sendet "Hallo Welt!" an den Browser des Benutzers
Flight::route('/', function() {
	// Ausführlich, funktioniert manchmal, wenn Sie es brauchen
	Flight::response()->write("Hallo Welt!");

	// Wenn Sie den Inhalt abrufen möchten, den Sie bis zu diesem Zeitpunkt festgelegt haben
	// So können Sie dies tun
	$body = Flight::response()->getBody();
});
```

## Statuscodes

Sie können den Statuscode der Antwort durch Verwendung der Methode `status` festlegen:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hallo Welt!";
	} else {
		Flight::response()->status(403);
		echo "Verboten";
	}
});
```

Wenn Sie den aktuellen Statuscode erhalten möchten, können Sie die Methode `status` ohne Argumente verwenden:

```php
Flight::response()->status(); // 200
```

## Festlegen eines Antwort-Headers

Sie können einen Header wie den Inhalts-Typ der Antwort durch Verwendung der Methode `header` festlegen:

```php

// Dies sendet "Hallo Welt!" im Klartext an den Browser des Benutzers
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hallo Welt!";
});
```



## JSON

Flight bietet Unterstützung für das Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden,
geben Sie einige Daten an, die JSON-kodiert werden sollen:

```php
Flight::json(['id' => 123]);
```

### JSONP

Für JSONP-Anfragen können Sie optional den Abfrageparameter angeben, den Sie
verwenden, um Ihre Rückruffunktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Daher sollten Sie bei einer GET-Anfrage mit `?q=my_func` die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameter angeben, wird standardmäßig `jsonp` verwendet.

## Umleiten auf eine andere URL

Sie können die aktuelle Anfrage durch Verwendung der Methode `redirect()` und Angabe
einer neuen URL umleiten:

```php
Flight::redirect('/neuer/standort');
```

Standardmäßig sendet Flight einen HTTP-Statuscode 303 ("Siehe Andere"). Sie können optional einen
benutzerdefinierten Code festlegen:

```php
Flight::redirect('/neuer/standort', 401);
```

## Stoppen

Sie können das Framework jederzeit anhalten, indem Sie die Methode `halt` aufrufen:

```php
Flight::halt();
```

Sie können auch einen optionalen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Bin gleich wieder da...');
```

Durch Aufrufen von `halt` wird jeglicher Antwortinhalt bis zu diesem Zeitpunkt verworfen. Wenn Sie das Framework stoppen und die aktuelle Antwort ausgeben möchten, verwenden Sie die Methode `stop`:

```php
Flight::stop();
```

## HTTP-Caching

Flight bietet integrierte Unterstützung für das Caching auf HTTP-Ebene. Wenn die Cache-Bedingung
erfüllt ist, gibt Flight eine HTTP-304-Not Modified-Antwort zurück. Beim nächsten Mal, wenn der
Client dieselbe Ressource anfordert, wird er aufgefordert, seine lokal
gespeicherte Version zu verwenden.

### Caching auf Routenebene

Wenn Sie Ihre gesamte Antwort zwischenspeichern möchten, können Sie die `cache()`-Methode verwenden und die Zeit für den Cache übergeben.

```php

// Dies zwischenspeichert die Antwort für 5 Minuten
Flight::route('/nachrichten', function () {
  Flight::cache(time() + 300);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});

// Alternativ können Sie einen String verwenden, den Sie an die strtotime()-Methode übergeben würden
Flight::route('/nachrichten', function () {
  Flight::cache('+5 Minuten');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### Zuletzt geändert

Sie können die `lastModified`-Methode verwenden und eine UNIX-Zeitstempel übergeben, um das Datum festzulegen,
an dem eine Seite zuletzt geändert wurde. Der Client wird weiterhin seinen Cache verwenden, bis
der zuletzt geänderte Wert geändert wird.

```php
Flight::route('/nachrichten', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### ETag

`ETag`-Caching ähnelt `Last-Modified`, außer dass Sie eine beliebige ID
für die Ressource angeben können:

```php
Flight::route('/nachrichten', function () {
  Flight::etag('meine-eindeutige-id');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Beachten Sie, dass das Aufrufen von `lastModified` oder `etag` sowohl den Cache-Wert setzt als auch prüft.
Wenn der Cache-Wert zwischen den Anfragen gleich ist, sendet Flight sofort
eine `HTTP 304`-Antwort und stoppt die Verarbeitung.