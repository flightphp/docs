Tracy Flight Panel Extensions
=====

Dies ist eine Sammlung von Erweiterungen, um die Arbeit mit Flight etwas reicher zu gestalten.

- Flight - Analysiert alle Flight-Variablen.
- Database - Analysiert alle Abfragen, die auf der Seite ausgeführt wurden (wenn Sie die Datenbankverbindung korrekt initialisieren)
- Request - Analysiert alle `$_SERVER`-Variablen und untersucht alle globalen Payloads (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analysiert alle `$_SESSION`-Variablen, wenn Sessions aktiv sind.

Dies ist das Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Und jedes Panel zeigt sehr hilfreiche Informationen über Ihre Anwendung an!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Klicken Sie [hier](https://github.com/flightphp/tracy-extensions), um den Code anzusehen.

Installation
-------
Führen Sie `composer require flightphp/tracy-extensions --dev` aus und Sie sind startklar!

Configuration
-------
Es gibt sehr wenig Konfiguration, die Sie vornehmen müssen, um damit zu beginnen. Sie müssen den Tracy-Debugger vor der Verwendung dieser [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide) initialisieren:

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// You may need to specify your environment with Debugger::enable(Debugger::DEVELOPMENT)

// if you use database connections in your app, there is a 
// required PDO wrapper to use ONLY IN DEVELOPMENT (not production please!)
// It has the same parameters as a regular PDO connection
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// or if you attach this to the Flight framework
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// now whenever you make a query it will capture the time, query, and parameters

// This connects the dots
if(Debugger::$showBar === true) {
	// This needs to be false or Tracy can't actually render :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## Additional Configuration

### Session Data
If you have a custom session handler (such as ghostff/session), you can pass any array of session data to Tracy and it will automatically output it for you. You pass it in with the `session_data` key in the second parameter of the `TracyExtensionLoader` constructor.

```php

use Ghostff\Session\Session;
// or use flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// This needs to be false or Tracy can't actually render :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_PHP 8.1+ is required for this section._

If you have Latte installed in your project, Tracy has a native integration with Latte to analyze your templates. You simple register the extension with your Latte instance.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// only add the extension if Tracy Debug Bar is enabled
	if(Debugger::$showBar === true) {
		// this is where you add the Latte Panel to Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```