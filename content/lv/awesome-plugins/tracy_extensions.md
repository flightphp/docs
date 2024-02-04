Tracy Flight Panel Extensions
=====

Tas ir kopums ar paplašinājumiem, kas padara darbu ar Flight nedaudz bagātīgāku.

- Flight - Analizēt visus Flight mainīgos.
- Datubāze - Analizēt visus vaicājumus, kas ir izpildīti lapā (ja pareizi inicializējat datubāzes savienojumu)
- Pieprasījums - Analizēt visus `$_SERVER` mainīgos un pārbaudīt visus globālos ielādes ( `$_GET`, `$_POST`, `$_FILES`)
- Sesija - Analizēt visus `$_SESSION` mainīgos, ja sesijas ir aktīvas.

Šis ir Panelis

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Un katrs paneļa rāda ļoti noderīgu informāciju par jūsu lietojumprogrammu!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Uzstādīšana
-------
Palaidiet `composer require flightphp/tracy-extensions --dev` un jūs esat ceļā!

Konfigurācija
-------
Ir ļoti maz konfigurācijas, ko jums jāveic, lai sāktu darbu ar to. Jums būs jāinicializē Tracy atkļūdotājs pirms izmantojiet šo [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap kods
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Varat būt nepieciešams norādīt savu vidi ar Debugger::enable(Debugger::DEVELOPMENT)

// ja jūs lietojat datubāzes savienojumus savā lietotnē, ir
// obligātais PDO apvalks, ko lietot TIKAI IZSTRĀDĀJUMOS (lūdzu, neizmantojiet produkcijā!)
// Tas ir tāds pats parametrs kā parasts PDO savienojums
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// vai ja pielipojat to pie Flight ietvariem
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// tagad, kad izpildāt vaicājumu, tas fiksēs laiku, vaicājumu un parametrus

// Tas savieno punktus
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// vairāk koda

Flight::start();
```  