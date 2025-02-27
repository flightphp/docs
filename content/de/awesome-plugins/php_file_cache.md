# flightphp/cache

Leichte, einfache und eigenständige PHP-In-Datei-Caching-Klasse

**Vorteile** 
- Leicht, eigenständig und einfach
- Alle Codes in einer Datei - keine sinnlosen Treiber.
- Sicher - jede generierte Cache-Datei hat einen PHP-Header mit die, der den direkten Zugriff unmöglich macht, selbst wenn jemand den Pfad kennt und Ihr Server nicht richtig konfiguriert ist
- Gut dokumentiert und getestet
- Handhabt die Konkurrierendheit korrekt über flock
- Unterstützt PHP 7.4+
- Kostenlos unter einer MIT-Lizenz

Diese Dokumentationsseite verwendet diese Bibliothek, um jede der Seiten zu cachen!

Klicken Sie [hier](https://github.com/flightphp/cache), um den Code zu sehen.

## Installation

Installation über Composer:

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
	// ENVIRONMENT ist eine Konstante, die in Ihrer Bootstrap-Datei oder an einer anderen Stelle in Ihrer App gesetzt wird
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Dann können Sie es in Ihrem Code so verwenden:

```php

// Erhalte Cache-Instanz
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // gibt die zu cachenden Daten zurück
}, 10); // 10 Sekunden

// oder
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 Sekunden
}
```

## Dokumentation

Besuchen Sie [https://github.com/flightphp/cache](https://github.com/flightphp/cache) für die vollständige Dokumentation und stellen Sie sicher, dass Sie den [Beispiele](https://github.com/flightphp/cache/tree/master/examples) Ordner sehen.