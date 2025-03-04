# Automātiska ielāde

Automātiska ielāde ir koncepcija PHP, kurā norādāt direktoriju vai direktorijas, no kurienes ielādēt klases. Tas ir daudz noderīgāks nekā izmantojot `require` vai `include`, lai ielādētu klases. Tas ir arī priekšnoteikums, lai izmantotu komponista pakotnes.

Pēc noklusējuma jebkura `Flight` klase tiek automātiski ielādēta jūsu dēļ pateicoties komponistam. Tomēr, ja vēlaties ielādēt savas klases, varat izmantot `Flight::path()` metodi, lai norādītu direktoriju, no kuras ielādēt klases.

## Pamata piemērs

Pieņemsim, ka mums ir direktoriju koks līdzīgs sekojošajam:

```text
# Piemēra ceļš
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - satur šī projekta kontrolierus
│   ├── translations
│   ├── UTILS - satur klases tikai šai lietojumprogrammai (tas ir ar lielajiem burtiem nolūka labad piemēram vēlāk)
│   └── views
└── public
    └── css
    └── js
    └── index.php
```

Jūs, iespējams, esat pamanījis, ka šis ir tas pats failu struktūra kā šī dokumentācijas vietne.

Katru direktoriju varat norādīt ielādēšanai šādi:

```php

/**
 * public/index.php
 */

// Pievienot ceļu autovadītājam
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// bez nosaukumu telpājums nepieciešams

// Visas automātiski ielādētās klases tiek ieteiktas būt Pascal Case (katrs vārds lielajiem burtiem, bez atstarpēm)
// Kopš 3.7.2 versijas, jūs varat izmantot Pascal_Snake_Case savām klases nosaukumiem, palaistot Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// darīt kaut ko
	}
}
```

## Telpvārdi (Namespaces)

Ja jums ir telpvārdi, tas patiešām kļūst ļoti viegli īstenot to. Jums vajadzētu izmantot `Flight::path()` metodi, lai norādītu saknes direktoriju (nevis dokumenta sakne vai `public/` mape) jūsu lietojumprogrammai.

```php

/**
 * public/index.php
 */

// Pievienot ceļu autovadītājam
Flight::path(__DIR__.'/../');
```

Tagad šis būtu jūsu kontroliera izskats. Apskatiet tālāk esošo piemēru, bet pievērsiet uzmanību komentāriem svarīgai informācijai.

```php
/**
 * app/controllers/MyController.php
 */

// telpvārdiem nepieciešami
// telpvārdi ir analogi direktoriju struktūrai
// telpvārdiem jāievēro tāda pati lietu struktūra kā direktorijām
// telpvārdi un direktorijas nevar saturēt nekādus pasvītras zīmes (ja nav iestatīts Loader::setV2ClassLoading(false))
namespace app\controllers;

// Visas automātiski ielādētās klases tiek ieteiktas būt Pascal Case (katrs vārds lielajiem burtiem, bez atstarpēm)
// Kopš 3.7.2 versijas, jūs varat izmantot Pascal_Snake_Case savām klases nosaukumiem, palaistot Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// darīt kaut ko
	}
}
```

Un ja vēlētos automātiski ielādēt klasi jūsu utils direktorijā, varētu darīt būtiski to pašu:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// telpvārds jāsakrīt ar direktoriju struktūru un gadījumu (pievērsiet uzmanību UTILS direktorijai, kas ir visas lielas burti
//     kā failu koks iepriekš)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// darīt kaut ko
	}
}
```

## Pasvītras zīmes klases nosaukumos

Kopš 3.7.2 versijas, jūs varat izmantot Pascal_Snake_Case savām klases nosaukumiem, palaistot `Loader::setV2ClassLoading(false);`.
Tas ļaus jums izmantot pasvītras zīmes savos klases nosaukumos.
Tas nav ieteicams, bet tas ir pieejams tiem, kam tas ir nepieciešams.

```php

/**
 * public/index.php
 */

// Pievienot ceļu autovadītājam
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// bez nosaukumu telpājums nepieciešams

class My_Controller {

	public function index() {
		// darīt kaut ko
	}
}
```