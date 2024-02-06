Tracy Flight Panel Extensions
=====

Dies ist ein Satz von Erweiterungen, um die Arbeit mit Flight etwas umfangreicher zu gestalten.

- Flight - Analysiere alle Flight-Variablen.
- Datenbank - Analysiere alle Abfragen, die auf der Seite ausgeführt wurden (wenn Sie die Datenbankverbindung korrekt initialisieren)
- Anfrage - Analysiere alle `$_SERVER`-Variablen und untersuche alle globalen Payloads (`$_GET`, `$_POST`, `$_FILES`)
- Sitzung - Analysiere alle `$_SESSION`-Variablen, wenn Sitzungen aktiv sind.

Dies ist das Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Und jedes Panel zeigt sehr hilfreiche Informationen über Ihre Anwendung!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Installation
-------
Führen Sie `composer require flightphp/tracy-extensions --dev` aus und los geht's!

Konfiguration
-------
Es sind sehr wenige Konfigurationen erforderlich, um dies zu starten. Sie müssen den Tracy-Debugger initialisieren, bevor Sie dies verwenden können [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// Bootstrap-Code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Möglicherweise müssen Sie Ihre Umgebung mit Debugger::enable(Debugger::DEVELOPMENT) spezifizieren

// Wenn Sie Datenbankverbindungen in Ihrer App verwenden, gibt es ein
// erforderlicher PDO-Wrapper NUR IN DER ENTWICKLUNG verwenden (bitte nicht in Produktion!)
// Es hat die gleichen Parameter wie eine reguläre PDO-Verbindung
$pdo = new PdoQueryCapture('sqlite:test.db', 'benutzer', 'passwort');
// oder wenn Sie dies an das Flight-Framework anhängen
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'benutzer', 'passwort']);
// jetzt, wenn Sie eine Abfrage machen, wird die Zeit, die Abfrage und die Parameter erfasst

// Dies verbindet die Punkte
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// mehr Code

Flight::start();
```