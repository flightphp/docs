Tracy Flight Panel Erweiterungen
=====

Dies ist ein Satz von Erweiterungen, um die Arbeit mit Flight etwas umfangreicher zu gestalten.

- Flight - Analysiere alle Flight-Variablen.
- Datenbank - Analysiere alle Abfragen, die auf der Seite ausgeführt wurden (wenn Sie die Datenbankverbindung korrekt initiieren)
- Anfrage - Analysiere alle `$_SERVER`-Variablen und untersuche alle globalen Payloads (`$_GET`, `$_POST`, `$_FILES`)
- Sitzung - Analysiere alle `$_SESSION`-Variablen, wenn Sitzungen aktiv sind.

Dies ist das Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Und jedes Panel zeigt sehr hilfreiche Informationen über Ihre Anwendung an!

![Flight Daten](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Datenbank](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Anfrage](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Installation
-------
Führen Sie `composer require flightphp/tracy-extensions --dev` aus und los geht's!

Konfiguration
-------
Es gibt sehr wenig Konfiguration, die Sie durchführen müssen, um damit zu beginnen. Sie müssen den Tracy-Debugger initiieren, bevor Sie dies verwenden [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// Bootstrap-Code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Sie müssen möglicherweise Ihre Umgebung mit Debugger::enable(Debugger::DEVELOPMENT) angeben

// Wenn Sie Datenbankverbindungen in Ihrer App verwenden, gibt es einen
// erforderlichen PDO-Wrapper, der NUR IN DER ENTWICKLUNG verwendet werden soll (bitte nicht in der Produktion!)
// Es hat dieselben Parameter wie eine reguläre PDO-Verbindung
$pdo = new PdoQueryCapture('sqlite:test.db', 'benutzer', 'passwort');
// oder wenn Sie dies an das Flight-Framework anhängen
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'benutzer', 'passwort']);
// Jetzt, wenn Sie eine Abfrage erstellen, werden Zeit, Abfrage und Parameter erfasst

// Hier werden die Punkte verbunden
if(Debugger::$showBar === true) {
	// Dies muss falsch sein, damit Tracy tatsächlich gerendert werden kann :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// Weiterer Code

Flight::start();
```

## Zusätzliche Konfiguration

### Sitzungsdaten
Wenn Sie einen benutzerdefinierten Sitzungs-Handler haben (wie z.B. ghostff/session), können Sie beliebige Sitzungsdatenarrays an Tracy übergeben, und es wird automatisch ausgegeben. Sie geben es mit dem `session_data`-Schlüssel im zweiten Parameter des Konstruktors von `TracyExtensionLoader` an.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Dies muss falsch sein, damit Tracy tatsächlich gerendert werden kann :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// Routen und andere Dinge...

Flight::start();
```

### Latte

Wenn Latte in Ihrem Projekt installiert ist, können Sie das Latte-Panel verwenden, um Ihre Templates zu analysieren. Sie können die Latte-Instanz dem Konstruktor von `TracyExtensionLoader` mit dem Schlüssel `latte` im zweiten Parameter übergeben.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// Hier fügen Sie das Latte-Panel zu Tracy hinzu
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Dies muss falsch sein, damit Tracy tatsächlich gerendert werden kann :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
