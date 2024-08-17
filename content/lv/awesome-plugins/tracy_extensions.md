# Tracy Flight Panel Extensions
=====

Tas ir komplekts paplašinājumu, kas padara darbu ar Flight nedaudz bagātāku.

- Flight - Analizēt visus Flight mainīgos.
- Database - Analizēt visus pieprasījumus, kas ir izpildīti lapā (ja pareizi inicializējat datu bāzes savienojumu)
- Request - Analizēt visus `$_SERVER` mainīgos un izpētīt visus globālos dati (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analizēt visus `$_SESSION` mainīgos, ja sesijas ir aktīvas.

Šī ir panelis

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs panelis rāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Noklikšķiniet [šeit](https://github.com/flightphp/tracy-extensions), lai skatītu kodu.

## Instalācija
-------
Izpildiet `composer require flightphp/tracy-extensions --dev` un jūs esat gatavs!

Konfigurācija
-------
Lai sāktu izmantot šo, jums ir nepieciešama ļoti maza konfigurācija. Jums būs jāinicializē Tracy atkļūdošana pirms šī sākuma [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// palaižamā kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Varat būt nepieciešams norādīt savu vidi, izmantojot Debugger::enable(Debugger::DEVELOPMENT)

// ja lietojat datu bāzes savienojumus savā lietojumprogrammā, ir
// nepieciešams PDO iesaiņotājs LIETOŠANAI TIKAI IZSTRĀDEI (lūdzu, neizmantojiet ražošanā!)
// Tā ir tāda pati parametru kopums kā parasts PDO savienojums
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// vai ja to pievienojat Flight struktūrai
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// tagad, kad veicat vaicājumu, tas saglabās laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	// Tas jābūt viltus, vai arī Tracy faktiski nevar atveidot :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// vairāk koda

Flight::start();
```

## Papildu konfigurācija

### Sesiju dati
Ja jums ir pielāgots sesiju apstrādātājs (piemēram, ghostff/session), jūs varat nodot jebkuru sesiju datu masīvu Tracy, un tas automātiski to izvadīs jums. Jūs to nododat ar `session_data` atslēgu `TracyExtensionLoader` konstruktorā otro parametru.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Tas jābūt viltus, vai arī Tracy faktiski nevar atveidot :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// maršruti un citi lielumi...

Flight::start();
```

### Latte

Ja jums ir Latte instalēts jūsu projektā, jūs varat izmantot Latte paneli, lai analizētu savus veidnes. Jūs varat nodot Latte instanci `TracyExtensionLoader` konstruktorā ar atslēgu `latte` otro parametru.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// šeit jūs pievienojat Latte Paneļu Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Tas jābūt viltus, vai arī Tracy faktiski nevar atveidot :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
