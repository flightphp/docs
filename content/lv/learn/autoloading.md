# Automātiska ielāde

Automātiskā ielāde ir koncepts PHP, kurā norādat direktoriju vai direktorijas, no kurām jāielādē klases. Tas ir daudz izdevīgāk nekā izmantot `require` vai `include`, lai ielādētu klases. Tas ir arī prasība, lai izmantotu Composer paketes.

Pēc noklusējuma jebkura `Flight` klase tiek automātiski ielādēta pateicoties komponistam. Tomēr, ja vēlaties automātiski ielādēt savas klases, varat izmantot `Flight::path` metodi, lai norādītu direktoriju, no kuras jāielādē klases.

## Pamata piemērs

Aplūkojam, ka mums ir direktoriju koks tāds kā zemāk:

```text
# Piemēra ceļš
/home/lietotājs/projekts/mans-flight-projekts/
├── app
│   ├── kešatmiņa
│   ├── konfigurācija
│   ├── vadītāji - satur šī projekta vadītājus
│   ├── tulkojumi
│   ├── UTILS - satur klases tikai šim lietojumprogrammai (tas ir ar lielajiem burtiem apzināti, kā piemēramā vēlāk)
│   └── skati
└── publisks
    └── css
	└── js
	└── index.php
```

Varbūt pievērsāties uzmanību, ka tas ir vienādas failu struktūras kā šī dokumentācijas vietne.

Jūs varat norādīt katru direktoriju, no kuras jāielādē, šādi:

```php

/**
 * publisks/index.php
 */

// Pievienot ceļu automātiskajam ielādētājam
Flight::path(__DIR__.'/../app/vadītāji/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/vadītāji/MyController.php
 */

// nav obligāta nosaukumu telpošana

// Visas automātiski ielādētās klases tiek ieteiktas būt Pascal Case (katrs vārds lielajiem burtiem, bez atstarpēm)
// Ir prasība, ka jūs nevarat izmantot pasvītrojumus savās klases nosaukumā
class MyController {

	public function index() {
		// kaut ko
	}
}
```

## Nosaukumtelpas

Ja jums ir nosaukumtelpas, ir ļoti viegli to ieviest. Jums vajadzētu izmantot `Flight::path()` metodi, lai norādītu aplikācijas saknes direktoriju (nevis dokumentu sakni vai `publisks/` mapes).

```php

/**
 * publisks/index.php
 */

// Pievienot ceļu automātiskajam ielādētājam
Flight::path(__DIR__.'/../');
```

Tagad šāda izskatās jūsu vadītāja varētu izskatīties. Apskatiet zemāk esošo piemēru, bet pievērsiet uzmanību komentāriem, lai iegūtu svarīgu informāciju.

```php
/**
 * app/vadītāji/MyController.php
 */

// nosaukumtelpas ir obligātas
// nosaukumtelpas ir vienādas ar direktoriju struktūru
// nosaukumtelpām jāseko tādai pašai lietotnei kā direktoriju struktūrai
// nosaukumtelpās un direktorijās nevar būt nekādu pasvītrojumu
namespace app\vadītāji;

// Visas automātiski ielādētās klases tiek ieteiktas būt Pascal Case (katrs vārds lielajiem burtiem, bez atstarpēm)
// Ir prasība, ka jūs nevarat izmantot pasvītrojumus savās klases nosaukumā
class MyController {

	public function index() {
		// kaut ko
	}
}
```

Un ja vēlētos automātiski ielādēt klasi savā UTILS direktorijā, jūs darītu būtiski to pašu:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// nosaukumtelpai jāsakrīt ar direktoriju struktūru un režīmu (Ņemiet vērā, ka UTILS direktorijā ir visi burti ar lielajiem burtiem
//     kā failu koks augšā)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// kaut ko
	}
}
```