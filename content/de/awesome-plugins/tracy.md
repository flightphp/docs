# Tracy

Tracy ist ein erstaunlicher Fehlerbehandlungstool, das mit Flight verwendet werden kann. Es hat mehrere Panels, die Ihnen bei der Fehlersuche Ihrer Anwendung helfen können. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen. Das Flight-Team hat speziell für Flight-Projekte mit dem [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) Plugin einige Panels erstellt.

## Installation

Installieren Sie es mit Composer. Und Sie möchten dies tatsächlich ohne die Entwicklerversion installieren, da Tracy über ein Produktionsfehlerbehandlungskomponente verfügt.

```bash
composer require tracy/tracy
```

## Grundkonfiguration

Es gibt einige grundlegende Konfigurationsoptionen, um loszulegen. Weitere Informationen dazu finden Sie in der [Tracy-Dokumentation](https://tracy.nette.org/de/konfiguration).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Tracy aktivieren
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // manchmal muss man explizit sein (auch Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // Sie können auch ein Array von IP-Adressen angeben

// Hier werden Fehler und Ausnahmen protokolliert. Stellen Sie sicher, dass dieses Verzeichnis existiert und beschreibbar ist.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // alle Fehler anzeigen
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // alle Fehler außer veraltete Hinweise
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // Wenn die Debugger-Leiste sichtbar ist, kann der Inhalt nicht von Flight festgelegt werden

	// Dies ist spezifisch für die Tracy-Erweiterung für Flight, wenn Sie diese bereits inkludiert haben
	// Andernfalls kommentieren Sie dies aus.
	new TracyExtensionLoader($app);
}
```

## Hilfreiche Tipps

Wenn Sie Ihren Code debuggen, gibt es einige sehr nützliche Funktionen, um Daten für Sie auszugeben.

- `bdump($var)` - Damit wird die Variable in der Tracy-Leiste in einem separaten Panel angezeigt.
- `dumpe($var)` - Damit wird die Variable angezeigt und das Programm wird sofort beendet.