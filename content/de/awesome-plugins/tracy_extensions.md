Tracy Flight Panel Erweiterungen
=====

Das ist eine Reihe von Erweiterungen, um die Arbeit mit Flight etwas zu bereichern.

- Flight - Analysiere alle Flugvariablen.
- Database - Analysiere alle Abfragen, die auf der Seite ausgeführt wurden (wenn Sie die Datenbankverbindung korrekt initialisieren)
- Request - Analysiere alle `$_SERVER`-Variablen und untersuche alle globalen Nutzlasten (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analysiere alle `$_SESSION`-Variablen, wenn Sitzungen aktiv sind.

Das ist das Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Und jedes Panel zeigt sehr hilfreiche Informationen über Ihre Anwendung!

![Flight Daten](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Datenbank](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Anfrage](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Installation
-------
Führen Sie `composer require flightphp/tracy-extensions --dev` aus und los geht's!

Konfiguration
-------
Es gibt sehr wenig Konfiguration, die Sie durchführen müssen, um dies zu starten. Sie müssen den Tracy-Debugger initialisieren, bevor Sie diese verwenden [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// Bootstrap-Code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Möglicherweise müssen Sie Ihre Umgebung mit Debugger::enable(Debugger::DEVELOPMENT) angeben

// Wenn Sie Datenbankverbindungen in Ihrer App verwenden, gibt es einen
// erforderlichen PDO-Wrapper, der NUR IN DER ENTWICKLUNG verwendet werden soll (nicht in der Produktion bitte!)
// Er hat die gleichen Parameter wie eine reguläre PDO-Verbindung
$pdo = new PdoQueryCapture('sqlite:test.db', 'benutzer', 'passwort');
// oder wenn Sie dies an das Flight-Framework anhängen
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'benutzer', 'passwort']);
// Jetzt werden immer, wenn Sie eine Abfrage durchführen, die Zeit, die Abfrage und die Parameter erfasst

// Das verbindet die Punkte
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// mehr Code

Flight::start();
```