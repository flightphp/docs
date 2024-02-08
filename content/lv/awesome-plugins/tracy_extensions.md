Tracy Flight Panel Extensions
=====

Šis ir paplašinājumu komplekts, kas padara darbu ar Flight nedaudz bagātīgāku.

- Lidojums - Analizēt visas Lidojuma mainīgās.
- Datu bāze - Analizēt visas pieprasījumus, kas ir aizskrējuši lapā (ja pareizi iniciējat datu bāzes savienojumu).
- Pieprasījums - Analizēt visas `$_SERVER` mainīgās un izpētīt visas globālās payloads (`$_GET`, `$_POST`, `$_FILES`).
- Sesija - Analizēt visas `$_SESSION` mainīgās, ja sesijas ir aktīvas.

Šis ir panelis

![Lidojuma josla](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs panelis parāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Lidojuma dati](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Lidojuma datu bāze](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Lidojuma pieprasījums](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalācija
-------
Izpildiet `composer require flightphp/tracy-extensions --dev` un jūs esat ceļā!

Konfigurācija
-------
Jums ir ļoti maz konfigurācijas, ko jums jādara, lai sāktu darbu ar to. Jums būs jāiniti Tracy atkļūdētājs pirms šī lietojuma lietošanas [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// izstrādes kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Jums var būt jānorāda savs vides režīms ar Debugger::enable(Debugger::DEVELOPMENT)

// ja lietojat datu bāzes savienojumus savā lietojumprogrammā, ir
// nepieciešams PDO appķērs, ko izmantot TIKAI IZSTRĀDEI (lūdzu, neizmantojiet ražošanā!)
// Tam ir tie paši parametri kā parastam PDO savienojumam
$pdo = new PdoQueryCapture('sqlite:test.db', 'lietotājs', 'parole');
// vai, ja piesaistāt šo Flight ietvaru
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'lietotājs', 'parole']);
// tagad, kad veicat vaicājumu, tas uztverēs laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// vairāk koda

Flight::start();
```