# Tracy Flight Panel Extensions
=====

Šis ir paplašinājumu kopums, lai darbs ar Flight būtu nedaudz bagātīgāks.

- Flight - Analizēt visus Flight mainīgos.
- Database - Analizēt visus vaicājumus, kas ir izpildīti lapā (ja pareizi iniciējat datubāzes savienojumu)
- Request - Analizēt visus `$_SERVER` mainīgos un pārbaudīt visus globālos dati (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analizēt visus `$_SESSION` mainīgos, ja sesijas ir aktīvas.

Šis ir paneļa

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs paneļa parāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalēšana
-------
Palaidiet `composer require flightphp/tracy-extensions --dev` un jūs esat gatavs!

Konfigurācija
-------
Jums ir jāveic ļoti maz konfigurācijas, lai sāktu darbu ar to. Jums būs jāiniciē Traci dubugeri pirms šī lietojuma izmantošanas [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// palaišanas kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Jums var būt nepieciešams norādīt savu vidi ar Debugger::enable(Debugger::DEVELOPMENT)

// ja jūs lietojat datubāzes savienojumus savā lietojumprogrammā, ir 
// nepieciešams PDO apvalks lietošanai TIKAI IZSTRĀDĒ (lūdzu, neizmantojiet produkcijā!)
// Tas ir ar pašiem parametriem kā parasts PDO savienojums
$pdo = new PdoQueryCapture('sqlite:test.db', 'lietotājs', 'parole');
// vai, ja pievienojat to Flight ietvarā
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'lietotājs', 'parole']);
// tagad katru reizi, kad veicat vaicājumu, tas fiksēs laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	// Tas ir jābūt false, lai Tracy faktiski varētu renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// vairāk koda

Flight::start();
```

## Papildu konfigurācija

### Sesiju dati
Ja jums ir pielāgots sesiju apstrādātājs (piemēram, ghostff/session), jūs varat padot jebkuru sesiju datu masīvu Tracy, un tas automātiski to izvadīs jums. Jūs to padodat ar `session_data` atslēgu `TracyExtensionLoader` konstruktorā otro parametru.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Tas ir jābūt false, lai Tracy faktiski varētu renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// maršruti un citi...

Flight::start();
```

### Latte

Ja jums ir Latte instalēts jūsu projektā, jūs varat izmantot Latte paneļa, lai analizētu savus veidnes. Jūs varat padot Latte instanci `TracyExtensionLoader` konstruktorā ar `latte` atslēgu otrajā parametrā.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// šeit jūs pievienojat Latte Panel pie Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Tas ir jābūt false, lai Tracy faktiski varētu renderēt :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
