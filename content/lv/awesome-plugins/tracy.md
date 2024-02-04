# Tracy

Tracy ir fantastisks kļūdu apstrādātājs, kas var tikt izmantots ar Flight. Tam ir vairākas panelis, kas var palīdzēt atkļūdot jūsu lietotni. Tam arī ir ļoti viegli paplašināt un pievienot savus paneļus. Flight komanda ir izveidojusi dažus paneļus speciāli Flight projektam ar [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) spraudni.

## Instalācija

Instalējiet ar komponistu. Un jūs patiešām vēlēsieties instalēt to bez izstrādes versijas, jo Tracy nāk ar ražošanas kļūdu apstrādes komponentu.

```bash
composer require tracy/tracy
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas opcijas, lai sāktu. Uzziniet vairāk par tām [Tracy dokumentācijā](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Iespējot Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // dažreiz jums ir jābūt specifiskam (arī Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // jūs varat arī nodot IP adreses masīvu

// Šeit tiks reģistrētas kļūdas un izņēmumi. Pārliecinieties, vai šis katalogs pastāv un tam ir rakstīšanas tiesības.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // rādīt visas kļūdas
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // visas kļūdas izņemot novecojušos paziņojumus
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // ja Tracy josla ir redzama, tad Flight nevar iestatīt satura garumu

	// Tas ir specifisks Tracy paplašinājumam Flight, ja esat to iekļāvis
	// citādi izkomentējiet to.
	new TracyExtensionLoader($app);
}
```

## Noderīgi padomi

Kad jūs atkļūkojat savu kodu, ir dažas ļoti noderīgas funkcijas, lai izvadītu datus jums.

- `bdump($var)` - Tas izvadīs mainīgo Tracy joslā atsevišķā panelī.
- `dumpe($var)` - Tas izvadīs mainīgo un tad nekavējoties nomirs.