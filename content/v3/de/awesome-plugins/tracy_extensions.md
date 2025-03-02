Tracy Flight Panel Erweiterungen
=====

Dies ist eine Sammlung von Erweiterungen, um die Arbeit mit Flight etwas komfortabler zu gestalten.

- Flight - Analysiere alle Flight-Variablen.
- Datenbank - Analysiere alle Abfragen auf der Seite (wenn Sie die Datenbankverbindung korrekt initialisieren)
- Anfrage - Analysiere alle `$_SERVER`-Variablen und untersuche alle globalen Payloads (`$_GET`, `$_POST`, `$_FILES`)
- Sitzung - Analysiere alle `$_SESSION`-Variablen, wenn Sitzungen aktiv sind.

Dies ist das Panel

![Flight-Leiste](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Und jedes Panel zeigt sehr hilfreiche Informationen über Ihre Anwendung!

![Flight-Daten](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight-Datenbank](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight-Anfrage](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Klicke [hier](https://github.com/flightphp/tracy-extensions), um den Code anzusehen.

Installation
-------
Führen Sie `composer require flightphp/tracy-extensions --dev` aus und los geht's!

Konfiguration
-------
Es gibt sehr wenig Konfiguration, die Sie benötigen, um dies zu starten. Sie müssen den Tracy-Debugger initialisieren, bevor Sie dies verwenden [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// Bootstrap-Code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Möglicherweise müssen Sie Ihre Umgebung mit Debugger::enable(Debugger::DEVELOPMENT) angeben

// Wenn Sie Datenbankverbindungen in Ihrer App verwenden, gibt es einen
// erforderlichen PDO-Wrapper, der NUR IN DER ENTWICKLUNG verwendet werden sollte (bitte nicht in der Produktion!)
// Er hat die gleichen Parameter wie eine reguläre PDO-Verbindung
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// Oder wenn Sie dies an das Flight-Framework anhängen
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// Jetzt erfasst es die Zeit, die Abfrage und die Parameter, wann immer Sie eine Abfrage machen

// Dies verbindet die Punkte
if(Debugger::$showBar === true) {
	// Dies muss falsch sein, oder Tracy kann tatsächlich nicht gerendert werden :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// mehr Code

Flight::start();
```

## Zusätzliche Konfiguration

### Sitzungsdaten
Wenn Sie einen benutzerdefinierten Sitzungshandler haben (wie z.B. ghostff/session), können Sie beliebige Sitzungsdaten an Tracy übergeben, und es wird sie automatisch für Sie ausgeben. Übergeben Sie es mit dem `session_data`-Schlüssel im zweiten Parameter des Konstruktors von `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Dies muss falsch sein, oder Tracy kann tatsächlich nicht gerendert werden :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// Routen und andere Dinge...

Flight::start();
```

### Latte

Wenn Sie Latte in Ihrem Projekt installiert haben, können Sie das Latte-Panel verwenden, um Ihre Vorlagen zu analysieren. Sie können die Latte-Instanz an den `TracyExtensionLoader`-Konstruktor mit dem `latte`-Schlüssel im zweiten Parameter übergeben.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// Hier fügen Sie das Latte Panel zu Tracy hinzu
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Dies muss falsch sein, oder Tracy kann tatsächlich nicht gerendert werden :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
