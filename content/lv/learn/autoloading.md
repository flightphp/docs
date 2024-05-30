# Automašīnīšana

Automašīnīšana ir koncepts PHP valodā, kur norāda direktoriju vai direktorijas, no kurienes ielādēt klases. Tas ir daudz izdevīgāk nekā izmantojot `require` vai `include`, lai ielādētu klases. Tas arī ir prasība, lai izmantotu Composer pakotnes.

Pēc noklusējuma jebkura `Flight` klase tiek automātiski ielādēta pateicībā composer. Taču, ja vēlaties ielādēt savas klases automātiski, varat izmantot `Flight::path` metodi, lai norādītu direktoriju, no kuras ielādēt klases.

## Pamata piemērs

Pieņemsim, ka mums ir direktoriju koks kā tas sekojoši:

```text
# Piemēra ceļš
/mājas/lietotājs/projekts/mans-flight-projekts/
├── lietotne
│   ├── kešatmiņa
│   ├── konfigurācija
│   ├── vadītāji - satur šī projekta vadītājus
│   ├── tulkojumi
│   ├── UTILS - satur klases tikai šai lietojumprogrammai (tas visi lielas burtos nolūkā, kā vēlāk piemēram)
│   └── skati
└── publisks
    └── css
	└── js
	└── index.php
```

Jūs varat norādīt katru no direktorijām, no kurienes ielādēt šādi:

```php

/**
 * public/index.php
 */

// Pievienot ceļu automātiski ielādētājam
Flight::path(__DIR__.'/../lietotne/vadītāji/');
Flight::path(__DIR__.'/../lietotne/utils/');


/**
 * lietotne/vadītāji/ManaisVadītājs.php
 */

// nav vajadzīgs nosaukuma telpojums

// Visas automātiski ielādētās klases ir ieteicams rakstīt ar Pascal Lielajiem burtiem (katrs vārds lietots lielais burtos, bez atstarpēm)
// Sākot ar 3.7.2 versiju, varat izmantot Pascal_Snake_Case saviem klases nosaukumiem, palaižot Loader::setV2ClassLoading(false);
class ManaisVadītājs {

	public function index() {
		// dari kaut ko
	}
}
```

## Nosaukumu telpas

Ja jums ir nosaukumu telpas, patiesībā tas kļūst ļoti viegli ieviest. Jums vajadzētu izmantot `Flight::path()` metodi, lai norādītu saknes direktoriju (nevis dokumenta saknes vai `public/` mapes) jūsu lietojumprogrammai.

```php

/**
 * public/index.php
 */

// Pievienot ceļu automātiski ielādētājam
Flight::path(__DIR__.'/../');
```

Tagad šis ir kā varētu izskatīties jūsu vadītājs. Apskatiet zemāk esošo piemēru, bet pievērsiet uzmanību komentāriem par svarīgu informāciju.

```php
/**
 * lietotne/vadītāji/ManaisVadītājs.php
 */

// nosaukumu telpas ir prasītas
// nosaukumu telpas ir tādas pašas kā direktoriju struktūra
// nosaukumu telpu ir jāievēro tāda pati lietu struktūra
// nosaukumu telpām un direktorijām nedrīkst būt nekādu apakšsvītru (ja nav iestatīts Loader::setV2ClassLoading(false))
namespace app\vadītāji;

// Visas automātiski ielādētās klases ir ieteicams rakstīt ar Pascal Lielajiem burtiem (katrs vārds lietots lielais burtos, bez atstarpēm)
// Sākot ar 3.7.2 versiju, varat izmantot Pascal_Snake_Case saviem klases nosaukumiem, palaižot Loader::setV2ClassLoading(false);
class ManaisVadītājs {

	public function index() {
		// dari kaut ko
	}
}
```

Un ja vēlējāties automātiski ielādēt klasi savā UTILS direktorijā, jūs darītu būtiski to pašu:

```php

/**
 * lietotne/UTILS/ArrayHelperUtil.php
 */

// nosaukuma telpai jāsaskan ar direktorijas struktūru un gadījumu (ņemot vērā UTILS direktoriju ir visas lielās burts
//     kā failu koks augstāk)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// dari kaut ko
	}
}
```

## Pasvītras klases nosaukumos

Sākot ar 3.7.2 versiju, jūs varat izmantot Pascal_Snake_Case saviem klases nosaukumiem, palaižot `Loader::setV2ClassLoading(false);`. Tas ļaus jums izmantot pasvītras savos klases nosaukumos. Tas nav ieteicams, bet tas ir pieejams tiem, kuriem tas ir nepieciešams.

```php

/**
 * public/index.php
 */

// Pievienot ceļu automātiski ielādētājam
Flight::path(__DIR__.'/../lietotne/vadītāji/');
Flight::path(__DIR__.'/../lietotne/utils/');
Loader::setV2ClassLoading(false);

/**
 * apdraudējumi/Manais_Apdraudējums.php
 */

// nav vajadzīgs nosaukuma telpojums

class Manais_Apdraudējums {

	public function index() {
		// dari kaut ko
	}
}
```