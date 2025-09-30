# flightphp/cache

Leichte, einfache und eigenständige PHP-In-Datei-Caching-Klasse, geforkt von [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Vorteile** 
- Leicht, eigenständig und einfach
- Aller Code in einer Datei - keine sinnlosen Treiber.
- Sicher - jede generierte Cache-Datei hat einen PHP-Header mit die, was direkten Zugriff unmöglich macht, selbst wenn jemand den Pfad kennt und Ihr Server nicht richtig konfiguriert ist
- Gut dokumentiert und getestet
- Behandelt Konkurrenz richtig über flock
- Unterstützt PHP 7.4+
- Kostenlos unter einer MIT-Lizenz

Diese Dokumentationsseite verwendet diese Bibliothek, um jede der Seiten zu cachen!

Klicken Sie [hier](https://github.com/flightphp/cache), um den Code anzusehen.

## Installation

Installieren Sie über composer:

```bash
composer require flightphp/cache
```

## Verwendung

Die Verwendung ist ziemlich unkompliziert. Dies speichert eine Cache-Datei im Cache-Verzeichnis.

```php
use flight\Cache;

$app = Flight::app();

// Sie übergeben das Verzeichnis, in dem der Cache gespeichert wird, an den Konstruktor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Dies stellt sicher, dass der Cache nur im Produktionsmodus verwendet wird
	// ENVIRONMENT ist eine Konstante, die in Ihrer Bootstrap-Datei oder anderswo in Ihrer App gesetzt wird
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Einen Cache-Wert abrufen

Sie verwenden die `get()`-Methode, um einen gecachten Wert abzurufen. Wenn Sie eine Bequemlichkeitsmethode wollen, die den Cache erneuert, wenn er abgelaufen ist, können Sie `refreshIfExpired()` verwenden.

```php

// Cache-Instanz abrufen
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // return data to be cached
}, 10); // 10 Sekunden

// oder
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 Sekunden
}
```

### Einen Cache-Wert speichern

Sie verwenden die `set()`-Methode, um einen Wert im Cache zu speichern.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 Sekunden
```

### Einen Cache-Wert löschen

Sie verwenden die `delete()`-Methode, um einen Wert im Cache zu löschen.

```php
Flight::cache()->delete('simple-cache-test');
```

### Überprüfen, ob ein Cache-Wert existiert

Sie verwenden die `exists()`-Methode, um zu überprüfen, ob ein Wert im Cache existiert.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// etwas tun
}
```

### Den Cache leeren
Sie verwenden die `flush()`-Methode, um den gesamten Cache zu leeren.

```php
Flight::cache()->flush();
```

### Metadaten mit Cache abrufen

Wenn Sie Timestamps und andere Metadaten zu einem Cache-Eintrag abrufen möchten, stellen Sie sicher, dass Sie `true` als korrekten Parameter übergeben.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // return data to be cached
}, 10, true); // true = return with metadata
// oder
$data = $cache->get("simple-cache-meta-test", true); // true = return with metadata

/*
Beispiel für einen gecachten Eintrag, der mit Metadaten abgerufen wird:
{
    "time":1511667506, <-- save unix timestamp
    "expire":10,       <-- expire time in seconds
    "data":"04:38:26", <-- unserialized data
    "permanent":false
}

Mit Metadaten können wir z. B. berechnen, wann der Eintrag gespeichert wurde oder wann er abläuft
Wir können auch auf die Daten selbst mit dem "data"-Schlüssel zugreifen
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // get unix timestamp when data expires and subtract current timestamp from it
$cacheddate = $data["data"]; // we access the data itself with the "data" key

echo "Latest cache save: $cacheddate, expires in $expiresin seconds";
```

## Dokumentation

Besuchen Sie [https://github.com/flightphp/cache](https://github.com/flightphp/cache), um den Code anzusehen. Stellen Sie sicher, dass Sie den [examples](https://github.com/flightphp/cache/tree/master/examples)-Ordner für zusätzliche Möglichkeiten zur Verwendung des Caches ansehen.