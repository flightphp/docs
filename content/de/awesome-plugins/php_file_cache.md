# Wruczek/PHP-Datei-Cache

Leichte, einfache und eigenständige PHP-Dateicache-Klasse

**Vorteile**
- Leicht, eigenständig und einfach
- Der gesamte Code in einer Datei - keine sinnlosen Treiber.
- Sicher - jede generierte Cache-Datei hat einen PHP-Header mit Die, was direkten Zugriff selbst dann unmöglich macht, wenn jemand den Pfad kennt und Ihr Server nicht ordnungsgemäß konfiguriert ist
- Gut dokumentiert und getestet
- Behandelt Parallelität korrekt über flock
- Unterstützt PHP 5.4.0 - 7.1+
- Kostenlos unter einer MIT-Lizenz

# Installation

Installiere über Composer:

```bash
composer require wruczek/php-file-cache
```

# Verwendung

Die Verwendung ist ziemlich unkompliziert.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Du übergibst das Verzeichnis, in dem der Cache gespeichert wird, dem Konstruktor
$app->register('cache', PhpDateiCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Dies stellt sicher, dass der Cache nur im Produktionsmodus verwendet wird
	// UMGEBUNG ist eine Konstante, die in deiner Bootstrap-Datei oder anderswo in deiner App festgelegt ist
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Danach kannst du es in deinem Code wie folgt verwenden:

```php

// Hole Cache-Instanz
$cache = Flight::cache();
$data = $cache->refreshIfExpired('einfacher-cache-test', function () {
    return date("H:i:s"); // gibt die zu cachenden Daten zurück
}, 10); // 10 Sekunden

// oder
$data = $cache->retrieve('einfacher-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('einfacher-cache-test', $data, 10); // 10 Sekunden
}
```

# Dokumentation

Besuche [https://github.com/Wruczek/PHP-Datei-Cache](https://github.com/Wruczek/PHP-File-Cache) für die vollständige Dokumentation und sieh dir unbedingt den [Beispiele](https://github.com/Wruczek/PHP-Datei-Cache/tree/master/examples) Ordner an.