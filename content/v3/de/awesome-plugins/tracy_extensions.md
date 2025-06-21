Tracy Flight Panel-Erweiterungen
=====

Dies ist eine Reihe von Erweiterungen, um die Arbeit mit Flight etwas reicher zu gestalten.

- Flight - Analysiere alle Flight-Variablen.
- Database - Analysiere alle Abfragen, die auf der Seite ausgeführt wurden (wenn du die Datenbankverbindung korrekt initiierst).
- Request - Analysiere alle `$_SERVER`-Variablen und untersuche alle globalen Nutzlasten (`$_GET`, `$_POST`, `$_FILES`).
- Session - Analysiere alle `$_SESSION`-Variablen, wenn Sitzungen aktiv sind.

Dies ist das Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Und jedes Panel zeigt sehr hilfreiche Informationen über deine Anwendung an!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Klicken Sie [hier](https://github.com/flightphp/tracy-extensions), um den Code anzusehen.

Installation
-------
Führen Sie `composer require flightphp/tracy-extensions --dev` aus und Sie sind startklar!

Konfiguration
-------
Es gibt sehr wenig Konfiguration, die Sie vornehmen müssen, um dies zu starten. Sie müssen den Tracy-Debugger vor der Nutzung initiieren [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// Bootstrap-Code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Sie können Ihre Umgebung mit Debugger::enable(Debugger::DEVELOPMENT) angeben

// Wenn Sie Datenbankverbindungen in Ihrer App verwenden, gibt es einen
// erforderlichen PDO-Wrapper, der NUR IN DER ENTWICKLUNG (nicht in der Produktion!) verwendet werden sollte
// Er hat die gleichen Parameter wie eine reguläre PDO-Verbindung
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// oder wenn Sie dies an das Flight-Framework anhängen
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// Nun wird bei jeder Abfrage die Zeit, die Abfrage und die Parameter erfasst

// Dies verbindet die Punkte
if(Debugger::$showBar === true) {
	// Dies muss false sein, sonst kann Tracy nicht rendern :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// Weiterer Code

Flight::start();
```

## Zusätzliche Konfiguration

### Session-Daten
Wenn Sie einen benutzerdefinierten Session-Handler haben (z. B. ghostff/session), können Sie ein Array von Session-Daten an Tracy übergeben und es wird automatisch ausgegeben. Sie übergeben es mit dem Schlüssel `session_data` im zweiten Parameter des Konstruktors von `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// oder verwenden Sie flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Dies muss false sein, sonst kann Tracy nicht rendern :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// Routen und andere Dinge...

Flight::start();
```

### Latte

Wenn Sie Latte in Ihrem Projekt installiert haben, können Sie das Latte-Panel verwenden, um Ihre Vorlagen zu analysieren. Sie können die Latte-Instanz an den Konstruktor von `TracyExtensionLoader` mit dem Schlüssel `latte` im zweiten Parameter übergeben.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// Hier fügen Sie die Latte-Erweiterung zu Tracy hinzu
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Dies muss false sein, sonst kann Tracy nicht rendern :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```