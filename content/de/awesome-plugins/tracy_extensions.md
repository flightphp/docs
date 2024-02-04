Tracy Flight Panel Extensions
=====

Dies ist ein Satz von Erweiterungen, um die Arbeit mit Flight etwas zu verbessern.

- Flight - Analysiere alle Flight-Variablen.
- Datenbank - Analysiere alle Abfragen, die auf der Seite ausgeführt wurden (wenn Sie die Datenbankverbindung korrekt initialisieren)
- Anfrage - Analysiere alle `$_SERVER`-Variablen und untersuche alle globalen Nutzlasten (`$_GET`, `$_POST`, `$_FILES`)
- Sitzung - Analysiere alle `$_SESSION`-Variablen, wenn Sitzungen aktiv sind.

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
Es gibt sehr wenig Konfiguration, die Sie benötigen, um damit zu beginnen. Sie müssen den Tracy-Debugger initiieren, bevor Sie dies verwenden müssen [https://tracy.nette.org/de/guide](https://tracy.nette.org/de/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// Bootstrapping-Code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Möglicherweise müssen Sie Ihre Umgebung mit Debugger::enable(Debugger::DEVELOPMENT) angeben

// Wenn Sie Datenbankverbindungen in Ihrer App verwenden, gibt es ein
// erforderlicher PDO-Wrapper nur IN ENTWICKLUNG (nicht in der Produktion bitte!)
// Es hat die gleichen Parameter wie eine reguläre PDO-Verbindung
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// oder wenn Sie dies an das Flight-Framework anhängen
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// Jetzt, wenn Sie eine Abfrage machen, werden die Zeit, die Abfrage und die Parameter erfasst

// Das verbindet die Punkte
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// mehr Code

Flight::start();
```