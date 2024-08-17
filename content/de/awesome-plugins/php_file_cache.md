# Wruczek/PHP-File-Cache

Leichte, einfache und eigenständige PHP-In-File-Caching-Klasse

**Vorteile**
- Leicht, eigenständig und einfach
- Der gesamte Code in einer Datei - keine sinnlosen Treiber.
- Sicher - jede generierte Cache-Datei hat einen PHP-Header mit "die", was direkten Zugriff unmöglich macht, selbst wenn jemand den Pfad kennt und Ihr Server nicht ordnungsgemäß konfiguriert ist.
- Gut dokumentiert und getestet
- Behandelt Konkurrenz richtig über flock
- Unterstützt PHP 5.4.0 - 7.1+
- Kostenlos unter einer MIT-Lizenz

Klicken Sie [hier](https://github.com/Wruczek/PHP-File-Cache), um den Code anzuzeigen.

## Installation

Installation über Composer:

```bash
composer require wruczek/php-file-cache
```

## Verwendung

Die Verwendung ist ziemlich einfach.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Sie geben das Verzeichnis, in dem der Cache gespeichert wird, dem Konstruktor weiter
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Dies stellt sicher, dass der Cache nur verwendet wird, wenn sich die Anwendung im Produktionsmodus befindet
	// ENVIRONMENT ist eine Konstante, die in Ihrer Bootstrap-Datei oder anderswo in Ihrer Anwendung festgelegt ist
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Dann können Sie es in Ihrem Code wie folgt verwenden:

```php

// Cache-Instanz abrufen
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // Daten zur Zwischenspeicherung zurückgeben
}, 10); // 10 Sekunden

// oder
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 Sekunden
}
```

## Dokumentation

Besuchen Sie [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) für die vollständige Dokumentation und stellen Sie sicher, dass Sie den [Beispiele](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples)-Ordner sehen.