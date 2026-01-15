# Autoloading

## Pārskats

Autoloading ir PHP koncepts, kurā jūs norādāt direktoriju vai direktorijas, no kurām ielādēt klases. Tas ir daudz izdevīgāk nekā izmantot `require` vai `include` klases ielādei. Tas ir arī prasība Composer pakotņu izmantošanai.

## Saprašana

Pēc noklusējuma jebkura `Flight` klase tiek automātiski autoloadēta jums, pateicoties composer. Tomēr, ja vēlaties autoloadēt savas klases, jūs varat izmantot `Flight::path()` metodi, lai norādītu direktoriju, no kura ielādēt klases.

Autoloadera izmantošana var palīdzēt ievērojami vienkāršot jūsu kodu. Tā vietā, lai faili sāktos ar daudziem `include` vai `require` paziņojumiem augšpusē, lai uztvertu visas klases, kas tiek izmantotas tajā failā, jūs varat dinamiski izsaukt savas klases, un tās tiks iekļautas automātiski.

## Pamata izmantošana

Pieņemsim, ka mums ir direktoriju koka struktūra šāda:

```text
# Piemēra ceļš
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - satur šī projekta kontrolierus
│   ├── translations
│   ├── UTILS - satur klases tikai šai lietojumprogrammai (tas ir visiem lielajiem burtiem mērķtiecīgi piemēram vēlāk)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Jūs varbūt pamanījāt, ka tā ir tā pati failu struktūra kā šai dokumentācijas vietnei.

Jūs varat norādīt katru direktoriju ielādei šādi:

```php

/**
 * public/index.php
 */

// Pievienot ceļu autoloaderam
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// nav nepieciešama namespacing

// Visām autoloadētajām klasēm iesaka būt Pascal Case (katrs vārds ar lielajiem burtiem, bez atstarpēm)
class MyController {

	public function index() {
		// izdarīt kaut ko
	}
}
```

## Namespaces

Ja jums ir namespaces, tas patiesībā kļūst ļoti viegli īstenojams. Jums vajadzētu izmantot `Flight::path()` metodi, lai norādītu jūsu lietojumprogrammas saknes direktoriju (nevis dokumenta sakni vai `public/` mapi).

```php

/**
 * public/index.php
 */

// Pievienot ceļu autoloaderam
Flight::path(__DIR__.'/../');
```

Tagad tā izskatās jūsu kontrolieris. Skatiet piemēru zemāk, bet pievērsiet uzmanību komentāriem svarīgai informācijai.

```php
/**
 * app/controllers/MyController.php
 */

// namespaces ir nepieciešami
// namespaces ir tādi paši kā direktoriju struktūra
// namespaces jāatbilst tā paša reģistra kā direktoriju struktūrai
// namespaces un direktorijām nevar būt apakšsvītras (ja vien nav iestatīts Loader::setV2ClassLoading(false))
namespace app\controllers;

// Visām autoloadētajām klasēm iesaka būt Pascal Case (katrs vārds ar lielajiem burtiem, bez atstarpēm)
// No 3.7.2 versijas jūs varat izmantot Pascal_Snake_Case saviem klases nosaukumiem, izpildot Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// izdarīt kaut ko
	}
}
```

Un, ja jūs vēlaties autoloadēt klasi jūsu utils direktorijā, jūs darītu gandrīz to pašu:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// namespace jāatbilst direktoriju struktūrai un reģistram (pamaniet, ka UTILS direktorija ir visi lielie burti
//     kā failu kokā augstāk)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// izdarīt kaut ko
	}
}
```

## Apakšsvītras klases nosaukumos

No 3.7.2 versijas jūs varat izmantot Pascal_Snake_Case saviem klases nosaukumiem, izpildot `Loader::setV2ClassLoading(false);`. 
Tas ļaus jums izmantot apakšsvītras savos klases nosaukumos. 
Tas nav ieteicams, bet tas ir pieejams tiem, kam tas ir nepieciešams.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Pievienot ceļu autoloaderam
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// nav nepieciešama namespacing

class My_Controller {

	public function index() {
		// izdarīt kaut ko
	}
}
```

## Skatīt arī
- [Routing](/learn/routing) - Kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Why a Framework?](/learn/why-frameworks) - Saprastie ietvara kā Flight priekšrocības.

## Traucējummeklēšana
- Ja jūs nevarat saprast, kāpēc jūsu namespaced klases netiek atrastas, atcerieties izmantot `Flight::path()` uz jūsu projekta saknes direktoriju, nevis jūsu `app/` vai `src/` direktoriju vai ekvivalentu.

### Klase nav atrasta (autoloading nedarbojas)

Šim var būt pāris iemesli. Zemāk ir daži piemēri, bet pārliecinieties, ka jūs arī apskatāt [autoloading](/learn/autoloading) sadaļu.

#### Nepareizs faila nosaukums
Visbiežākais ir tas, ka klases nosaukums neatbilst faila nosaukumam.

Ja jums ir klase ar nosaukumu `MyClass`, tad failam jābūt nosauktam `MyClass.php`. Ja jums ir klase ar nosaukumu `MyClass` un fails ir nosaukts `myclass.php` 
tad autoloader nevarēs to atrast.

#### Nepareizs Namespace
Ja jūs izmantojat namespaces, tad namespace jāatbilst direktoriju struktūrai.

```php
// ...code...

// ja jūsu MyController ir app/controllers direktorijā un tas ir namespaced
// tas nedarbosies.
Flight::route('/hello', 'MyController->hello');

// jums jāizvēlas viena no šīm opcijām
Flight::route('/hello', 'app\controllers\MyController->hello');
// vai, ja jums ir use paziņojums augšpusē

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// arī var būt rakstīts
Flight::route('/hello', MyController::class.'->hello');
// arī...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` nav definēts

Skeletā lietojumprogrammā tas ir definēts `config.php` failā, bet, lai jūsu klases tiktu atrastas, jums jāpārliecinās, ka `path()`
metode ir definēta (droši vien uz jūsu direktorijas sakni) pirms mēģināt to izmantot.

```php
// Pievienot ceļu autoloaderam
Flight::path(__DIR__.'/../');
```

## Changelog
- v3.7.2 - Jūs varat izmantot Pascal_Snake_Case saviem klases nosaukumiem, izpildot `Loader::setV2ClassLoading(false);`
- v2.0 - Autoload funkcionalitāte pievienota.