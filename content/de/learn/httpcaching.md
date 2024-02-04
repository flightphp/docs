# HTTP-Caching

Flight bietet eine integrierte Unterstützung für das Caching auf HTTP-Ebene. Wenn die Caching-Bedingung erfüllt ist, gibt Flight eine HTTP `304 Not Modified`-Antwort zurück. Das nächste Mal, wenn der Client dieselbe Ressource anfordert, wird er aufgefordert, seine lokal zwischengespeicherte Version zu verwenden.

## Last-Modified

Sie können die Methode `lastModified` verwenden und einen UNIX-Zeitstempel übergeben, um das Datum und die Uhrzeit festzulegen, an dem eine Seite zuletzt geändert wurde. Der Client wird seinen Cache weiterhin verwenden, bis der zuletzt geänderte Wert geändert wird.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

## ETag

Das Caching von `ETag` ist ähnlich wie bei `Last-Modified`, außer dass Sie eine beliebige ID für die Ressource angeben können:

```php
Flight::route('/news', function () {
  Flight::etag('meine-eindeutige-id');
  echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Bitte beachten Sie, dass das Aufrufen von `lastModified` oder `etag` sowohl den Cache-Wert festlegt als auch überprüft. Wenn der Cache-Wert zwischen den Anfragen gleich ist, sendet Flight sofort eine `HTTP 304`-Antwort und bricht die Verarbeitung ab.