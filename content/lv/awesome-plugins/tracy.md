# Tracy

Tracy ir fantastisks kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir vairākas panelis, kas var palīdzēt jums atkļūdot jūsu lietojumprogrammu. Ir ļoti viegli paplašināt un pievienot savus paneļus. Flight komanda ir izveidojusi dažus paneļus speciāli Flight projektam, izmantojot [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) spraudni.

## Instalācija

Instalējiet ar komponistu. Un jums faktiski vajadzēs instalēt to bez izstrādes versijas, jo Tracy tiek piegādāts ar ražošanas kļūdu apstrādes komponentu.

```bash
composer require tracy/tracy
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas opcijas, lai sāktu. Par tām varat lasīt vairāk [Tracy dokumentācijā](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Iespējot Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // dažreiz ir jābūt skaidrai (arī Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // varat norādīt arī IP adreses masīvu

// Šeit tiks reģistrēti kļūdas un izņēmumi. Pārliecinieties, ka šis katalogs pastāv un ir rakstāms.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // rādīt visas kļūdas
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // visas kļūdas, izņemot novecojušus paziņojumus
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // ja Tracy josla ir redzama, tad Flight nevar iestatīt satura garumu

	// Tas ir specifisks Tracy paplašinājumam Flight, ja jūs to esat iekļāvuši
	// pretējā gadījumā komentējiet to.
	new TracyExtensionLoader($app);
}
```

## Noderīgi padomi

Kad jūs atkļūvojat savu kodu, ir dažas ļoti noderīgas funkcijas, lai izvadītu datus jums.

- `bdump($var)` - Tas izmetīs mainīgo uz Tracy joslas atsevišķajā panelī.
- `dumpe($var)` - Tas izmetīs mainīgo un tad nekavējoties nomirs.