# Tracy

Tracy ir brīnišķīgs kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir vairākas panelis, kas var palīdzēt jums atkļūdot jūsu lietojumprogrammu. Tas ir arī ļoti viegli paplašināms un pievienot savus paneļus. Flight komanda ir izveidojusi dažus paneļus speciāli Flight projektiem ar [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) spraudni.

## Instalēšana

Instalējiet ar komponistu. Un jūs faktiski vēlēsities instalēt to bez izstrādes versijas, jo Tracy nāk ar ražošanas kļūdu apstrādes komponentu.

```bash
composer require tracy/tracy
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas opcijas, lai sāktu. Jūs varat lasīt vairāk par tām [Tracy dokumentācijā](https://tracy.nette.org/en/configuring).

```php
require 'vendor/autoload.php';

use Tracy\Debugger;

// Iespējot Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // dažreiz jums ir jābūt skaidram (arī Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // jūs varat arī norādīt IP adresu masīvu

// Šeit tiks reģistrētas kļūdas un izņēmumi. Pārliecinieties, ka šis katalogs eksistē un ir rakstāms.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // rādīt visas kļūdas
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // visas kļūdas, izņemot novecojušos paziņojumus
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // ja Debugger josla ir redzama, tad saturs garums nevar tikt iestatīts ar Flight

	// Tas ir specifisks Tracy Extension for Flight, ja esat to iekļāvis
	// citādi izkomentējiet to.
	new TracyExtensionLoader($app);
}
```

## Lietderīgi padomi

Kad jūs atkļūvojat savu kodu, ir dažas ļoti noderīgas funkcijas, lai izvadītu datus jums.

- `bdump($var)` - Tas iznāksīs mainīgo uz Tracy Bar atsevišķā panelī.
- `dumpe($var)` - Tas iznāksīs mainīgo un tad nomirs uzreiz.