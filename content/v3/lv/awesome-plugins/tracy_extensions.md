Tracy Flight Panel Paplašinājumi
=====

Tas ir paplašinājumu kopums, lai padarītu darbu ar Flight nedaudz bagātāku.

- Flight - Analizēt visus Flight mainīgos.
- Datubāze - Analizēt visus vaicājumus, kas tika izpildīti lapā (ja pareizi inicializējat datubāzes savienojumu)
- Pieprasījums - Analizēt visus `$_SERVER` mainīgos un pārbaudīt visus globālos datus (`$_GET`, `$_POST`, `$_FILES`)
- Sesija - Analizēt visus `$_SESSION` mainīgos, ja sesijas ir aktīvas.

Tas ir Panelis

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs panelis rāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Noklikšķiniet [šeit](https://github.com/flightphp/tracy-extensions), lai apskatītu kodu.

Uzstādīšana
-------
Izpildiet `composer require flightphp/tracy-extensions --dev` un jūs esat ceļā!

Konfigurācija
-------
Ir ļoti maz konfigurācijas, ko jūs vajadzētu veikt, lai sāktu darbu. Jums būs jāinicializē Tracy tīklā, pirms to izmantosiet [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Jums var būt nepieciešams norādīt savu vidi ar Debugger::enable(Debugger::DEVELOPMENT)

// ja jūsu lietojumprogrammā izmantojat datubāzes savienojumus, ir 
// nepieciešama PDO iesaiņojums, ko izmantot TIKAI ATTISTĪBĀ (ne ražošanā, lūdzu!)
// Tam ir tādi paši parametri kā parastajam PDO savienojumam
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// vai, ja to pievienojat Flight ietvaram
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// tagad, kad jūs veicat vaicājumu, tas reģistrēs laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	// Tam jābūt nepatiesam, vai Tracy patiešām nevar attēlot :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// vēl kods

Flight::start();
```

## Papildu konfigurācija

### Sesijas dati
Ja jums ir pielāgota sesijas apstrādātājs (piemēram, ghostff/session), jūs varat nodot jebkuru sesiju datu masīvu Tracy un tas automātiski to izvadīs jums. Jūs to pārsūtāt ar `session_data` atslēgu otrajā parametru `TracyExtensionLoader` konstruktorā.

```php

use Ghostff\Session\Session;
// vai izmantojiet flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Tam jābūt nepatiesam, vai Tracy patiešām nevar attēlot :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// maršruti un citas lietas...

Flight::start();
```

### Latte

Ja jums ir Latte uzstādīts jūsu projektā, jūs varat izmantot Latte paneli, lai analizētu savus veidnes. Jūs varat nodot Latte instanci `TracyExtensionLoader` konstruktoram ar `latte` atslēgu otrajā parametru.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// šeit jūs pievienojat Latte paneli Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Tam jābūt nepatiesam, vai Tracy patiešām nevar attēlot :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```