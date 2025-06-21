Tracy Flight Paneļu Paplašinājumi
=====

Šis ir paplašinājumu kopums, lai darbs ar Flight būtu nedaudz bagātāks.

- Flight - Analizēt visas Flight mainīgās.
- Datubāze - Analizēt visus vaicājumus, kas ir izpildīti lapā (ja pareizi tiek uzsākta datubāzes savienojuma ierosināšana)
- Pieprasījums - Analizēt visas `$_SERVER` mainīgās un pārbaudīt visus globālos slodzes datus (`$_GET`, `$_POST`, `$_FILES`)
- Sesija - Analizēt visas `$_SESSION` mainīgās, ja sesijas ir aktīvas.

Šis ir Paneelis

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs panelis parāda ļoti noderīgu informāciju par jūsu lietotni!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Noklikšķiniet [šeit](https://github.com/flightphp/tracy-extensions), lai apskatītu kodu.

Instalēšana
-------
Izpildiet `composer require flightphp/tracy-extensions --dev` un jūs esat ceļā!

Konfigurācija
-------
Konfigurācijai, kas jums jāveic, lai sāktu darbu, ir ļoti maz. Jums būs jāuzsāk Tracy atkļūdotājs pirms šī izmantošanas [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// sāknēšanas kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Jūs varat norādīt savu vidi ar Debugger::enable(Debugger::DEVELOPMENT)

// ja jūs izmantojat datubāzes savienojumus savā lietotnē, tur ir 
// nepieciešams PDO apvalks, ko izmantot TIKAI ATTĪSTĪBAS VIDĒ (ne produkcijā, lūdzu!)
// Tam ir tādi paši parametri kā regulārai PDO savienojumam
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// vai, ja jūs pievienojat to Flight framework
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// tagad, kad jūs veicat vaicājumu, tas noķers laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	// Šim jābūt nepatiesam, pretējā gadījumā Tracy nevarēs renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// vairāk kods

Flight::start();
```

## Papildu Konfigurācija

### Sesijas Dati
Ja jums ir pielāgots sesijas apdarinātājs (piemēram, ghostff/session), jūs varat nodot jebkuru sesijas datu masīvu uz Tracy, un tas automātiski izvadīs to jums. Jūs nododat to ar `session_data` atslēgu otrajā parametrā uz `TracyExtensionLoader` konstruktoru.

```php

use Ghostff\Session\Session;
// vai izmantojiet flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Šim jābūt nepatiesam, pretējā gadījumā Tracy nevarēs renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// maršruti un citas lietas...

Flight::start();
```

### Latte

Ja jums ir instalēts Latte jūsu projektā, jūs varat izmantot Latte paneli, lai analizētu savus veidnes. Jūs varat nodot Latte instanci uz `TracyExtensionLoader` konstruktoru ar `latte` atslēgu otrajā parametrā.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// šeit jūs pievienojat Latte Paneli uz Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Šim jābūt nepatiesam, pretējā gadījumā Tracy nevarēs renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```