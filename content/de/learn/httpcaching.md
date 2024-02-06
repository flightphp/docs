# HTTP-Caching

Flight stellt eine eingebaute Unterstützung für das Caching auf HTTP-Ebene bereit. Wenn die Cache-Bedingung erfüllt ist, wird Flight eine HTTP `304 Not Modified`-Antwort zurückgeben. Das nächste Mal, wenn der Client die gleiche Ressource anfordert, wird er aufgefordert, die lokal zwischengespeicherte Version zu verwenden.

## Zuletzt geändert

Sie können die `lastModified`-Methode verwenden und einen UNIX-Zeitstempel übergeben, um das Datum und die Uhrzeit festzulegen, wann eine Seite zuletzt geändert wurde. Der Client wird weiterhin seinen Cache verwenden, bis der zuletzt geänderte Wert geändert wird.

```php
Flight::route('/nachrichten', function () {
  Flight::lastModified(1234567890);
  echo 'Dieser Inhalt wird zwischengespeichert sein.';
});
```

## ETag

Das Caching von `ETag` ist ähnlich wie `Last-Modified`, außer dass Sie eine beliebige ID für die Ressource angeben können:

```php
Flight::route('/nachrichten', function () {
  Flight::etag('meine-eindeutige-id');
  echo 'Dieser Inhalt wird zwischengespeichert sein.';
});
```

Denken Sie daran, dass das Aufrufen von `lastModified` oder `etag` sowohl den Cache-Wert setzen als auch überprüfen wird. Wenn der Cache-Wert zwischen den Anfragen identisch ist, sendet Flight sofort eine `HTTP 304`-Antwort und stoppt die Verarbeitung.