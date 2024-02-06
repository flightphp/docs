Tracy Flight Panel Extensions
=====

Šī ir komplekta paplašinājumi, lai darbs ar Flight būtu nedaudz bagātīgāks.

- Flight - Analizēt visas Flight mainīgās.
- Database - Analizēt visas vaicājumus, kas ir izpildīti uz lapas (ja pareizi iniciējat datu datubāzes savienojumu)
- Request - Analizēt visas `$_SERVER` mainīgās un izpētīt visus globālos dati (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analizēt visas `$_SESSION` mainīgās, ja sesijas ir aktīvas.

Tas ir panelis

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs panelis parāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalēšana
-------
Izpildiet `composer require flightphp/tracy-extensions --dev` un jūs esat startā!

Konfigurācija
-------
Ir ļoti maz konfigurācijas, ko jums jāveic, lai sāktu darbu ar to. Jums būs jāiniciē Tracy atkļūdošanas rīkotājs pirms šī izmantošanas [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// ielādes kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Varbūt jums būs jānorāda sava vide, izmantojot Debugger::enable(Debugger::DEVELOPMENT)

// ja lietojat datubāzes savienojumus savā lietojumprogrammā, ir 
// nepieciešams PDO apvalks, ko LIETOJIE TIKAI IZSTRĀDĀJUMOS (lūdzu, neizmantojiet ražošanā!)
// Tam ir tādas pašas parametrs kā parasta PDO savienojumam
$pdo = new PdoQueryCapture('sqlite:test.db', 'lietotājvārds', 'parole');
// vai, ja pievienojat to Flight ietvara
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'lietotājvārds', 'parole']);
// tagad, kad veicat vaicājumu, tas saglabās laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// vairāk koda

Flight::start();
```