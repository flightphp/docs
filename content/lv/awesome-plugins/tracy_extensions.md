Tracy Flight Panel Extensions
=====

Šis ir paplašinājumu kopums, kas padara darbu ar Flight nedaudz bagātāku.

- Lidojums - Analizēt visus Lidojuma mainīgos.
- Datubāze - Analizēt visas vaicājumus, kas ir izpildīti lapā (ja pareizi iedibināt datu bāzes savienojumu)
- Pieprasījums - Analizēt visas `$_SERVER` mainīgos un pārbaudīt visus globālos dati (`$_GET`, `$_POST`, `$_FILES`)
- Sesija - Analizēt visas `$_SESSION` mainīgos, ja sesijas ir aktīvas.

Šis ir Panelis

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs panelis parāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalācija
-------
Izpildiet `composer require flightphp/tracy-extensions --dev` un esat gatavs!

Konfigurācija
-------
Jums nav nepieciešama daudz konfigurēšana, lai sāktu. Jums būs jāinicē Tracy atkļūdošanas rīks pirms izmantojat to [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// uzsaistes kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Varat būt nepieciešams norādīt savu vidi ar Debugger::enable(Debugger::DEVELOPMENT)

// ja izmantojat datu bāzes savienojumus savā lietotnē, tur ir
// nepieciešams PDO apvalks, ko izmanto TIKAI IZSTRĀDĒ (lūdzu, neizmantojiet ražošanai!)
// Tas ir vienādi ar ierasta PDO savienojuma parametriem
$pdo = new PdoQueryCapture('sqlite:test.db', 'lietotājvārds', 'parole');
// vai, ja piesaistāt to Flight ietvarā
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'lietotājvārds', 'parole']);
// tagad, kad veicat vaicājumu, tas uztverts laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	// Tas jābūt nepatiesam, vai Tracy patiešām nevar renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// vairāk koda

Flight::start();
```

## Papildus konfigurācija

### Sesijas Dati
Ja jums ir pielāgots sesiju apstrādātājs (piemēram, ghostff/session), jūs varat nodot jebkuru sesiju datu masīvu Tracy un tas automātiski to izvadīs jums. Jūs to nododat ar `session_data` atslēgu `TracyExtensionLoader` konstruktorā.

```php

lietot Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Tas jābūt nepatiesam, vai Tracy patiešām nevar renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// maršruti un citas lietas...

Flight::start();
```

### Latte

Ja jums ir Latte instalēts jūsu projektā, jūs varat izmantot Latte paneli, lai analizētu savus veidnes. Jūs varat nodot Latte instanci `TracyExtensionLoader` konstruktorā, izmantojot atslēgu `latte` otrajā parametrā.

```php

lietot Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], funkcija($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// šeit jūs pievienojat Latte Paneli Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Tas jābūt nepatiesam, vai Tracy patiešām nevar renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}