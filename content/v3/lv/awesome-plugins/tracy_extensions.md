Tracy Flight Panel Extensions
=====

Šis ir paplašinājumu kopums, lai darbs ar Flight būtu nedaudz bagātāks.

- Flight - Analizēt visas Flight mainīgās.
- Datubāze - Analizēt visus vaicājumus, kas ir palikuši izpildīti lapā (ja pareizi inicializējat datubāzes savienojumu).
- Pieprasījums - Analizēt visas `$_SERVER` mainīgās un izpētīt visus globālos slodzi (`$_GET`, `$_POST`, `$_FILES`).
- Sesija - Analizēt visas `$_SESSION` mainīgās, ja sesijas ir aktīvas.

Šis ir Panelis

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs panelis parāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Noklikšķiniet [šeit](https://github.com/flightphp/tracy-extensions), lai skatītu kodu.

Installation
-------
Izpildiet `composer require flightphp/tracy-extensions --dev`, un jūs esat ceļā!

Configuration
-------
Ir ļoti maz konfigurācijas, kas jums jāveic, lai to sāktu. Jums būs jāinicializē Tracy atkļūdotājs pirms šī izmantošanas [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Jums var būt jānorāda jūsu vide ar Debugger::enable(Debugger::DEVELOPMENT)

// ja jūs izmantojat datubāzes savienojumus savā lietojumprogrammā, ir nepieciešams PDO apvalks, ko izmantot TIKAI ATTĪSTĪBAS VIDĒ (nevis ražošanā, lūdzu!)
// Tam ir tie paši parametri kā regulāram PDO savienojumam
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// vai arī, ja jūs pievienojat to Flight framework
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// tagad, kad vienreiz izpildīsiet vaicājumu, tas uztvers laiku, vaicājumu un parametrus

// Šis savieno punktus
if(Debugger::$showBar === true) {
	// Šim jābūt false, vai Tracy nevarēs faktiski renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// vairāk koda

Flight::start();
```

## Additional Configuration

### Session Data
Ja jums ir pielāgots sesijas apstrādātājs (piemēram, ghostff/session), jūs varat nodot jebkuru sesijas datu masīvu Tracy, un tas automātiski izvadīs to jums. Jūs to nododiet ar `session_data` atslēgu otrajā parametru no `TracyExtensionLoader` konstruktora.

```php

use Ghostff\Session\Session;
// vai izmantojiet flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Šim jābūt false, vai Tracy nevarēs faktiski renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// maršruti un citas lietas...

Flight::start();
```

### Latte

_PHP 8.1+ ir nepieciešams šai sadaļai._

Ja jums ir Latte instalēts jūsu projektā, Tracy ir iebūvēta integrācija ar Latte, lai analizētu jūsu veidnes. Jūs vienkārši reģistrējat paplašinājumu ar jūsu Latte instanci.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// citas konfigurācijas...

	// pievienojiet paplašinājumu tikai tad, ja Tracy Debug Bar ir iespējots
	if(Debugger::$showBar === true) {
		// šeit jūs pievienojat Latte Paneli Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```