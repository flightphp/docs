# Maršruta starpējais kodols

Flight atbalsta maršrutu un grupu maršrutu starpējo kodolu. Starpējais kodols ir funkcija, kas tiek izpildīta pirms (vai pēc) maršruta atzvana. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes jūsu kodā vai validēt, vai lietotājam ir atļauja piekļūt maršrutam.

## Pamata starpējais kodols

Šeit ir pamata piemērs:

```php
// Ja sniedzat tikai anonīmu funkciju, tā tiks izpildīta pirms maršruta atzvanīšanas. 
// nav "pēc" maršrutu starpējo funkciju, izņemot klases (skatīt zemāk)
Flight::route('/ceļš', function() { echo ' Šeit es esmu!'; })->addMiddleware(function() {
	echo 'Starpējais kodols pirmais!';
});

Flight::start();

// Tas izvadīs "Starpējais kodols pirmais! Šeit es esmu!"
```

Ir daži ļoti svarīgi punkti par starpējo kodolu, par kuriem jums jābūt informētiem, pirms to izmantojat:
- Starpējo funkcijas tiek izpildītas secībā, kā tās tiek pievienotas maršrutam. Izpildes process ir līdzīgs tam, kā [Slim Framework izturas pret to](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Pirmskonsultācijas tiek izpildītas pievienošanas secībā, un Pēckonsultācijas tiek izpildītas apgrieztā secībā.
- Ja jūsu starpējā funkcija atgriež false, visi izpildes procesi tiek apturēti, un tiek izraisīta kļūda 403 Aizliegts. Visticamāk, jūs vēlēsities to apstrādāt eleganti, izmantojot `Flight::redirect()` vai kaut ko līdzīgu.
- Ja jums ir nepieciešami parametri no jūsu maršruta, tie tiks padoti vienā masīvā jūsu starpējāi funkcijai. (`function($params) { ... }` vai `public function before($params) {}`). Iemesls tam ir tāds, ka jūs varat strukturēt savus parametrus grupās un dažādās šajās grupās parametri var parādīties citā secībā, kas izjauktu starpējo funkciju, atsaucoties uz nepareizo parametru. Šādā veidā jūs varat piekļūt tiem pēc nosaukuma nevis pēc pozīcijas.
- Ja ievadīsit tikai starpējās funkcijas nosaukumu, tā automātiski tiks izpildīta, izmantojot [atkarību injekcijas konteineru](dependency-injection-container), un starpējais kodols tiks izpildīts ar nepieciešamajiem parametriem. Ja jums nav reģistrēta atkarību injekcijas konteiners, tā nodos `flight\Engine` gadījumu iekšā `__construct()`.


## Starpējo klases

Starpējais kodols var tikt reģistrēts kā klase arī. Ja jums nepieciešama "pēc" funkcionalitāte, jums **obligāti** jāizmanto klase.

```php
class ManaStarpejaisKlase {
	public function before($params) {
		echo 'Starpējais kodols pirmais!';
	}

	public function after($params) {
		echo 'Starpējais kodols pēdējais!';
	}
}

$ManaStarpejaisKlase = new ManaStarpejaisKlase();
Flight::route('/ceļš', function() { echo ' Šeit es esmu! '; })->addMiddleware($ManaStarpejaisKlase); // arī ->addMiddleware([ $ManaStarpejaisKlase, $ManaStarpejaisKlase2 ]);

Flight::start();

// Tas parādīs "Starpējais kodols pirmais! Šeit es esmu! Starpējais kodols pēdējais!"
```

## Starpējo kļūdu apstrāde

Iedomāsimies, ka jums ir autentifikācijas starpējais kodols, un jūs vēlaties novirzīt lietotāju uz pieteikšanās lapu, ja viņi nav autentificējušies. Jums ir dažas iespējas, ar kurām varat rīkoties:

1. Jūs varat atgriezt false no starpējās funkcijas, un Flight automātiski atgriezīs kļūdu 403 Aizliegts, bet bez pielāgojumiem.
1. Jūs varat novirzīt lietotāju uz pieteikšanās lapu, izmantojot `Flight::redirect()`.
1. Jūs varat izveidot pielāgotu kļūdu starpējā funkcijā un apturēt maršruta izpildi.

### Pamata piemērs

Šeit ir vienkāršs atgriešanas false; piemērs:
```php
class ManaStarpejaisKlase {
	public function before($params) {
		if (isset($_SESSION['lietotajs']) === false) {
			return false;
		}

		// jo tas ir true, viss vienkārši turpinās
	}
}
```

### Novirzīšanas piemērs

Šeit ir piemērs, kā novirzīt lietotāju uz pieteikšanās lapu:
```php
class ManaStarpejaisKlase {
	public function before($params) {
		if (isset($_SESSION['lietotajs']) === false) {
			Flight::redirect('/pieteikties');
			exit;
		}
	}
}
```

### Pielāgotas kļūdas piemērs

Iedomāsimies, ka jums ir jāizvada JSON kļūda, jo jūs izstrādājat API. Tas var izskatīties šādi:
```php
class ManaStarpejaisKlase {
	public function before($params) {
		$autentifikācija = Flight::request()->headers['Autorizācija'];
		if(empty($autentifikācija)) {
			Flight::jsonHalt(['kļūda' => 'Lai piekļūtu šai lapai, ir jābūt pierakstītam sistēmā.'], 403);
			// vai
			Flight::json(['kļūda' => 'Lai piekļūtu šai lapai, ir jābūt pierakstītam sistēmā.'], 403);
			exit;
			// vai
			Flight::halt(403, json_encode(['kļūda' => 'Lai piekļūtu šai lapai, ir jābūt pierakstītam sistēmā.']);
		}
	}
}
```

## Maršrutu grupēšana

Jūs varat pievienot maršruta grupu, un tad katram maršrutam šajā grupā būs vienāds starpējais kodols. Tas ir noderīgi, ja jums jāgrupē daudzi maršruti, piemēram, ar Autentifikācijas starpējo kodolu, lai pārbaudītu galvenes API atslēgu.

```php

// pievienots grupas metodei beigās
Flight::group('/api', function() {

	// Šis "tukšais" izskatās maršruts faktiski sakrīt ar /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Tas sakrīt ar /api/lietotāji
    Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotaji');
	// Tas sakrīt ar /api/lietotāji/1234
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'skatīt_lietotāju');
}, [ new ApiAuthMiddleware() ]);
```

Ja jūs vēlaties piemērot globālu starpējo kodolu visiem savas maršrutkārtas, jūs varat pievienot "tukšu" grupu:

```php

// pievienots grupas metodei beigās
Flight::group('', function() {

	// Tas joprojām ir /lietotāji
	Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotaji');
	// Un tas joprojām ir /lietotāji/1234
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'skatīt_lietotāju');
}, [ new ApiAuthMiddleware() ]);
```