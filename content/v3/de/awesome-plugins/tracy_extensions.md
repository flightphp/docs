Tracy Flight Panel Erweiterungen
=====

Dies ist eine Sammlung von Erweiterungen, um die Arbeit mit Flight ein wenig reicher zu gestalten.

- Flight - Analysiere alle Flight-Variablen.
- Datenbank - Analysiere alle Abfragen, die auf der Seite ausgeführt wurden (wenn du die Datenbankverbindung korrekt initiierst)
- Anfrage - Analysiere alle `$_SERVER`-Variablen und untersuche alle globalen Payloads (`$_GET`, `$_POST`, `$_FILES`)
- Sitzung - Analysiere alle `$_SESSION`-Variablen, wenn Sitzungen aktiv sind.

Dies ist das Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Und jedes Panel zeigt sehr hilfreiche Informationen über deine Anwendung an!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Klicke [hier](https://github.com/flightphp/tracy-extensions), um den Code zu sehen.

Installation
-------
Führe `composer require flightphp/tracy-extensions --dev` aus und du bist auf dem Weg!

Konfiguration
-------
Es gibt sehr wenig Konfiguration, die du machen musst, um dies zu starten. Du musst den Tracy-Debugger initiieren, bevor du dies verwendest [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// Bootstrap-Code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Möglicherweise musst du deine Umgebung mit Debugger::enable(Debugger::DEVELOPMENT) angeben

// Wenn du Datenbankverbindungen in deiner App verwendest, gibt es einen
// erforderlichen PDO-Wrapper, den du NUR IN DER ENTWICKLUNG verwenden kannst (bitte nicht in der Produktion!)
// Er hat die gleichen Parameter wie eine reguläre PDO-Verbindung
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// oder wenn du dies mit dem Flight-Framework verbindest
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// jetzt wird jedes Mal, wenn du eine Abfrage machst, die Zeit, Abfrage und Parameter erfasst

// Dies verbindet die Punkte
if(Debugger::$showBar === true) {
	// Dies muss falsch sein oder Tracy kann es nicht tatsächlich rendern :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// mehr Code

Flight::start();
```

## Zusätzliche Konfiguration

### Sitzungsdaten
Wenn du einen benutzerdefinierten Sitzungs-Handler (wie ghostff/session) hast, kannst du ein beliebiges Array von Sitzungsdaten an Tracy übergeben und es wird automatisch für dich ausgegeben. Du übergibst es mit dem Schlüssel `session_data` im zweiten Parameter des Konstruktors von `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// oder verwende flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Dies muss falsch sein oder Tracy kann es nicht tatsächlich rendern :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// Routen und andere Dinge...

Flight::start();
```

### Latte

Wenn du Latte in deinem Projekt installiert hast, kannst du das Latte-Panel verwenden, um deine Vorlagen zu analysieren. Du kannst die Latte-Instanz im Konstruktor von `TracyExtensionLoader` mit dem Schlüssel `latte` im zweiten Parameter übergeben.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// hier fügst du das Latte-Panel zu Tracy hinzu
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Dies muss falsch sein oder Tracy kann es nicht tatsächlich rendern :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```