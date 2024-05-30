# Maršruta starpnieki

Flight atbalsta maršruta un grupas maršruta starpniekus. Starpnieks ir funkcija, kas tiek izpildīta pirms (vai pēc) maršruta atzvana. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes jūsu kodā vai pārbaudīt, vai lietotājam ir atļauja piekļūt maršrutam.

## Pamata starpnieki

Šeit ir pamata piemērs:

```php
// Ja nodrošināt tikai anonīmu funkciju, tā tiks izpildīta pirms maršruta atzvana. 
// nav "pēc" starpnieku funkciju, izņemot klases (skatīt zemāk)
Flight::route('/ceļš', function() { echo ' Šeit esmu!'; })->addMiddleware(function() {
	echo 'Pirmkods starpnieks!';
});

Flight::start();

// Tas izvadīs "Pirmkods starpnieks! Šeit esmu!"
```

Ir daži ļoti svarīgi piezīmju punkti par starpniekiem, par kuriem jums vajadzētu zināt, pirms tos izmantojat:
- Starpnieka funkcijas tiek izpildītas tajā secībā, kā tās tiek pievienotas pie maršruta. Izpilde ir līdzīga tam, kā [Slim Framework šo apstrādā](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Pirmskodi tiek izpildīti pievienotajā secībā, un Pēckodi tiek izpildīti apgrieztā secībā.
- Ja jūsu starpnieka funkcija atgriež false, visa izpilde tiek apturēta, un tiek izmesta kļūdas ziņojums 403 Forbiden. Jums, visticamāk, vajadzēs to apstrādāt eleganti, izmantojot `Flight::redirect()` vai kaut ko līdzīgu.
- Ja jums ir nepieciešami parametri no jūsu maršruta, tie tiks padoti vienā masīvā jūsu starpnieka funkcijai. (`function($params) { ... }` vai `public function before($params) {}`). Iemesls tam ir tāds, ka varat strukturēt savus parametrus grupās, un dažādās šajās grupās jūsu parametri varētu pat parādīties kādā citā secībā, kas pārkāptu starpnieka funkciju, nekorekti atsaucoties uz nepareizo parametru. Šādā veidā jūs varat piekļūt tiem pēc vārda, nevis pēc pozīcijas.
- Ja jūs padodat tikai starpnieka nosaukumu, tas automātiski tiks izpildīts ar [atkarību injekcijas konteineru](dependency-injection-container) un starpnieks tiks izpildīts ar nepieciešamajiem parametriem. Ja jums nav reģistrēta atkarību injekcijas konteineri, tiks padots `flight\Engine` instances `__construct()`.

## Starpnieka klases

Starpnieks var tikt reģistrēts arī kā klase. Ja jums ir nepieciešama "pēc" funkcionalitāte, jums **jā** izmanto klase.

```php
class MansStarpnieks {
	public function before($params) {
		echo 'Pirmkods starpnieks!';
	}

	public function after($params) {
		echo 'Pēckods starpnieks!';
	}
}

$MansStarpnieks = new MansStarpnieks();
Flight::route('/ceļš', function() { echo ' Šeit esmu! '; })->addMiddleware($MansStarpnieks); // arī ->addMiddleware([ $MansStarpnieks, $MansStarpnieks2 ]);

Flight::start();

// Tas izvadīs "Pirmkods starpnieks! Šeit esmu! Pēckods starpnieks!"
```

## Starpnieku kļūmju apstrāde

Tēlojiet, ka jums ir autentifikācijas starpnieks, un ja lietotājs nav autentificējies, jūs vēlaties novirzīt lietotāju uz pieteikšanās lapu. Jums ir vairākas opcijas:

1. Jūs varat atgriezt false no starpnieka funkcijas, un Flight automātiski atgriezīs kļūdas ziņojumu 403 Forbiden, bet bez pielāgojuma.
1. Jūs varat novirzīt lietotāju uz pieteikšanās lapu, izmantojot `Flight::redirect()`.
1. Jūs varat izveidot pielāgotu kļūdu starpniekā un apturēt maršruta izpildi.

### Pamata piemērs

Šeit ir vienkāršs atgriešanās false; piemērs:
```php
class MansStarpnieks {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// jo tas ir patiess, viss turpina iet savu ceļu
	}
}
```

### Novirzīšanas piemērs

Šeit ir piemērs, kā novirzīt lietotāju uz pieteikšanās lapu:
```php
class MansStarpnieks {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/pieteikties');
			exit;
		}
	}
}
```

### Pielāgotas kļūdas piemērs

Tēlojiet, ka jums ir jāizvada JSON kļūda, jo jūs izstrādājat API. To var izdarīt šādi:
```php
class MansStarpnieks {
	public function before($params) {
		$autentifikācija = Flight::request()->headers['Authorization'];
		if(empty($autentifikācija)) {
			Flight::json(['error' => 'Jums jābūt pierakstījušies, lai piekļūtu šai lapai.'], 403);
			exit;
			// vai
			Flight::halt(403, json_encode(['error' => 'Jums jābūt pierakstījušies, lai piekļūtu šai lapai.']);
		}
	}
}
```

## Starpnieku grupēšana

Jūs varat pievienot maršruta grupu un tad katram maršrutam šajā grupā būs tāds pats starpnieks. Tas ir noderīgi, ja jums ir jāgrupē vairāki maršruti, piemēram, pēc Autentifikācijas starpnieka, lai pārbaudītu API atslēgu galvenē.

```php

// pievienots grupas metodei
Flight::group('/api', function() {

	// Šis "tukšais" izskatās tikai grupa atbilst /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotāji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotājs_skats');
}, [ jauns ApiAutentifikācijasMiddleware() ]);
```

Ja vēlaties piemērot globālo starpnieku visiem saviem maršrutiem, varat pievienot "tukšu" grupu:

```php

// pievienots grupas metodei
Flight::group('', function() {
	Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotāji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotājs_skats');
}, [ jauns ApiAutentifikācijasMiddleware() ]);
```