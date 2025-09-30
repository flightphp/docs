# Autoloading

## Pārskats

Autoloading ir PHP koncepts, kurā jūs norādāt direktoriju vai direktorijus, no kuriem ielādēt klases. Tas ir daudz izdevīgāk nekā izmantot `require` vai `include` klašu ielādei. Tas ir arī prasība Composer pakotņu izmantošanai.

## Saprašana

Pēc noklusējuma jebkura `Flight` klase tiek automātiski autoloadēta jums, pateicoties composer. Tomēr, ja vēlaties autoloadēt savas klases, jūs varat izmantot `Flight::path()` metodi, lai norādītu direktoriju, no kura ielādēt klases.

Autoloadera izmantošana var palīdzēt ievērojami vienkāršot jūsu kodu. Tā vietā, lai faili sāktos ar daudziem `include` vai `require` paziņojumiem augšpusē, lai uztvertu visas tajā failā izmantotās klases, jūs varat dinamiski izsaukt savas klases, un tās tiks iekļautas automātiski.

## Pamatlietojums

Pieņemsim, ka mums ir direktoriju koks šāds:

```text
# Piemēra ceļš
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - satur šī projekta kontrolierus
│   ├── translations
│   ├── UTILS - satur klases tikai šai lietojumprogrammai (tas ir visi lielie burti mērķtiecīgi piemēram vēlāk)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Jūs varbūt pamanījāt, ka tas ir tāds pats failu struktūra kā šī dokumentācijas vietnei.

Jūs varat norādīt katru direktoriju, no kura ielādēt, šādi:

```php

/**
 * public/index.php
 */

// Pievienojiet ceļu autoloaderim
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// nav nepieciešama vārdu telpa

// Visām autoloadētajām klasēm ieteicams būt Pascal Case (katrs vārds ar lielu burtu, bez atstarpēm)
class MyController {

	public function index() {
		// dariet kaut ko
	}
}
```

## Vārdu telpas

Ja jums ir vārdu telpas, tas faktiski kļūst ļoti viegli ieviest. Jums vajadzētu izmantot `Flight::path()` metodi, lai norādītu jūsu lietojumprogrammas saknes direktoriju (nevis dokumenta sakni vai `public/` mapi).

```php

/**
 * public/index.php
 */

// Pievienojiet ceļu autoloaderim
Flight::path(__DIR__.'/../');
```

Tagad tas ir jūsu kontrolieris izskatītos. Skatiet piemēru zemāk, bet pievērsiet uzmanību komentāriem svarīgai informācijai.

```php
/**
 * app/controllers/MyController.php
 */

// vārdu telpas ir nepieciešamas
// vārdu telpas ir tādas pašas kā direktoriju struktūra
// vārdu telpām jāatbilst tāpat kā direktoriju struktūrai
// vārdu telpām un direktorijiem nevar būt apakšsvītras (ja vien nav iestatīts Loader::setV2ClassLoading(false))
namespace app\controllers;

// Visām autoloadētajām klasēm ieteicams būt Pascal Case (katrs vārds ar lielu burtu, bez atstarpēm)
// No 3.7.2 versijas, jūs varat izmantot Pascal_Snake_Case savām klases nosaukumiem, palaižot Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// dariet kaut ko
	}
}
```

Un ja jūs vēlaties autoloadēt klasi jūsu utils direktorijā, jūs darītu gandrīz to pašu:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// vārdu telpai jāatbilst direktoriju struktūrai un reģistram (pamaniet, ka UTILS direktorija ir visi lielie burti
//     kā failu kokā augstāk)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// dariet kaut ko
	}
}
```

## Apakšsvītras klases nosaukumos

No 3.7.2 versijas, jūs varat izmantot Pascal_Snake_Case savām klases nosaukumiem, palaižot `Loader::setV2ClassLoading(false);`. 
Tas ļaus izmantot apakšsvītras jūsu klases nosaukumos. 
Tas nav ieteicams, bet tas ir pieejams tiem, kam tas ir nepieciešams.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Pievienojiet ceļu autoloaderim
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// nav nepieciešama vārdu telpa

class My_Controller {

	public function index() {
		// dariet kaut ko
	}
}
```

## Skatīt arī
- [Routing](/learn/routing) - Kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Why a Framework?](/learn/why-frameworks) - Saprast ieguvumus, izmantojot ietvaru kā Flight.

## Traucējummeklēšana
- Ja jūs nevarat saprast, kāpēc jūsu vārdu telpās esošās klases netiek atrastas, atcerieties izmantot `Flight::path()` uz jūsu projekta saknes direktoriju, nevis jūsu `app/` vai `src/` direktoriju vai ekvivalentu.

### Klase netika atrasta (autoloading nedarbojas)

Šim var būt pāris iemesli. Zemāk ir daži piemēri, bet pārliecinieties, ka jūs arī apskatāt [autoloading](/learn/autoloading) sadaļu.

#### Nepareizs faila nosaukums
Visbiežākais ir tas, ka klases nosaukums neatbilst faila nosaukumam.

Ja jums ir klase ar nosaukumu `MyClass`, tad failam jābūt nosauktam `MyClass.php`. Ja jums ir klase ar nosaukumu `MyClass` un fails ir nosaukts `myclass.php` 
tad autoloader nevarēs to atrast.

#### Nepareiza vārdu telpa
Ja jūs izmantojat vārdu telpas, tad vārdu telpai jāatbilst direktoriju struktūrai.

```php
// ...kods...

// ja jūsu MyController ir app/controllers direktorijā un tas ir ar vārdu telpu
// tas nedarbosies.
Flight::route('/hello', 'MyController->hello');

// jums jāizvēlas viena no šīm opcijām
Flight::route('/hello', 'app\controllers\MyController->hello');
// vai ja jums ir use paziņojums augšpusē

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// var arī rakstīt
Flight::route('/hello', MyController::class.'->hello');
// arī...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` nav definēts

Skeletā lietojumprogrammā tas ir definēts `config.php` failā, bet lai jūsu klases tiktu atrastas, jums jāpārliecinās, ka `path()`
metode ir definēta (droši vien uz jūsu direktorijas sakni) pirms mēģināt to izmantot.

```php
// Pievienojiet ceļu autoloaderim
Flight::path(__DIR__.'/../');
```

## Izmaiņu žurnāls
- v3.7.2 - Jūs varat izmantot Pascal_Snake_Case savām klases nosaukumiem, palaižot `Loader::setV2ClassLoading(false);`
- v2.0 - Autoload funkcionalitāte pievienota.