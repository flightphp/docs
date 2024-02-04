# Tracy

Tracy ist ein erstaunlicher Fehlerbehandlung, die mit Flight verwendet werden kann. Es hat eine Reihe von Panels, die Ihnen bei der Fehlerbehebung Ihrer Anwendung helfen können. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen. Das Flight-Team hat einige Panels speziell für Flight-Projekte mit dem [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) Plugin erstellt.

## Installation

Installieren Sie es mit Composer. Und Sie möchten dies tatsächlich ohne die Entwicklerversion installieren, da Tracy mit einem Produktionsfehlerbehandlungskomponente geliefert wird.

```bash
composer require tracy/tracy
```

## Grundkonfiguration

Es gibt einige grundlegende Konfigurationsoptionen, um loszulegen. Weitere Informationen finden Sie in der [Tracy-Dokumentation](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Tracy aktivieren
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // manchmal müssen Sie explizit sein (auch Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // Sie können auch ein Array von IP-Adressen angeben

// Hier werden Fehler und Ausnahmen protokolliert. Stellen Sie sicher, dass dieses Verzeichnis existiert und beschreibbar ist.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // alle Fehler anzeigen
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // alle Fehler außer veralteten Hinweisen
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // Wenn die Debugger-Leiste sichtbar ist, kann die Inhaltslänge nicht von Flight festgelegt werden

	// Dies ist spezifisch für die Tracy-Erweiterung für Flight, wenn Sie diese inkludiert haben,
	// kommentieren Sie andernfalls dies aus.
	new TracyExtensionLoader($app);
}
```

## Hilfreiche Tipps

Wenn Sie Ihren Code debuggen, gibt es einige sehr hilfreiche Funktionen, um Daten für Sie auszugeben.

- `bdump($var)` - Dies gibt die Variable in der Tracy-Leiste in einem separaten Panel aus.
- `dumpe($var)` - Dies gibt die Variable aus und beendet dann sofort.